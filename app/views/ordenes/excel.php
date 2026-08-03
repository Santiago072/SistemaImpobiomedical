<?php
/**
 * Vista: Exportar a Excel - Reporte de Órdenes de Compra
 * Variables: $datosExcel, $filtros
 */

$logoDir = dirname(__DIR__, 3) . '/logo/';
$baseUrl = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
if (strpos($baseUrl, 'http') !== 0) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl  = $protocol . $host . '/' . ltrim($baseUrl, '/');
}

if (!function_exists('imgBase64OrUrl')) {
    function imgBase64OrUrl(string $localPath, string $httpUrl): string {
        if (file_exists($localPath)) {
            $ext  = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
            $d    = @file_get_contents($localPath);
            if ($d) {
                return 'data:image/' . $mime . ';base64,' . base64_encode($d);
            }
        }
        return $httpUrl;
    }
}

$imgLogoImp = imgBase64OrUrl($logoDir . 'logoimp.png', rtrim($baseUrl, '/') . '/logo/logoimp.png');
$imgLogoPdf = imgBase64OrUrl($logoDir . 'logopdf.png', rtrim($baseUrl, '/') . '/logo/logopdf.png');

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
        .hdr-table { border: 2px solid #10757e; width: 100%; margin-bottom: 15px; border-collapse: collapse; background: #ffffff; }
        
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

    <!-- ENCABEZADO CORPORATIVO EN EXCEL CON REGLA MSO PARA EVITAR MULTIPLICACIÓN DE FILAS -->
    <table class="hdr-table">
        <!-- Barra superior teal -->
        <tr height="5" style="background:#10757e; height:5px;">
            <td colspan="8" style="background:#10757e; height:5px; padding:0; border:none;"></td>
        </tr>

        <tr>
            <!-- COL 1: Logo IMPOMIN + Datos -->
            <td colspan="3" style="width:35%; text-align:left; vertical-align:top; padding:8px 10px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <?php if (!empty($imgLogoImp)): ?>
                    <img src="<?= $imgLogoImp ?>" width="120" height="28" style="display:block; margin-bottom:4px;"><br style="mso-data-placement:same-cell;">
                <?php endif; ?>
                <span style="font-size:11px; font-weight:bold; color:#1f3864;">IMPOMIN S.A.S</span><br style="mso-data-placement:same-cell;">
                <span style="font-size:9.5px; font-weight:bold; color:#10757e;">Nit. 900.535.843-3</span><br style="mso-data-placement:same-cell;">
                <span style="font-size:8px; color:#475569;">Cra 10 No. 9-80 Barrio Cooperativa - Florencia</span><br style="mso-data-placement:same-cell;">
                <span style="font-size:8px; color:#475569;">Calle 33A No 71 A 27 - Laureles - Medellín</span><br style="mso-data-placement:same-cell;">
                <span style="font-size:8px; color:#475569;">impobiomedical@impomin.com</span>
            </td>

            <!-- COL 2: Título y Fecha -->
            <td colspan="3" style="width:35%; text-align:center; vertical-align:middle; padding:8px 10px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <span style="font-size:14px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">REPORTE DE ÓRDENES DE COMPRA</span><br style="mso-data-placement:same-cell;">
                <span style="font-size:10px; font-weight:bold; color:#10757e;">Sistema Impobiomedical</span><br style="mso-data-placement:same-cell;"><br style="mso-data-placement:same-cell;">
                <span style="display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:3px 10px; border-radius:10px; font-size:8.5px; font-weight:bold;">
                    Fecha: <?= $fechaSoloFecha ?> | Registros: <?= $totalRegistros ?>
                </span>
            </td>

            <!-- COL 3: Logo IMPOBIOMEDICAL -->
            <td colspan="2" style="width:30%; text-align:center; vertical-align:middle; padding:8px 10px; border:none;">
                <?php if (!empty($imgLogoPdf)): ?>
                    <img src="<?= $imgLogoPdf ?>" width="140" height="35" style="display:block; margin:0 auto;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Barra inferior teal -->
        <tr height="4" style="background:#10757e; height:4px;">
            <td colspan="8" style="background:#10757e; height:4px; padding:0; border:none;"></td>
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
