<?php
/**
 * Vista: Hoja de Respaldo de Proveedores y Utilidades
 * Variables: $cotizacion, $items
 */
$pageTitle = 'Respaldo Cotización ' . htmlspecialchars($cotizacion['numero_cotizacion']);
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';

$totalBase = 0;
$totalUtilidad = 0;
$totalFlete = 0;
$totalCalibracion = 0;
$totalEstampillas = 0;
$totalGeneral = 0;
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
                            <th>Proveedor</th>
                            <th>Cód. Proveedor</th>
                            <th style="text-align:right;">Precio Base</th>
                            <th style="text-align:right;">Utilidad (Ganancia)</th>
                            <th style="text-align:right;">Flete (Envío)</th>
                            <th style="text-align:right;">Calibración</th>
                            <th style="text-align:right;">Estampillas</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="9" class="mod-empty">No hay ítems en esta cotización.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $it): 
                                $qty = (int)$it['cantidad'];
                                // Los valores de la BD vienen como unitarios, los multiplicamos por la cantidad para el total de la fila.
                                // Nota: Asumimos que los campos guardados en la BD (precio_proveedor, porcentaje_utilidad, etc.) 
                                // son por unidad de producto.
                                $pBase = (float)($it['precio_proveedor'] ?? 0) * $qty;
                                $pUtil = (float)($it['porcentaje_utilidad'] ?? 0) * $qty;
                                $pFlete = (float)($it['flete'] ?? 0) * $qty;
                                $pCalib = (float)($it['calibracion'] ?? 0) * $qty;
                                $pEstamp = (float)($it['estampillas'] ?? 0) * $qty;
                                
                                $subtotalFila = $pBase + $pUtil + $pFlete + $pCalib + $pEstamp;

                                $totalBase += $pBase;
                                $totalUtilidad += $pUtil;
                                $totalFlete += $pFlete;
                                $totalCalibracion += $pCalib;
                                $totalEstampillas += $pEstamp;
                                $totalGeneral += $subtotalFila;
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 40, '…')) ?></strong><br>
                                    <span style="font-size:11px; color:#6b7280;">Cant: <?= $qty ?></span>
                                </td>
                                <td><?= htmlspecialchars($it['proveedor'] ?: 'No especificado') ?></td>
                                <td><?= htmlspecialchars($it['codigo_proveedor'] ?: '-') ?></td>
                                <td style="text-align:right; color:#4b5563;">$<?= number_format($pBase, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#059669; font-weight:600;">$<?= number_format($pUtil, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#d97706;">$<?= number_format($pFlete, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#2563eb;">$<?= number_format($pCalib, 0, ',', '.') ?></td>
                                <td style="text-align:right; color:#7c3aed;">$<?= number_format($pEstamp, 0, ',', '.') ?></td>
                                <td style="text-align:right; font-weight:bold; background:#f9fafb;">$<?= number_format($subtotalFila, 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f3f4f6; font-size:14px;">
                            <td colspan="3" style="text-align:right; font-weight:bold;">TOTALES GLOBALES (Sin IVA):</td>
                            <td style="text-align:right; font-weight:bold;">$<?= number_format($totalBase, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#059669;">$<?= number_format($totalUtilidad, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#d97706;">$<?= number_format($totalFlete, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#2563eb;">$<?= number_format($totalCalibracion, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:bold; color:#7c3aed;">$<?= number_format($totalEstampillas, 0, ',', '.') ?></td>
                            <td style="text-align:right; font-weight:900; color:#111827; font-size:16px;">$<?= number_format($totalGeneral, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div style="margin-top:20px; padding:15px; background:#f0fdf4; border:1px solid #6ee7b7; border-radius:8px; text-align:right;">
                <p style="margin:0; font-size:14px; color:#064e3b;">
                    <strong>Ganancia (Utilidad) Total Proyectada:</strong> 
                    <span style="font-size:20px; font-weight:bold;">$<?= number_format($totalUtilidad, 0, ',', '.') ?></span>
                </p>
                <?php if($totalGeneral > 0): ?>
                <p style="margin:5px 0 0 0; font-size:12px; color:#047857;">
                    Margen bruto estimado: <strong><?= number_format(($totalUtilidad / $totalGeneral) * 100, 1) ?>%</strong>
                </p>
                <?php endif; ?>
            </div>
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
