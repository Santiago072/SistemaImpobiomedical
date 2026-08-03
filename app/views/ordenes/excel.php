<?php
/**
 * Vista: Exportar a Excel - Reporte de Órdenes de Compra
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
    <title>Reporte de Órdenes de Compra</title>
    <style>
        body { font-family: Arial, sans-serif; }
        br { mso-data-placement: same-cell; }
        td { mso-data-placement: same-cell; }
        
        /* ── Encabezado Corporativo Excel ── */
        table.hdr-table { border: 2px solid #10757e; width: 100%; margin-bottom: 15px; border-collapse: collapse; background: #ffffff; }
        
        table.data-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 15px; }
        table.data-table th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 12px; text-transform: uppercase; border: 1.5px solid #0d5c63; padding: 8px; text-align: center; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        .filter-table { border-collapse: collapse; margin-bottom: 15px; width: 50%; }
        .filter-table th { background-color: #1f3864; color: #ffffff; border: 1px solid #1f3864; padding: 6px; font-size: 11px; text-align: left; }
        .filter-table td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        
        .money { font-weight: bold; color: #059669; text-align: right; }
    </style>
</head>
<body>

    <!-- ENCABEZADO CORPORATIVO EN EXCEL CON ALTURA DE FILA EXPLÍCITA -->
    <table class="hdr-table">
        <!-- Barra superior teal -->
        <tr height="6" style="background:#10757e; height:6pt;">
            <td colspan="8" style="background:#10757e; height:6pt; padding:0; border:none;"></td>
        </tr>

        <!-- Fila principal del encabezado con altura fija de 95pt para que Excel expanda la fila 2 -->
        <tr height="95" style="height:95pt; background:#ffffff;">
            <!-- COL 1: Logo IMPOMIN + Datos Corporativos -->
            <td colspan="3" style="width:37.5%; text-align:left; vertical-align:top; padding:10px 12px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" width="120" height="28" style="display:block; margin-bottom:4px;"><br style="mso-data-placement:same-cell;">
                <?php endif; ?>
                <font face="Arial" size="2" color="#1f3864"><b>IMPOMIN S.A.S</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#10757e"><b>Nit. 900.535.843-3</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">Cra 10 No. 9-80 Barrio Cooperativa - Florencia</font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">Calle 33A No 71 A 27 - Laureles - Medellín</font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">impobiomedical@impomin.com</font>
            </td>

            <!-- COL 2: Título, Subtítulo y Metadatos -->
            <td colspan="3" style="width:37.5%; text-align:center; vertical-align:middle; padding:10px 12px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <font face="Arial" size="4" color="#1f3864"><b>REPORTE DE ÓRDENES DE COMPRA</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="2" color="#10757e"><b>Sistema Impobiomedical</b></font><br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">
                <table align="center" style="margin:0 auto; border-collapse:collapse;">
                    <tr>
                        <td style="background-color:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:4px 12px; font-size:11px; font-weight:bold; text-align:center;">
                            Fecha: <?= $fechaSoloFecha ?> &nbsp;|&nbsp; Registros: <?= $totalRegistros ?>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- COL 3: Logo IMPOBIOMEDICAL -->
            <td colspan="2" style="width:25%; text-align:center; vertical-align:middle; padding:10px 12px; border:none;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" width="160" height="40" style="display:block; margin:0 auto;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Barra inferior teal -->
        <tr height="5" style="background:#10757e; height:5pt;">
            <td colspan="8" style="background:#10757e; height:5pt; padding:0; border:none;"></td>
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
