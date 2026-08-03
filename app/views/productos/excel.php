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

    <!-- ENCABEZADO CORPORATIVO EN EXCEL IDÉNTICO AL HEADER DEL PDF -->
    <table class="hdr-table">
        <!-- Barra superior teal -->
        <tr height="5" style="background:#10757e; height:5px;">
            <td colspan="6" style="background:#10757e; height:5px; padding:0; border:none;"></td>
        </tr>

        <tr>
            <!-- COL 1: Logo IMPOMIN + Datos -->
            <td colspan="2" style="width:34%; text-align:left; vertical-align:top; padding:8px 10px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" width="120" height="28" style="display:block; margin-bottom:4px;"><br>
                <?php endif; ?>
                <strong style="color:#1f3864; font-size:11px;">IMPOMIN S.A.S</strong><br>
                <strong style="color:#10757e; font-size:9.5px;">Nit. 900.535.843-3</strong><br>
                <span style="color:#475569; font-size:8px; line-height:1.2;">
                    Cra 10 No. 9-80 Barrio Cooperativa - Florencia<br>
                    Calle 33A No 71 A 27 - Laureles - Medellín<br>
                    impobiomedical@impomin.com
                </span>
            </td>

            <!-- COL 2: Título y Fecha -->
            <td colspan="2" style="width:36%; text-align:center; vertical-align:middle; padding:8px 10px; border-right:1px solid #e2e8f0; border-top:none; border-bottom:none; border-left:none;">
                <strong style="font-size:14px; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">CATÁLOGO DE PRODUCTOS</strong><br>
                <strong style="font-size:10px; color:#10757e;">Sistema Impobiomedical</strong><br><br>
                <span style="display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:3px 10px; border-radius:10px; font-size:8.5px; font-weight:bold;">
                    Fecha: <?= $fechaSoloFecha ?>
                </span>
            </td>

            <!-- COL 3: Logo IMPOBIOMEDICAL -->
            <td colspan="2" style="width:30%; text-align:center; vertical-align:middle; padding:8px 10px; border:none;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" width="140" height="35" style="display:block; margin:0 auto;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Barra inferior teal -->
        <tr height="4" style="background:#10757e; height:4px;">
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
