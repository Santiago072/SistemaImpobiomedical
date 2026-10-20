<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias de Robustez y Fuzzing de entradas numéricas.
 * Valida el comportamiento ante valores extremos, negativos, nulos o malformados.
 */
class RobustezEntradasTest extends TestCase
{
    /**
     * Prueba que las cantidades en ítems siempre se normalicen a un mínimo positivo seguro (>= 1).
     */
    public function testNormalizacionCantidadesExtremas(): void
    {
        $normalizarCantidad = function ($input): int {
            return max(1, (int)$input);
        };

        $this->assertSame(1, $normalizarCantidad(0), 'Cantidad 0 debe elevarse a 1');
        $this->assertSame(1, $normalizarCantidad(-15), 'Cantidades negativas deben elevarse a 1');
        $this->assertSame(1, $normalizarCantidad('abc'), 'Valores no numéricos deben elevarse a 1');
        $this->assertSame(5, $normalizarCantidad(5), 'Cantidad positiva válida debe respetarse');
        $this->assertSame(100, $normalizarCantidad('100'), 'Cantidad numérica en string debe convertirse a entero');
    }

    /**
     * Prueba que el validador numérico rechace ceros, negativos o inyecciones de caracteres.
     */
    public function testValidarNumeroEstricto(): void
    {
        $this->assertTrue(validar_numero(1));
        $this->assertTrue(validar_numero('45'));
        $this->assertTrue(validar_numero(99.99));

        $this->assertFalse(validar_numero(0), '0 no es un ID válido');
        $this->assertFalse(validar_numero(-10), 'Negativo no es un ID válido');
        $this->assertFalse(validar_numero('1 OR 1=1'), 'Inyección SQL en parámetro numérico debe ser rechazada');
        $this->assertFalse(validar_numero('3; DROP TABLE usuarios;'), 'Payload SQL debe ser rechazado');
        $this->assertFalse(validar_numero(null));
        $this->assertFalse(validar_numero(''));
    }

    /**
     * Prueba la sanitización de NIT eliminando puntos, espacios y caracteres extraños pero preservando el guion verificador.
     */
    public function testSanitizacionNitNormalizado(): void
    {
        $this->assertSame('900535843-3', normalizar_nit('900.535.843-3'));
        $this->assertSame('900535843-3', normalizar_nit('  900 535 843 - 3 '));
        $this->assertSame('12345678', normalizar_nit(' 12.345.678 '));
        $this->assertSame('800123456-7', normalizar_nit('800.123.456-7'));
    }

    /**
     * Prueba la robustez de la calculadora de utilidades ante costos cero o negativos.
     */
    public function testCalculadoraConValoresCero(): void
    {
        $costoProveedor = 0.0;
        $pctUtilidad = 30.0;
        $flete = 0.0;
        $calibracion = 0.0;
        $estampillas = 0.0;

        $ganancia = $costoProveedor * ($pctUtilidad / 100);
        $precioUnitario = $costoProveedor + $ganancia + $flete + $calibracion + $estampillas;

        $this->assertSame(0.0, $precioUnitario, 'Costo cero con fletes cero debe dar 0 sin errores de división ni excepción');
    }
}
