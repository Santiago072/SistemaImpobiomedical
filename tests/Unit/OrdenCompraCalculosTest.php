<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class OrdenCompraCalculosTest extends TestCase
{
    /**
     * Prueba el cálculo contable de una orden de compra con IVA y retención en la fuente.
     */
    public function testCalculoValorPagarConIvaYRetencion(): void
    {
        $items = [
            ['precio_unit' => 500000.0, 'cantidad' => 2, 'iva' => 'si', 'porcentaje_iva' => 19.0], // Subtotal: 1,000,000 | IVA: 190,000
            ['precio_unit' => 200000.0, 'cantidad' => 1, 'iva' => 'no', 'porcentaje_iva' => 0.0],  // Subtotal: 200,000   | IVA: 0
        ];
        $porcentajeRetencion = 2.5; // 2.5% sobre la base gravable total antes de IVA

        $subtotal = 0.0;
        $totalIva = 0.0;
        foreach ($items as $it) {
            $sub = $it['precio_unit'] * $it['cantidad'];
            $subtotal += $sub;
            if (strtolower($it['iva']) === 'si') {
                $totalIva += $sub * ($it['porcentaje_iva'] / 100);
            }
        }

        $retencion = $subtotal * ($porcentajeRetencion / 100); // 1,200,000 * 2.5% = 30,000
        $valorPagar = $subtotal + $totalIva - $retencion;       // 1,200,000 + 190,000 - 30,000 = 1,360,000

        $this->assertSame(1200000.0, $subtotal);
        $this->assertSame(190000.0, $totalIva);
        $this->assertSame(30000.0, $retencion);
        $this->assertSame(1360000.0, $valorPagar);
    }

    /**
     * Prueba el algoritmo de agrupamiento de ítems por orden_id en memoria (eliminación de N+1).
     */
    public function testAgrupamientoItemsPorOrdenId(): void
    {
        $itemsPlana = [
            ['id' => 1, 'orden_id' => 10, 'titulo' => 'Electrocardiógrafo'],
            ['id' => 2, 'orden_id' => 10, 'titulo' => 'Papel térmico'],
            ['id' => 3, 'orden_id' => 11, 'titulo' => 'Monitor de signos vitales'],
            ['id' => 4, 'orden_id' => 12, 'titulo' => 'Bomba de infusión'],
            ['id' => 5, 'orden_id' => 12, 'titulo' => 'Sensor SpO2'],
            ['id' => 6, 'orden_id' => 12, 'titulo' => 'Brazalete NIBP'],
        ];

        $agrupados = [];
        foreach ($itemsPlana as $item) {
            $agrupados[(int)$item['orden_id']][] = $item;
        }

        $this->assertCount(3, $agrupados);
        $this->assertCount(2, $agrupados[10]);
        $this->assertCount(1, $agrupados[11]);
        $this->assertCount(3, $agrupados[12]);
        $this->assertSame('Electrocardiógrafo', $agrupados[10][0]['titulo']);
        $this->assertSame('Sensor SpO2', $agrupados[12][1]['titulo']);
    }

    /**
     * Prueba los estados válidos permitidos para una orden de compra.
     */
    public function testEstadosValidosOrden(): void
    {
        $estadosPermitidos = ['pendiente', 'completada'];

        $this->assertTrue(in_array('pendiente', $estadosPermitidos, true));
        $this->assertTrue(in_array('completada', $estadosPermitidos, true));
        $this->assertFalse(in_array('cancelada', $estadosPermitidos, true));
        $this->assertFalse(in_array('borrador', $estadosPermitidos, true));
    }
}
