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
                            <th style="text-align:right;">Utilidad</th>
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

                                $ppUnit = (float)($it['precio_proveedor']    ?? 0);
                                $puUnit = (float)($it['porcentaje_utilidad'] ?? 0);
                                $pfUnit = (float)($it['flete']               ?? 0);
                                $pcUnit = (float)($it['calibracion']         ?? 0);
                                $peUnit = (float)($it['estampillas']         ?? 0);

                                // Acumulados unitarios
                                $acumUtil   = $ppUnit + $puUnit;
                                $acumFlete  = $acumUtil  + $pfUnit;
                                $acumCalib  = $acumFlete + $pcUnit;
                                $acumEstamp = $acumCalib + $peUnit;

                                // Valor Final con IVA total de la fila
                                $subtotalSinIva = $acumEstamp * $qty;
                                $pct            = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaFila        = (strtolower($it['iva']) === 'si')
                                                  ? $subtotalSinIva * ($pct / 100) : 0;
                                $valorFinalIva  = $subtotalSinIva + $ivaFila;

                                $totalPrecioProveedor += $ppUnit    * $qty;
                                $totalUtilidad        += $acumUtil  * $qty;
                                $totalFlete           += $acumFlete * $qty;
                                $totalCalibracion     += $acumCalib * $qty;
                                $totalEstampillas     += $acumEstamp* $qty;
                                $totalValorFinal      += $valorFinalIva;

                                // Porcentaje de utilidad sobre precio proveedor
                                $pctUtil = ($ppUnit > 0) ? round(($puUnit / $ppUnit) * 100, 1) : 0;

                                // Operaciones del JSON
                                $ops = json_decode($it['calc_ops'] ?? '{}', true) ?: [];
                                $opsUtil   = $ops['utilidad']    ?? [];
                                $opsFlete  = $ops['flete']       ?? [];
                                $opsCalib  = $ops['calibracion'] ?? [];
                                $opsEstamp = $ops['estampillas'] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-size:13px;"><?= htmlspecialchars($it['titulo']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($it['codigo_proveedor'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($it['proveedor'] ?: 'No especificado') ?></td>

                                <!-- Precio Proveedor -->
                                <td style="text-align:right; color:#4b5563;">
                                    <strong>$<?= number_format($ppUnit, 0, ',', '.') ?></strong>
                                </td>

                                <!-- Utilidad acumulada + operaciones -->
                                <td style="text-align:right; color:#059669; font-weight:600; font-size:11px; vertical-align:top;">
                                    <div style="margin-bottom:3px;">
                                        <strong>$<?= number_format($acumUtil, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsUtil)): ?>
                                    <div style="background:#f0fdf4; border-left:2px solid #059669; padding:2px 4px; margin-top:2px; line-height:1.2;">
                                        <?php foreach ($opsUtil as $op):
                                            $tipo  = $op['tipo']  ?? 'suma';
                                            $valor = (float)($op['valor'] ?? 0);
                                            if ($valor == 0) continue;
                                            if ($tipo === 'suma') {
                                                echo '<span>+ $' . number_format($valor, 0, ',', '.') . '</span><br>';
                                            } elseif ($tipo === 'mult_pct') {
                                                echo '<span>+ ' . $valor . '%</span><br>';
                                            } elseif ($tipo === 'div_pct') {
                                                echo '<span>÷ ' . $valor . '</span><br>';
                                            }
                                        endforeach;
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Flete acumulado + operaciones -->
                                <td style="text-align:right; color:#d97706; font-weight:600; font-size:11px; vertical-align:top;">
                                    <div style="margin-bottom:3px;">
                                        <strong>$<?= number_format($acumFlete, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsFlete)): ?>
                                    <div style="background:#fef3c7; border-left:2px solid #d97706; padding:2px 4px; margin-top:2px; line-height:1.2;">
                                        <?php foreach ($opsFlete as $op):
                                            $tipo  = $op['tipo']  ?? 'suma';
                                            $valor = (float)($op['valor'] ?? 0);
                                            if ($valor == 0) continue;
                                            if ($tipo === 'suma') {
                                                echo '<span>+ $' . number_format($valor, 0, ',', '.') . '</span><br>';
                                            } elseif ($tipo === 'mult_pct') {
                                                echo '<span>+ ' . $valor . '%</span><br>';
                                            } elseif ($tipo === 'div_pct') {
                                                echo '<span>÷ ' . $valor . '</span><br>';
                                            }
                                        endforeach;
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Calibración acumulada + operaciones -->
                                <td style="text-align:right; color:#2563eb; font-weight:600; font-size:11px; vertical-align:top;">
                                    <div style="margin-bottom:3px;">
                                        <strong>$<?= number_format($acumCalib, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsCalib)): ?>
                                    <div style="background:#dbeafe; border-left:2px solid #2563eb; padding:2px 4px; margin-top:2px; line-height:1.2;">
                                        <?php foreach ($opsCalib as $op):
                                            $tipo  = $op['tipo']  ?? 'suma';
                                            $valor = (float)($op['valor'] ?? 0);
                                            if ($valor == 0) continue;
                                            if ($tipo === 'suma') {
                                                echo '<span>+ $' . number_format($valor, 0, ',', '.') . '</span><br>';
                                            } elseif ($tipo === 'mult_pct') {
                                                echo '<span>+ ' . $valor . '%</span><br>';
                                            } elseif ($tipo === 'div_pct') {
                                                echo '<span>÷ ' . $valor . '</span><br>';
                                            }
                                        endforeach;
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Estampillas acumuladas + operaciones -->
                                <td style="text-align:right; color:#7c3aed; font-weight:600; font-size:11px; vertical-align:top;">
                                    <?php if ($acumEstamp > $acumCalib): ?>
                                    <div style="margin-bottom:3px;">
                                        <strong>$<?= number_format($acumEstamp, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsEstamp)): ?>
                                    <div style="background:#ede9fe; border-left:2px solid #7c3aed; padding:2px 4px; margin-top:2px; line-height:1.2;">
                                        <?php foreach ($opsEstamp as $op):
                                            $tipo  = $op['tipo']  ?? 'suma';
                                            $valor = (float)($op['valor'] ?? 0);
                                            if ($valor == 0) continue;
                                            if ($tipo === 'suma') {
                                                echo '<span>+ $' . number_format($valor, 0, ',', '.') . '</span><br>';
                                            } elseif ($tipo === 'mult_pct') {
                                                echo '<span>+ ' . $valor . '%</span><br>';
                                            } elseif ($tipo === 'div_pct') {
                                                echo '<span>÷ ' . $valor . '</span><br>';
                                            }
                                        endforeach;
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <span style="color:#9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- V/F con IVA -->
                                <td style="text-align:right; font-weight:bold; background:#f0fdf4; color:#059669; vertical-align:top;">
                                    <div>$<?= number_format($valorFinalIva, 0, ',', '.') ?></div>
                                    <?php if ($ivaFila > 0): ?>
                                    <div style="font-size:10px; font-weight:400; color:#6b7280; margin-top:2px;">
                                        IVA: $<?= number_format($ivaFila, 0, ',', '.') ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
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
