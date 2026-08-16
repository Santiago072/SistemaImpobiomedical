<?php
/**
 * Vista: Exportación a Excel de Reporte de Órdenes de Compra — Sistema Impobiomedical
 * Genera un archivo .xls compatible con Microsoft Excel y hojas de cálculo.
 */

while (ob_get_level() > 0) { ob_end_clean(); }

$nombreArchivo = 'Reporte_Ordenes_Compra_' . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Pragma: no-cache');
header('Expires: 0');

$fechaSoloFecha = date('d/m/Y');
$totalRegistros = count($datosExcel ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1f2937; }
    .titulo { font-size: 16pt; font-weight: bold; color: #10757e; text-align: center; }
    .subtitulo { font-size: 12pt; font-weight: bold; color: #1f3864; text-align: center; }
    .meta { font-size: 10pt; color: #64748b; text-align: center; font-style: italic; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background-color: #10757e; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #0d5c63; padding: 8px; }
    td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: middle; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .money { color: #059669; font-weight: bold; text-align: right; }
    .badge-pend { color: #b45309; font-weight: bold; }
    .badge-comp { color: #15803d; font-weight: bold; }
</style>
</head>
<body>

<table>
    <tr>
        <td colspan="9" class="titulo">IMPOMIN S.A.S — IMPOBIOMEDICAL</td>
    </tr>
    <tr>
        <td colspan="9" class="subtitulo">REPORTE DE ÓRDENES DE COMPRA (P.O.)</td>
    </tr>
    <tr>
        <td colspan="9" class="meta">Fecha de Generación: <?= $fechaSoloFecha ?> | Total de Órdenes Exportadas: <?= $totalRegistros ?></td>
    </tr>
    <tr><td colspan="9"></td></tr>
</table>

<table>
    <thead>
        <tr>
            <th>N° P.O.</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>NIT / Identificación</th>
            <th>Banco</th>
            <th>N° Cuenta</th>
            <th>Tipo de Cuenta</th>
            <th>Cliente a Entregar</th>
            <th>Estado</th>
            <th>Valor a Pagar ($)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $sumaTotal = 0;
        foreach ($datosExcel as $row): 
            $val = (float)($row['valor_pagar'] ?? 0);
            $sumaTotal += $val;
            $est = $row['estado'] ?? 'pendiente';
        ?>
        <tr>
            <td class="text-center" style="font-weight:bold;"><?= (int)$row['numero_po'] ?></td>
            <td class="text-center"><?= htmlspecialchars($row['fecha'] ?? '') ?></td>
            <td style="font-weight:bold;"><?= htmlspecialchars($row['proveedor'] ?? '') ?></td>
            <td class="text-center" style="mso-number-format:'\@';"><?= htmlspecialchars($row['nit'] ?? '') ?></td>
            <td class="text-center"><?= htmlspecialchars($row['banco_nombre'] ?? '—') ?></td>
            <td class="text-center" style="mso-number-format:'\@';"><?= htmlspecialchars($row['banco_cuenta'] ?? '—') ?></td>
            <td class="text-center"><?= htmlspecialchars($row['banco_tipo_cuenta'] ?? '—') ?></td>
            <td><?= htmlspecialchars($row['cliente'] ?? '—') ?></td>
            <td class="text-center <?= $est === 'completada' ? 'badge-comp' : 'badge-pend' ?>">
                <?= $est === 'completada' ? 'Completada' : 'Pendiente' ?>
            </td>
            <td class="money" style="mso-number-format:'\$#,##0.00';">$ <?= number_format($val, 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="9" style="text-align:right; font-size:12pt; background-color:#e2e8f0; color:#1e293b;">TOTAL GENERAL:</th>
            <th class="money" style="font-size:12pt; background-color:#e2e8f0; mso-number-format:'\$#,##0.00';">$ <?= number_format($sumaTotal, 2, ',', '.') ?></th>
        </tr>
    </tfoot>
</table>

</body>
</html>
<?php
exit();
