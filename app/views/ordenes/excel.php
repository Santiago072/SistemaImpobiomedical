<?php
/**
 * Vista: Exportar a Excel - Reporte de Órdenes de Compra — IMPOMIN S.A.S / Impobiomedical
 * Variables: $datosExcel, $filtros
 */

$baseUrl = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
if (strpos($baseUrl, 'http') !== 0) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl  = $protocol . $host . '/' . ltrim($baseUrl, '/');
}

$logoImpUrl = rtrim($baseUrl, '/') . '/logo/logoimp.png';
$logoPdfUrl = rtrim($baseUrl, '/') . '/logo/logopdf.png';

$fechaSoloFecha = date('d/m/Y');
$totalRegistros = count($datosExcel ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Órdenes de Compra — Impobiomedical</title>
    <style>
        body { font-family: Arial, sans-serif; }
        br { mso-data-placement: same-cell; }
        td { mso-data-placement: same-cell; }
        
        /* ── Encabezado Corporativo Ejecutivo Excel ── */
        table.excel-hdr { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-bottom: 10px; background: #ffffff; }
        
        /* ── Tabla de Datos ── */
        table.data-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 10px; }
        table.data-table th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 11px; text-transform: uppercase; border: 1.5px solid #0d5c63; padding: 8px; text-align: center; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        .filter-table { border-collapse: collapse; margin-bottom: 12px; width: 50%; }
        .filter-table th { background-color: #1f3864; color: #ffffff; border: 1px solid #1f3864; padding: 6px; font-size: 11px; text-align: left; }
        .filter-table td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        
        .money { font-weight: bold; color: #059669; text-align: right; }
    </style>
</head>
<body>

    <!-- ENCABEZADO CORPORATIVO EJECUTIVO EN EXCEL -->
    <table class="excel-hdr">
        <!-- 1. Barra superior teal -->
        <tr height="6" style="background:#10757e; height:6pt;">
            <td colspan="8" style="background:#10757e; height:6pt; padding:0; border:none;"></td>
        </tr>

        <!-- 2. Fila de Logos y Empresa -->
        <tr height="55" style="height:55pt; background:#ffffff;">
            <td colspan="4" style="text-align:left; vertical-align:middle; padding:8px 12px; border:none;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" width="135" height="30" style="display:inline-block; vertical-align:middle; margin-right:10px;"><br style="mso-data-placement:same-cell;">
                <?php endif; ?>
                <font face="Arial" size="2" color="#1f3864"><b>IMPOMIN S.A.S</b></font> &nbsp;&nbsp;|&nbsp;&nbsp; 
                <font face="Arial" size="1" color="#10757e"><b>Nit. 900.535.843-3</b></font>
            </td>
            <td colspan="4" style="text-align:right; vertical-align:middle; padding:8px 12px; border:none;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" width="165" height="42" style="display:inline-block; vertical-align:middle;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- 3. Franja Principal del Título (Azul Institucional #1f3864) -->
        <tr height="32" style="background:#1f3864; height:32pt;">
            <td colspan="8" style="background:#1f3864; color:#ffffff; text-align:center; vertical-align:middle; font-size:14px; font-weight:bold; border:none;">
                <font face="Arial" color="#ffffff" size="4"><b>REPORTE DE ÓRDENES DE COMPRA — SISTEMA IMPOBIOMEDICAL</b></font>
            </td>
        </tr>

        <!-- 4. Franja de Metadatos y Contacto Corporativo -->
        <tr height="22" style="background:#eff6ff; height:22pt;">
            <td colspan="8" style="background:#eff6ff; text-align:center; vertical-align:middle; border-bottom:2px solid #10757e; border-top:none; border-left:none; border-right:none;">
                <font face="Arial" size="1" color="#1e40af">
                    Fecha de Reporte: <b><?= $fechaSoloFecha ?></b> &nbsp;|&nbsp; 
                    Total Registros: <b><?= $totalRegistros ?></b> &nbsp;|&nbsp; 
                    Florencia - Medellín &nbsp;|&nbsp; impobiomedical@impomin.com
                </font>
            </td>
        </tr>

        <!-- 5. Espacio de separación antes de la tabla -->
        <tr height="10" style="height:10pt;">
            <td colspan="8" style="border:none;"></td>
        </tr>
    </table>

    <?php if (!empty($filtros)): ?>
    <table class="filter-table">
        <thead>
            <tr>
                <th colspan="2">Filtros Aplicados</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($filtros['proveedor'])): ?>
            <tr><td><strong>Proveedor:</strong></td><td><?= htmlspecialchars($filtros['proveedor']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($filtros['cotizacion_numero'])): ?>
            <tr><td><strong>Nº Cotización:</strong></td><td><?= htmlspecialchars($filtros['cotizacion_numero']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin'])): ?>
            <tr><td><strong>Rango de Fechas:</strong></td><td>
                <?php 
                    if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                        echo htmlspecialchars($filtros['fecha_inicio']) . ' a ' . htmlspecialchars($filtros['fecha_fin']);
                    } elseif (!empty($filtros['fecha_inicio'])) {
                        echo 'Desde ' . htmlspecialchars($filtros['fecha_inicio']);
                    } elseif (!empty($filtros['fecha_fin'])) {
                        echo 'Hasta ' . htmlspecialchars($filtros['fecha_fin']);
                    }
                ?>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nombre del Proveedor</th>
                <th>Número de Orden</th>
                <th>Nombre de Banco</th>
                <th>Número de Cuenta</th>
                <th>Tipo de Cuenta</th>
                <th>NIT / Identificación</th>
                <th>Valor a Pagar ($)</th>
                <th>Cliente a Entregar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datosExcel as $row): ?>
            <tr>
                <td style="text-align: left;"><strong><?= htmlspecialchars($row['proveedor'] ?? '') ?></strong></td>
                <td style="text-align: center;"><?= (int)$row['numero_po'] ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['banco_nombre'] ?? '') ?></td>
                <!-- Formato de texto para evitar notación científica -->
                <td style="mso-number-format:'\@'; text-align: center;"><?= htmlspecialchars($row['banco_cuenta'] ?? '') ?></td>
                <td style="text-align: center;"><?= htmlspecialchars($row['banco_tipo_cuenta'] ?? '') ?></td>
                <td style="mso-number-format:'\@'; text-align: center;"><?= htmlspecialchars($row['nit'] ?? '') ?></td>
                <td class="money"><?= number_format((float)$row['valor_pagar'], 2, ',', '.') ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($row['cliente'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
