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
        <div class="mod-header header-actions-wrap align-center space-between">
            <div>
                <h1 class="mod-title"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Hoja de Respaldo Interna</h1>
                <p class="mod-sub">Cotización: <strong><?= htmlspecialchars($cotizacion['numero_cotizacion']) ?></strong> - Cliente: <strong><?= htmlspecialchars($cotizacion['nombre_cliente']) ?></strong></p>
            </div>
            <div>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="btn-mod-primary btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="mod-table-wrap p-24">
            <div class="tabla-responsive">
                <table class="mod-table" id="tablaRespaldo">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cód. Proveedor</th>
                            <th>Proveedor</th>
                            <th class="text-right">Precio Proveedor</th>
                            <th class="text-right">Utilidad</th>
                            <th class="text-right">Flete</th>
                            <th class="text-right">Calibración</th>
                            <th class="text-right">Estampillas</th>
                            <th class="text-right">V/F con IVA</th>
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

                                // Acumulados unitarios (como estaban)
                                $acumUtil   = $ppUnit + $puUnit;
                                $acumFlete  = $acumUtil  + $pfUnit;
                                $acumCalib  = $acumFlete + $pcUnit;
                                $acumEstamp = $acumCalib + $peUnit;

                                // Valor Final unitario CON IVA (SIN multiplicar por cantidad para mostrar en tabla)
                                $pct            = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaUnitario    = (strtolower($it['iva']) === 'si')
                                                  ? $acumEstamp * ($pct / 100) : 0;
                                $valorFinalUnitario = $acumEstamp + $ivaUnitario;
                                
                                // Para totales: multiplicar por cantidad
                                $valorFinalIva  = $valorFinalUnitario * $qty;

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
                                    <strong class="item-title"><?= htmlspecialchars($it['titulo']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($it['codigo_proveedor'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($it['proveedor'] ?: 'No especificado') ?></td>

                                <!-- Precio Proveedor -->
                                <td class="text-right text-gray">
                                    <strong>$<?= number_format($ppUnit, 0, ',', '.') ?></strong>
                                </td>

                                <!-- Utilidad acumulada + operaciones -->
                                <td class="text-right text-green-dark font-600 font-11 val-top">
                                    <div class="mb-3">
                                        <strong>$<?= number_format($acumUtil, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsUtil)): ?>
                                    <div class="resp-ops-box resp-ops-green">
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
                                <td class="text-right text-amber-dark font-600 font-11 val-top">
                                    <div class="mb-3">
                                        <strong>$<?= number_format($acumFlete, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsFlete)): ?>
                                    <div class="resp-ops-box resp-ops-amber">
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
                                <td class="text-right text-blue-dark font-600 font-11 val-top">
                                    <div class="mb-3">
                                        <strong>$<?= number_format($acumCalib, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsCalib)): ?>
                                    <div class="resp-ops-box resp-ops-blue">
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
                                <td class="text-right text-purple-dark font-600 font-11 val-top">
                                    <?php if ($acumEstamp > $acumCalib): ?>
                                    <div class="mb-3">
                                        <strong>$<?= number_format($acumEstamp, 0, ',', '.') ?></strong>
                                    </div>
                                    <?php if (!empty($opsEstamp)): ?>
                                    <div class="resp-ops-box resp-ops-purple">
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
                                    <span class="text-muted-gray">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- V/F con IVA (valor unitario, sin multiplicar por cantidad) -->
                                <td class="text-right font-bold bg-green-light text-green-dark val-top">
                                    <div>$<?= number_format($valorFinalUnitario, 0, ',', '.') ?></div>
                                    <?php if ($ivaUnitario > 0): ?>
                                    <div class="resp-iva-sub">
                                        IVA: $<?= number_format($ivaUnitario, 0, ',', '.') ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
