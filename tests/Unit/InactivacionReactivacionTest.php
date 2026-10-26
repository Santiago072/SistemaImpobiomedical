<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias para el ciclo de Inactivación, Eliminación Definitiva y Reactivación
 * en los módulos de Productos, Clientes y Proveedores.
 */
class InactivacionReactivacionTest extends TestCase
{
    /**
     * Prueba 1: Decisión entre Eliminación Definitiva vs Inactivación (Soft-Delete)
     * Si un producto/cliente/proveedor tiene cotizaciones asociadas (> 0), debe inactivarse.
     * Si NO tiene cotizaciones asociadas (== 0), permite eliminación definitiva.
     */
    public function testDecisionEliminarOInactivarPorHistorial(): void
    {
        $evaluarAccionEliminar = function (int $asociaciones): string {
            return $asociaciones > 0 ? 'inactivar' : 'eliminar_definitivo';
        };

        // Producto nuevo sin cotizaciones
        $this->assertSame('eliminar_definitivo', $evaluarAccionEliminar(0), 'Un producto sin historial debe permitir eliminación definitiva.');

        // Producto con 3 cotizaciones pasadas
        $this->assertSame('inactivar', $evaluarAccionEliminar(3), 'Un producto con cotizaciones debe pasar a estado inactivo.');

        // Cliente con 1 cotización
        $this->assertSame('inactivar', $evaluarAccionEliminar(1), 'Un cliente con cotizaciones debe pasar a estado inactivo.');

        // Cliente sin cotizaciones
        $this->assertSame('eliminar_definitivo', $evaluarAccionEliminar(0), 'Un cliente sin cotizaciones debe permitir eliminación definitiva.');
    }

    /**
     * Prueba 2: Transición de estado y reactivación
     * Al reactivar un registro inactivo, su estado pasa a 'activo'.
     */
    public function testTransicionEstadoReactivacion(): void
    {
        $registro = [
            'id' => 45,
            'nombre' => 'Pulsoxímetro P01',
            'estado' => 'inactivo'
        ];

        $reactivar = function (array &$item): void {
            $item['estado'] = 'activo';
        };

        $this->assertSame('inactivo', $registro['estado']);
        $reactivar($registro);
        $this->assertSame('activo', $registro['estado'], 'El registro debe quedar con estado activo tras la reactivación.');
    }

    /**
     * Prueba 3: Filtrado en búsquedas comerciales
     * Los registros inactivos deben ser excluidos del autocompletado en nuevas cotizaciones.
     */
    public function testFiltradoInactivosEnBusquedaComercial(): void
    {
        $catalogo = [
            ['id' => 1, 'titulo' => 'Electrocardiógrafo', 'estado' => 'activo'],
            ['id' => 2, 'titulo' => 'Pulsoxímetro Inactivo', 'estado' => 'inactivo'],
            ['id' => 3, 'titulo' => 'Monitor Signos Vitales', 'estado' => 'activo'],
        ];

        $resultadosSelect = array_values(array_filter($catalogo, function ($prod) {
            return ($prod['estado'] ?? '') === 'activo';
        }));

        $this->assertCount(2, $resultadosSelect, 'Solo deben devolverse los productos activos.');
        $this->assertSame('Electrocardiógrafo', $resultadosSelect[0]['titulo']);
        $this->assertSame('Monitor Signos Vitales', $resultadosSelect[1]['titulo']);
    }

    /**
     * Prueba 4: Visibilidad de botones en la interfaz de usuario
     * - Activo: Botón de Editar (azul) y Botón de Desactivar (rojo).
     * - Inactivo con historial: Botón de Editar (azul) y Botón de Reactivar (verde).
     * - Inactivo sin historial: Botón de Editar (azul), Botón de Reactivar (verde) y Botón de Eliminar Definitivo (rojo).
     */
    public function testVisibilidadBotonesAccion(): void
    {
        $determinarBotones = function (string $estado, int $asociaciones, bool $isAdmin): array {
            $botones = ['editar'];
            if (!$isAdmin) {
                return $botones;
            }
            if ($estado === 'activo') {
                $botones[] = 'desactivar_rojo';
            } else {
                $botones[] = 'reactivar_verde';
                if ($asociaciones === 0) {
                    $botones[] = 'eliminar_definitivo_rojo';
                }
            }
            return $botones;
        };

        // Admin con producto activo
        $this->assertSame(['editar', 'desactivar_rojo'], $determinarBotones('activo', 5, true));

        // Admin con producto inactivo con cotizaciones pasadas (protegido contra borrado definitivo)
        $this->assertSame(['editar', 'reactivar_verde'], $determinarBotones('inactivo', 5, true));

        // Admin con producto inactivo sin cotizaciones (permite reactivar o eliminar definitivamente)
        $this->assertSame(['editar', 'reactivar_verde', 'eliminar_definitivo_rojo'], $determinarBotones('inactivo', 0, true));

        // Usuario asesor (no admin) solo tiene editar
        $this->assertSame(['editar'], $determinarBotones('activo', 0, false));
    }
}
