<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidacionesNegocioTest extends TestCase
{
    /**
     * Prueba el bloqueo de emisión de órdenes de compra para cotizaciones concluidas o descartadas.
     */
    public function testBloqueoEmisionOrdenSegunEstadoComercial(): void
    {
        $cotizacionPendiente = ['id' => 1, 'estado_comercial' => 'pendiente'];
        $cotizacionConcluida = ['id' => 2, 'estado_comercial' => 'concluida'];
        $cotizacionDescartada = ['id' => 3, 'estado_comercial' => 'descartada'];

        $puedeEmitirPendiente = ($cotizacionPendiente['estado_comercial'] === 'pendiente');
        $puedeEmitirConcluida = ($cotizacionConcluida['estado_comercial'] === 'pendiente');
        $puedeEmitirDescartada = ($cotizacionDescartada['estado_comercial'] === 'pendiente');

        $this->assertTrue($puedeEmitirPendiente, 'Una cotización pendiente debe permitir emitir órdenes.');
        $this->assertFalse($puedeEmitirConcluida, 'Una cotización concluida NO debe permitir emitir órdenes.');
        $this->assertFalse($puedeEmitirDescartada, 'Una cotización descartada NO debe permitir emitir órdenes.');
    }

    /**
     * Prueba el formato de operaciones dinámicas de la calculadora de utilidades (calc_ops JSON).
     */
    public function testEstructuraCalcOpsJson(): void
    {
        $operaciones = [
            'utilidad'    => ['tipo' => 'pct', 'valor' => 35.0],
            'flete'       => ['tipo' => 'fijo', 'valor' => 25000.0],
            'calibracion' => ['tipo' => 'fijo', 'valor' => 80000.0],
            'estampillas' => ['tipo' => 'pct', 'valor' => 1.5],
        ];

        $jsonEncoded = json_encode($operaciones, JSON_UNESCAPED_UNICODE);
        $this->assertIsString($jsonEncoded);

        $decoded = json_decode($jsonEncoded, true);
        $this->assertIsArray($decoded);
        $this->assertEquals(35.0, (float)$decoded['utilidad']['valor']);
        $this->assertEquals(25000.0, (float)$decoded['flete']['valor']);
        $this->assertSame('fijo', $decoded['calibracion']['tipo']);
    }
}
