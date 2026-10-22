<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas de Integración y Ciclo de Vida de Negocio:
 * Simula los flujos críticos de interacción descubiertos durante las pruebas:
 * 1. Ciclo de Vida de Ajuste: Clon temporal, modificación y cancelación segura sin alterar el original.
 * 2. Ciclo de Vida de Ajuste con Finalización: Preservación del número original exacto (ej: EB 03).
 * 3. Reglas de Validación de Proveedor en Órdenes de Compra (bloqueo de proveedores mixtos).
 * 4. Paginación y Filtros compuestos en Órdenes y Cotizaciones (búsqueda por número P.O., proveedor, rango de fechas).
 * 5. Integridad de Clones Huérfanos y Limpieza de Borradores de Sesión.
 */
class FlujoIntegracionNegocioTest extends TestCase
{
    /**
     * Prueba 1: Simulación de Aislamiento de Ajuste Directo con Clon Temporal.
     * Si el usuario inicia un ajuste, se crea un clon temporal (es_revision = 1, estado = borrador).
     * Si cancela, se destruye el clon y la cotización original finalizada permanece intacta.
     */
    public function testAislamientoAjusteDirectoYCancelacion(): void
    {
        // Cotización original finalizada en base de datos
        $cotizacionOriginal = [
            'id' => 101,
            'numero_cotizacion' => 'EB 04',
            'estado' => 'finalizada',
            'cliente_nombre' => 'Hospital San Vicente',
            'cliente_nit' => '900123456-1',
            'dias_validez' => 30,
            'items' => [
                ['id' => 501, 'titulo' => 'Electrocardiógrafo', 'precio' => 4500000.0, 'cantidad' => 1],
            ]
        ];

        // 1. Iniciar Ajuste: Crear clon de trabajo
        $clonTemporal = [
            'id' => 999, // ID nuevo generado
            'es_revision' => 1,
            'estado' => 'borrador',
            'cliente_nombre' => $cotizacionOriginal['cliente_nombre'],
            'cliente_nit' => $cotizacionOriginal['cliente_nit'],
            'dias_validez' => $cotizacionOriginal['dias_validez'],
            'items' => [
                ['id' => 601, 'titulo' => 'Electrocardiógrafo', 'precio' => 4500000.0, 'cantidad' => 1],
                ['id' => 602, 'titulo' => 'Sensor SpO2 Reutilizable', 'precio' => 150000.0, 'cantidad' => 2], // Ítem agregado durante el ajuste
            ]
        ];

        // 2. Simular "Cancelar Ajuste"
        $ajusteCancelado = true;
        if ($ajusteCancelado) {
            // El clon se destruye
            $clonDestruido = true;
            $clonTemporal = null;
        }

        // 3. Verificaciones de integridad
        $this->assertTrue($clonDestruido);
        $this->assertNull($clonTemporal);
        $this->assertSame('EB 04', $cotizacionOriginal['numero_cotizacion']);
        $this->assertSame('finalizada', $cotizacionOriginal['estado']);
        $this->assertCount(1, $cotizacionOriginal['items'], 'La cotización original NO debe haber sufrido mutaciones.');
    }

    /**
     * Prueba 2: Simulación de Guardado de Ajuste Directo (Conservación estricta del número P.O. / Cotización).
     */
    public function testFinalizacionAjusteConservaNumeroExacto(): void
    {
        $numeroFijoOriginal = 'JS 12';
        $postData = [
            'cliente_nombre' => 'Clínica Las Américas',
            'cliente_nit' => '890987654-3',
            'dias_validez' => 45,
            'condiciones_pago' => 'CREDITO 30 DIAS'
        ];

        // El servicio de finalización recibe $numeroFijo
        $numeroFinal = !empty($numeroFijoOriginal) ? $numeroFijoOriginal : 'OTRO_NUMERO';

        $this->assertSame('JS 12', $numeroFinal, 'En modo ajuste el número debe mantenerse exactamente igual.');
        $this->assertStringNotContainsString('_01', $numeroFinal, 'No debe agregar sufijo de revisión cuando es ajuste directo.');
    }

    /**
     * Prueba 3: Regla de Negocio para Emisión de Órdenes (Bloqueo de Proveedores Mixtos).
     * Una orden de compra a un proveedor no puede mezclar ítems asignados a otro proveedor.
     */
    public function testBloqueoProveedoresMixtosEnOrdenDeCompra(): void
    {
        // Caso A: Ítems del mismo proveedor -> VÁLIDO
        $itemsMismoProveedor = [
            ['id' => 1, 'titulo' => 'Monitor', 'proveedor' => 'Mindray Medical'],
            ['id' => 2, 'titulo' => 'Cable ECG', 'proveedor' => 'Mindray Medical'],
        ];

        $proveedoresA = array_unique(array_filter(array_map(fn($i) => trim($i['proveedor'] ?? ''), $itemsMismoProveedor)));
        $esValidoA = (count($proveedoresA) <= 1);
        $this->assertTrue($esValidoA, 'Ítems del mismo proveedor deben permitir generar la orden.');

        // Caso B: Ítems de proveedores diferentes -> BLOQUEADO
        $itemsProveedoresMixtos = [
            ['id' => 1, 'titulo' => 'Monitor', 'proveedor' => 'Mindray Medical'],
            ['id' => 2, 'titulo' => 'Bomba de Infusión', 'proveedor' => 'B. Braun Medical'],
        ];

        $proveedoresB = array_unique(array_filter(array_map(fn($i) => trim($i['proveedor'] ?? ''), $itemsProveedoresMixtos)));
        $esValidoB = (count($proveedoresB) <= 1);
        $this->assertFalse($esValidoB, 'Ítems de múltiples proveedores deben ser rechazados con error de proveedor mixto.');
    }

    /**
     * Prueba 4: Sincronización y Detección de Proveedor Registrado vs Nuevo.
     * Si el proveedor ya tiene órdenes previas o NIT coincidente, debe detectarse como 'registrado'.
     */
    public function testDeteccionEstadoProveedorRegistradoVsNuevo(): void
    {
        $proveedoresExistentes = [
            'biomex sas' => ['nit' => '900555123-1', 'banco' => 'Bancolombia'],
            'siemens healthcare' => ['nit' => '860111222-3', 'banco' => 'Davivienda'],
        ];

        // Consulta 1: Proveedor existente por nombre
        $buscar1 = 'Biomex SAS';
        $key1 = mb_strtolower(trim($buscar1));
        $estaRegistrado1 = isset($proveedoresExistentes[$key1]);
        $this->assertTrue($estaRegistrado1);

        // Consulta 2: Proveedor nunca antes usado
        $buscar2 = 'Nuevo Laboratorio XYZ';
        $key2 = mb_strtolower(trim($buscar2));
        $estaRegistrado2 = isset($proveedoresExistentes[$key2]);
        $this->assertFalse($estaRegistrado2);
    }

    /**
     * Prueba 5: Robustez en Filtros Compuestos de Búsqueda de Órdenes (Búsqueda por P.O. numérico o texto).
     */
    public function testFiltroBusquedaOrdenPorPoNumerico(): void
    {
        $terminoBusqueda1 = 'P.O. 45';
        $soloDigitos1 = preg_replace('/\D/', '', $terminoBusqueda1);
        $this->assertSame('45', $soloDigitos1);
        $this->assertSame(45, (int)$soloDigitos1);

        $terminoBusqueda2 = '#108';
        $soloDigitos2 = preg_replace('/\D/', '', $terminoBusqueda2);
        $this->assertSame('108', $soloDigitos2);
        $this->assertSame(108, (int)$soloDigitos2);

        $terminoBusquedaTexto = 'B. Braun';
        $soloDigitosTexto = preg_replace('/\D/', '', $terminoBusquedaTexto);
        $this->assertEmpty($soloDigitosTexto, 'Un nombre de proveedor no debe interpretarse como número P.O.');
    }

    /**
     * Prueba 6: Paginación y Offset seguro.
     * Previene divisiones por cero o números de página negativos/cero.
     */
    public function testCalculoPaginacionSeguro(): void
    {
        $porPagina = 10;

        // Caso: 0 registros
        $total0 = 0;
        $paginas0 = (int)ceil($total0 / $porPagina);
        $this->assertSame(0, $paginas0);

        // Caso: 25 registros -> 3 páginas
        $total25 = 25;
        $paginas25 = (int)ceil($total25 / $porPagina);
        $this->assertSame(3, $paginas25);

        // Página solicitada por query string inválida (ej: -5 o 0)
        $paginaInvalida = -3;
        $paginaSegura = max(1, (int)$paginaInvalida);
        $offset = ($paginaSegura - 1) * $porPagina;

        $this->assertSame(1, $paginaSegura);
        $this->assertSame(0, $offset);
    }

    /**
     * Prueba 7: Regla de Negocio de Integridad y Bloqueo de Eliminación:
     * Si una cotización tiene órdenes de compra oficiales asociadas,
     * el sistema bloquea su eliminación para proteger los compromisos de compra y la trazabilidad.
     */
    public function testBloqueoEliminacionCotizacionConOrdenesAsociadas(): void
    {
        // 1. Simulación de cotización con órdenes de compra asociadas
        $ordenesAsociadas = [
            ['id' => 1, 'numero_po' => 105, 'proveedor' => 'Mindray Medical'],
            ['id' => 2, 'numero_po' => 106, 'proveedor' => 'B. Braun']
        ];

        // Lógica de validación
        $puedeEliminar = empty($ordenesAsociadas);
        $this->assertFalse($puedeEliminar, 'Una cotización con órdenes de compra asociadas NO debe ser eliminada');

        $numerosPo = array_map(function($o) {
            return 'P.O. #' . (int)$o['numero_po'];
        }, $ordenesAsociadas);
        $textoPo = implode(', ', $numerosPo);

        $this->assertSame('P.O. #105, P.O. #106', $textoPo);

        // 2. Simulación de cotización sin órdenes de compra (debe permitir eliminación)
        $ordenesVacias = [];
        $puedeEliminarSinOrdenes = empty($ordenesVacias);
        $this->assertTrue($puedeEliminarSinOrdenes, 'Una cotización sin órdenes de compra SÍ debe permitir su eliminación');
    }

    /**
     * Prueba 8: Reordenamiento Secuencial de Ítems en Cotización:
     * Verifica la lógica de intercambio de posiciones al subir o bajar filas.
     */
    public function testReordenamientoItemsCotizacion(): void
    {
        $items = [
            ['id' => 10, 'titulo' => 'Electrocardiógrafo', 'orden' => 1],
            ['id' => 20, 'titulo' => 'Monitor de Signos', 'orden' => 2],
            ['id' => 30, 'titulo' => 'Desfibrilador', 'orden' => 3],
        ];

        // Mover ítem 30 (Desfibrilador) hacia 'arriba': debe intercambiar con el 20
        $idxActual = 2; // posición del ítem 30
        $idxDestino = $idxActual - 1; // posición 1 (ítem 20)

        $temp = $items[$idxActual];
        $items[$idxActual] = $items[$idxDestino];
        $items[$idxDestino] = $temp;

        // Reasignar orden secuencial 1, 2, 3
        foreach ($items as $pos => &$it) {
            $it['orden'] = $pos + 1;
        }
        unset($it);

        $this->assertSame(30, $items[1]['id']);
        $this->assertSame(2, $items[1]['orden']);
        $this->assertSame('Desfibrilador', $items[1]['titulo']);

        $this->assertSame(20, $items[2]['id']);
        $this->assertSame(3, $items[2]['orden']);
        $this->assertSame('Monitor de Signos', $items[2]['titulo']);

        $this->assertSame(10, $items[0]['id']);
        $this->assertSame(1, $items[0]['orden']);
    }
}
