<?php
/**
 * Vista: Exportar Productos a Excel
 * Variables: $productos, $busqueda, $categoriaSel
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
    <title>Catálogo de Productos</title>
    <style>
        body { font-family: Arial, sans-serif; }
        
        /* ── Encabezado Corporativo Excel ── */
        .hdr-table { border: 2px solid #10757e; width: 100%; margin-bottom: 15px; border-collapse: collapse; background: #ffffff; }
        .hdr-table td { padding: 4px 8px; border: none; vertical-align: middle; }
        
        table.data-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 15px; }
        table.data-table th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 12px; text-transform: uppercase; border: 1.5px solid #0d5c63; padding: 8px; text-align: center; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        .filter-table { border-collapse: collapse; margin-bottom: 15px; width: 50%; }
        .filter-table th { background-color: #1f3864; color: #ffffff; border: 1px solid #1f3864; padding: 6px; font-size: 11px; text-align: left; }
        .filter-table td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        
        .codigo { font-weight: bold; color: #0f766e; text-align: center; }
        .tag-activo { color: #166534; font-weight: bold; text-align: center; }
        .tag-inactivo { color: #991b1b; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

    <!-- ENCABEZADO CORPORATIVO EN EXCEL CON ESTRUCTURA LIMPIA Y SEPARADA EN FILAS -->
    <table class="hdr-table">
        <!-- Fila 1: Barra de acento superior teal -->
        <tr style="background:#10757e; height:6px;">
            <td colspan="6" style="background:#10757e; height:6px; padding:0; border:none;"></td>
        </tr>
        
        <!-- Fila 2: Fila dedicada a los Logos -->
        <tr>
            <td colspan="2" style="text-align:left; vertical-align:middle; padding:6px 10px;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" height="36" style="height:36px;">
                <?php endif; ?>
            </td>
            <td colspan="2" style="text-align:center; vertical-align:middle; padding:6px 10px;">
                <span style="font-size:15px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">CATÁLOGO DE PRODUCTOS</span>
            </td>
            <td colspan="2" style="text-align:right; vertical-align:middle; padding:6px 10px;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" height="42" style="height:42px;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Fila 3: Fila dedicada a la Información textual -->
        <tr>
            <td colspan="2" style="text-align:left; vertical-align:top; padding:0 10px 8px 10px;">
                <strong style="color:#1f3864; font-size:11px;">IMPOMIN S.A.S</strong><br>
                <span style="color:#10757e; font-size:9.5px; font-weight:bold;">Nit. 900.535.843-3</span><br>
                <span style="color:#64748b; font-size:8.5px;">Florencia - Caquetá / Medellín - Colombia</span>
            </td>
            <td colspan="2" style="text-align:center; vertical-align:top; padding:0 10px 8px 10px;">
                <span style="font-size:10px; font-weight:bold; color:#10757e;">Sistema Impobiomedical</span><br><br>
                <span style="display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:2px 8px; border-radius:10px; font-size:8.5px; font-weight:bold;">
                    Fecha: <?= $fechaSoloFecha ?>
                </span>
            </td>
            <td colspan="2" style="text-align:right; vertical-align:top; padding:0 10px 8px 10px;">
                <strong style="color:#10757e; font-size:11px;">IMPOBIOMEDICAL</strong><br>
                <span style="color:#64748b; font-size:8.5px;">impobiomedical@impomin.com</span>
            </td>
        </tr>

        <!-- Fila 4: Barra de acento inferior teal -->
        <tr style="background:#10757e; height:4px;">
            <td colspan="6" style="background:#10757e; height:4px; padding:0; border:none;"></td>
        </tr>
    </table>

    <?php if (!empty($busqueda) || !empty($categoriaSel)): ?>
    <table class="filter-table">
        <thead>
            <tr>
                <th colspan="2">Filtros Aplicados</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($busqueda)): ?>
            <tr><td><strong>Búsqueda:</strong></td><td><?= htmlspecialchars($busqueda) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($categoriaSel)): ?>
            <tr><td><strong>Categoría:</strong></td><td><?= htmlspecialchars($categoriaSel) ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>Código del Producto</th>
                <th>Categoría</th>
                <th>Nombre del Producto</th>
                <th>Descripción</th>
                <th>¿Aplica IVA?</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $row): ?>
            <tr>
                <td class="codigo" style="mso-number-format:'\@'; text-align: center; vertical-align: middle;"><?= htmlspecialchars($row['codigo_producto'] ?? '') ?></td>
                <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($row['categoria'] ?? '') ?></td>
                <td style="text-align: center; vertical-align: middle;"><strong><?= htmlspecialchars($row['titulo'] ?? '') ?></strong></td>
                <td style="text-align: left; vertical-align: middle;"><?= nl2br(htmlspecialchars($row['descripcion'] ?? '')) ?></td>
                <td style="text-align: center; vertical-align: middle;"><?= (strtolower($row['iva'] ?? '') === 'si') ? 'Sí' : 'No' ?></td>
                <td class="<?= (strtolower($row['estado'] ?? '') === 'activo') ? 'tag-activo' : 'tag-inactivo' ?>" style="text-align: center; vertical-align: middle;">
                    <?= (strtolower($row['estado'] ?? '') === 'activo') ? 'Activo' : 'Inactivo' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
