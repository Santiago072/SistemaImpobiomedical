<?php
/**
 * Vista: Exportar Productos a Excel
 * Variables: $productos, $busqueda, $categoriaSel
 */

if (!function_exists('imgBase64')) {
    function imgBase64(string $ruta): string {
        if (!file_exists($ruta)) return '';
        $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
        $d    = @file_get_contents($ruta);
        if (!$d) return '';
        return 'data:image/' . $mime . ';base64,' . base64_encode($d);
    }
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgBase64($logoDir . 'logopdf.png');
$imgLogoImp = imgBase64($logoDir . 'logoimp.png');

$fechaGenerado = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .hdr-table { border: 2px solid #10757e; width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .hdr-table td { border: none; padding: 6px; }
        
        table.data-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 15px; }
        table.data-table th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 13px; text-transform: uppercase; border: 1.5px solid #0d5c63; padding: 8px; text-align: center; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        .filter-table { border-collapse: collapse; margin-bottom: 15px; width: 50%; }
        .filter-table th { background-color: #1f3864; color: #ffffff; border: 1px solid #1f3864; padding: 6px; font-size: 11px; text-align: left; }
        .filter-table td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        
        .codigo { font-weight: bold; color: #0f766e; text-align: center; }
        .tag-activo { color: #166534; font-weight: bold; }
        .tag-inactivo { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>

    <!-- ENCABEZADO CORPORATIVO EN EXCEL -->
    <table class="hdr-table">
        <tr>
            <td colspan="2" style="width:35%; text-align:left; vertical-align:top;">
                <?php if ($imgLogoImp): ?>
                    <img src="<?= $imgLogoImp ?>" height="40"><br>
                <?php endif; ?>
                <strong style="color:#1f3864; font-size:12px;">IMPOMIN S.A.S</strong><br>
                <span style="color:#10757e; font-size:10px; font-weight:bold;">Nit. 900.535.843-3</span><br>
                <span style="color:#555; font-size:9px;">Florencia - Caquetá / Medellín - Colombia</span>
            </td>
            <td colspan="2" style="width:35%; text-align:center; vertical-align:middle;">
                <span style="font-size:16px; font-weight:bold; color:#1f3864;">CATÁLOGO DE PRODUCTOS</span><br>
                <span style="font-size:11px; font-weight:bold; color:#10757e;">Sistema Impobiomedical</span><br>
                <span style="font-size:10px; color:#666;">Generado el: <?= $fechaGenerado ?></span>
            </td>
            <td colspan="2" style="width:30%; text-align:center; vertical-align:middle;">
                <?php if ($imgLogoPdf): ?>
                    <img src="<?= $imgLogoPdf ?>" height="50">
                <?php endif; ?>
            </td>
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
                <td style="vertical-align: middle;"><?= nl2br(htmlspecialchars($row['descripcion'] ?? '')) ?></td>
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
