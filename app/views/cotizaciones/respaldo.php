<?php
/**
 * Vista: Hoja de Respaldo de Proveedores y Utilidades
 * Variables: $cotizacion, $items
 */
$pageTitle = 'Respaldo Cotización ' . htmlspecialchars($cotizacion['numero_cotizacion']);
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';

$totalPrecioProveedor = 0;
$totalUtilidad        = 0;
$totalFlete           = 0;
$totalCalibracion     = 0;
$totalEstampillas     = 0;
$totalValorFinal      = 0;
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="mod-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1 class="mod-title"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Hoja de Respaldo Interna</h1>
                <p class="mod-sub">Cotización: <strong><?= htmlspecialchars($cotizacion['numero_cotizacion']) ?></strong> - Cliente: <strong><?= htmlspecialchars($cotizacion['nombre_cliente']) ?></strong></p>
            </div>
            <div>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="btn-mod-primary" style="background:#6b7280; border:none;">
                    <i class="bi bi-arrow-left"></i> Volver a Consultar
                </a>
                <button type="button" class="btn-mod-primary" onclick="window.print()" style="margin-left:8px;">
                    <i class="bi bi-printer-fill"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="mod-table-wrap" style="padding:24px;">
            <div class="tabla-responsive">
                <table class="mod-table" id="tablaRespaldo">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cód. Proveedor</th>
                            <th>Proveedor</th>
                            <th style="text-align:right;">Precio Proveedor</th>
                            <th style="text-align:right;">Porcentaje Utilidad</th>
                            <th style="text-align:right;">Flete</th>
                            <th style="text-align:right;">Calibración</th>
                            <th style="text-align:right;">Estampillas</th>
                            <th style="text-align:right;">V/F con IVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="9" class="mod-empty">No hay ítems en esta cotización.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $it):
                                $qty = (int)$it['cantidad'];

                                /*
                                 * Los valores guardados en BD son diferenciales UNITARIOS en cascada.
                                 * Se reconstruyen los ACUMULADOS unitarios para mostrar exactamente
                                 * lo que la calculadora dinámica muestra en cada etapa:
                                 *
                                 *   acum_util    = precio_proveedor + porcentaje_utilidad  → col "Utilidad"
                                 *   acum_flete   = acum_util   + flete                    → col "Flete"
                                 *   acum_calib   = acum_flete  + calibracion              → col "Calibración"
                                 *   acum_estamp  = acum_calib  + estampillas              → col "Estampillas"
                                 *
                                 * "Precio Proveedor" = precio_proveedor unitario
                                 * "Valor Final con IVA" = acum_estamp * qty + IVA (total de la fila)
                                 *
                                 * Los totales del pie van × cantidad para reflejar el costo total real.
                                 */
                                $ppUnit = (float)($it['precio_proveedor']    ?? 0);
                                $puUnit = (float)($it['porcentaje_utilidad'] ?? 0);
                                $pfUnit = (float)($it['flete']               ?? 0);
                                $pcUnit = (float)($it['calibracion']         ?? 0);
                                $peUnit = (float)($it['estampillas']         ?? 0);

                                // Acumulados unitarios — estos son los valores que muestra la calculadora
                                $acumUtil   = $ppUnit + $puUnit;
                                $acumFlete  = $acumUtil  + $pfUnit;
                                $acumCalib  = $acumFlete + $pcUnit;
                                $acumEstamp = $acumCalib + $peUnit; // precio venta sin IVA unitario

                                // Valor Final con IVA de la fila completa (unitario × cantidad + IVA)
                                $subtotalSinIva = $acumEstamp * $qty;
                                $pct            = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaFila        = (strtolower($it['iva']) === 'si')
                                                  ? $subtotalSinIva * ($pct / 100)
                                                  : 0;
                                $valorFinalIva  = $subtotalSinIva + $ivaFila;

                                // Totales del pie (× cantidad)
                                $totalPrecioProveedor += $ppUnit  * $qty;
                                $totalUtilidad        += $acumUtil   * $qty;
                                $totalFlete           += $acumFlete  * $qty;
                                $totalCalibracion     += $acumCalib  * $qty;
                                $totalEstampillas     += $acumEstamp * $qty;
                                $totalValorFinal      += $valorFinalIva;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 40, '…')) ?></strong><br>
                                    <span style="font-size:11px; color:#6b7280;">Cant: <?= $qty ?></span>
                                </td>
                                <td><?= htmlspecialchars($it['codigo_proveedor'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($it['proveedor'] ?: 'No especificado') ?></td>
                                <td style="text-align:right; color:#4b5563;">$<?= number_format($ppUnit, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#059669; font-weight:600;">$<?= number_format($acumUtil, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#d97706;">$<?= number_format($acumFlete, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#2563eb;">$<?= number_format($acumCalib, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#7c3aed;">$<?= number_format($acumEstamp, 0, ',', '.') ?></td>
                                <td style="text-align:right; font-weight:bold; background:#f0fdf4; color:#059669;">$<?= number_format($valorFinalIva, 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <!-- <tfoot>
                        <tr style="background:#f3f4f6; font-size:14px;">
                            <td style="text-align:right; font-weight:bold;">TOTALES:</td>
                            <td style="text-align:right; font-weight:bold; color:#4b5563;">$<?= number_format($totalPrecioProveedor, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#059669;">$<?= number_format($totalUtilidad, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#d97706;">$<?= number_format($totalFlete, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#2563eb;">$<?= number_format($totalCalibracion, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#7c3aed;">$<?= number_format($totalEstampillas, 0, ',', '.') ?></td>
                            <td colspan="2"></td>
                            <td style="text-align:right; font-weight:900; color:#059669; font-size:16px;">$<?= number_format($totalValorFinal, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot> -->
                </table>
            </div>
            
            <!-- <div style="margin-top:20px; padding:15px; background:#f0fdf4; border:1px solid #6ee7b7; border-radius:8px; text-align:right;">
                <p style="margin:0; font-size:14px; color:#064e3b;">
                    <strong>Ganancia (Utilidad) Total Proyectada:</strong>
                    <span style="font-size:20px; font-weight:bold;">$<?= number_format($totalUtilidad, 0, ',', '.') ?></span>
                </p>
                <p style="margin:5px 0 0 0; font-size:14px; color:#064e3b;">
                    <strong>Valor Final Total con IVA:</strong>
                    <span style="font-size:20px; font-weight:bold; color:#059669;">$<?= number_format($totalValorFinal, 0, ',', '.') ?></span>
                </p>
            </div> -->
        </div>
    </main>
</div>

<style>
@media print {
    .layout-menu, .layout-topbar, .mod-actions, .btn-mod-primary { display: none !important; }
    .layout-main { margin-left: 0 !important; padding: 0 !important; }
    body { background: white; }
    .mod-table-wrap { box-shadow: none; border: none; padding: 0 !important; }
    .mod-header { margin-bottom: 20px; }
    #tablaRespaldo th, #tablaRespaldo td { border: 1px solid #000; font-size: 11px; padding: 6px; }
}
</style>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
