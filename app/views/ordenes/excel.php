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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Órdenes de Compra</title>
    <style>
        body { font-family: Arial, sans-serif; }
        
        /* ── Encabezado Corporativo Excel ── */
        .hdr-table { border: 2px solid #10757e; width: 100%; margin-bottom: 15px; border-collapse: collapse; background: #ffffff; }
        .hdr-table td { padding: 4px 6px; border: none; vertical-align: middle; }
        
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

    <!-- ENCABEZADO CORPORATIVO EN EXCEL (LOGOS Y TEXTOS EN COLUMNAS SEPARADAS) -->
    <table class="hdr-table">
        <!-- Fila 1: Barra de acento superior teal -->
        <tr height="6" style="background:#10757e; height:6px;">
            <td colspan="8" style="background:#10757e; height:6px; padding:0; border:none;"></td>
        </tr>
        
        <!-- Fila 2: Contenido del Encabezado -->
        <tr height="65" style="height:65px;">
            <!-- Col A: Logo IMPOMIN -->
            <td style="width:10%; text-align:left; vertical-align:middle; padding:6px; border:none;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" height="34" style="height:34px;">
                <?php endif; ?>
            </td>

            <!-- Col B-C: IMPOMIN Datos -->
            <td colspan="2" style="width:25%; text-align:left; vertical-align:middle; padding:6px; border:none;">
                <strong style="color:#1f3864; font-size:11px;">IMPOMIN S.A.S</strong><br>
                <span style="color:#10757e; font-size:9.5px; font-weight:bold;">Nit. 900.535.843-3</span><br>
                <span style="color:#475569; font-size:8px;">
                    Cra 10 No. 9-80 Barrio Coop. - Florencia<br>
                    Calle 33A No 71 A 27 - Medellín
                </span>
            </td>

            <!-- Col D-E: Título y Fecha -->
            <td colspan="2" style="width:30%; text-align:center; vertical-align:middle; padding:6px; border:none;">
                <span style="font-size:14px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">REPORTE DE ÓRDENES DE COMPRA</span><br>
                <span style="font-size:10px; font-weight:bold; color:#10757e;">Sistema Impobiomedical</span><br><br>
                <span style="display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:2px 8px; border-radius:10px; font-size:8.5px; font-weight:bold;">
                    Fecha: <?= $fechaSoloFecha ?>
                </span>
            </td>

            <!-- Col F-G: IMPOBIOMEDICAL Texto -->
            <td colspan="2" style="width:25%; text-align:right; vertical-align:middle; padding:6px; border:none;">
                <strong style="color:#10757e; font-size:11px;">IMPOBIOMEDICAL</strong><br>
                <span style="color:#475569; font-size:8px;">impobiomedical@impomin.com</span>
            </td>

            <!-- Col H: Logo IMPOBIOMEDICAL -->
            <td style="width:10%; text-align:right; vertical-align:middle; padding:6px; border:none;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" height="42" style="height:42px;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Fila 3: Barra de acento inferior teal -->
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
