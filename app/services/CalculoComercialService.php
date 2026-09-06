<?php

/**
 * CalculoComercialService — Servicio especializado en cálculos comerciales y financieros.
 * Principio SRP: Aislar de los controladores las fórmulas matemáticas de cotizaciones y órdenes de compra:
 * - Subtotales, IVA, descuentos, retenciones y fletes.
 * - Margen de rentabilidad y precios de venta.
 */
class CalculoComercialService
{
    /**
     * Calcula los totales consolidados de un conjunto de ítems de cotización.
     */
    public function calcularTotalesCotizacion(array $items): array
    {
        $subtotal = 0.0;
        $totalIva = 0.0;

        foreach ($items as $item) {
            $cantidad = max(1, (int)($item['cantidad'] ?? 1));
            $precio = (float)($item['precio'] ?? 0);
            $aplicaIva = strtolower($item['iva'] ?? 'si') === 'si';
            $porcentajeIva = (float)($item['porcentaje_iva'] ?? 19);

            $subItem = $precio * $cantidad;
            $subtotal += $subItem;

            if ($aplicaIva) {
                $totalIva += $subItem * ($porcentajeIva / 100);
            }
        }

        $total = $subtotal + $totalIva;

        return [
            'subtotal'  => $subtotal,
            'total_iva' => $totalIva,
            'total'     => $total,
        ];
    }

    /**
     * Calcula los totales financieros de una orden de compra según sus ítems y condiciones de pago.
     */
    public function calcularTotalesOrdenCompra(array $orden, array $items): array
    {
        $subtotal = 0.0;
        $totalIva = 0.0;

        foreach ($items as $it) {
            $pu     = (float)($it['precio_unit'] ?? 0);
            $qty    = max(1, (int)($it['cantidad'] ?? 1));
            $pct    = (float)($it['porcentaje_iva'] ?? 19);
            $aplica = strtolower($it['iva'] ?? 'si') === 'si';
            $sub    = $pu * $qty;

            $subtotal += $sub;
            $totalIva += $aplica ? ($sub * ($pct / 100)) : 0.0;
        }

        $descuento = (float)($orden['descuento'] ?? 0);
        $subtotalNeto = max(0.0, $subtotal - $descuento);
        $retencionPorcentaje = (float)($orden['retencion'] ?? 0);
        $retencion = $subtotalNeto * ($retencionPorcentaje / 100);
        $flete = (float)($orden['flete'] ?? 0);
        $valorPagar = $subtotalNeto + $totalIva - $retencion + $flete;

        return [
            'subtotal'       => $subtotal,
            'descuento'      => $descuento,
            'subtotal_neto'  => $subtotalNeto,
            'iva'            => $totalIva,
            'retencion'      => $retencion,
            'flete'          => $flete,
            'valor_pagar'    => $valorPagar,
        ];
    }
}
