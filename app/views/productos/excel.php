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
$totalRegistros = count($productos ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        body { font-family: Arial, sans-serif; }
        br { mso-data-placement: same-cell; }
        td { mso-data-placement: same-cell; }
        
        /* ── Encabezado Corporativo Excel ── */
        table.hdr-table { border-collapse: collapse; width: 100%; margin-bottom: 10px; background: #ffffff; }
        
        table.data-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; margin-top: 10px; }
        table.data-table th { background-color: #10757e; color: #ffffff; font-weight: bold; font-size: 12px; text-transform: uppercase; border: 1.5px solid #0d5c63; padding: 8px; text-align: center; }
        table.data-table td { border: 1px solid #cbd5e1; padding: 8px; font-size: 11px; vertical-align: middle; }
        
        .filter-table { border-collapse: collapse; margin-bottom: 12px; width: 50%; }
        .filter-table th { background-color: #1f3864; color: #ffffff; border: 1px solid #1f3864; padding: 6px; font-size: 11px; text-align: left; }
        .filter-table td { border: 1px solid #cbd5e1; padding: 6px; font-size: 11px; }
        
        .codigo { font-weight: bold; color: #0f766e; text-align: center; }
        .tag-activo { color: #166534; font-weight: bold; text-align: center; }
        .tag-inactivo { color: #991b1b; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

    <!-- ENCABEZADO CORPORATIVO EN FILAS NATIVAS DE EXCEL (A a F: COLSPAN 3 - 1 - 2) -->
    <table class="hdr-table">
        <!-- 1. Margen superior para que el encabezado no se pegue al marco de Excel -->
        <tr height="10" style="height:10pt;">
            <td colspan="6" style="border:none;"></td>
        </tr>

        <!-- 2. Barra superior teal con marco superior, izquierdo y derecho -->
        <tr height="6" style="background:#10757e; height:6pt;">
            <td colspan="6" style="background:#10757e; height:6pt; padding:0; border-top:1.5px solid #10757e; border-left:1.5px solid #10757e; border-right:1.5px solid #10757e; border-bottom:none;"></td>
        </tr>

        <!-- 3. Contenido Principal del Encabezado (Altura fija de 110pt) -->
        <tr height="110" style="height:110pt; background:#ffffff;">
            <!-- COL 1: Logo IMPOMIN + Datos Corporativos (colspan=3: A+B+C ~440px de ancho) -->
            <td colspan="3" style="width:40%; text-align:left; vertical-align:top; padding:8px 12px; border-left:1.5px solid #10757e; border-right:1px solid #cbd5e1; border-top:none; border-bottom:none;">
                <?php if (!empty($logoImpUrl)): ?>
                    <img src="<?= $logoImpUrl ?>" width="130" height="30" style="display:block; margin-bottom:4px;"><br style="mso-data-placement:same-cell;">
                <?php endif; ?>
                <font face="Arial" size="2" color="#1f3864"><b>IMPOMIN S.A.S</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#10757e"><b>Nit. 900.535.843-3</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">Cra 10 No. 9-80 Barrio Cooperativa - Florencia</font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">Calle 33A No 71 A 27 - Laureles - Medellín</font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#475569">impobiomedical@impomin.com</font>
            </td>

            <!-- COL 2: Título, Subtítulo y Metadatos (colspan=1: Columna D ~550px de ancho) -->
            <td colspan="1" style="width:45%; text-align:center; vertical-align:middle; padding:8px 12px; border-right:1px solid #cbd5e1; border-top:none; border-bottom:none; border-left:none;">
                <font face="Arial" size="4" color="#1f3864"><b>CATÁLOGO DE PRODUCTOS</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="2" color="#10757e"><b>Sistema Impobiomedical</b></font><br style="mso-data-placement:same-cell;">
                <font face="Arial" size="1" color="#64748b">Fecha: <?= $fechaSoloFecha ?> &nbsp;|&nbsp; Registros: <?= $totalRegistros ?></font>
            </td>

            <!-- COL 3: Logo IMPOBIOMEDICAL (colspan=2: Columnas E+F ~160px de ancho) -->
            <td colspan="2" style="width:15%; text-align:center; vertical-align:middle; padding:8px 12px; border-right:1.5px solid #10757e; border-top:none; border-bottom:none; border-left:none;">
                <?php if (!empty($logoPdfUrl)): ?>
                    <img src="<?= $logoPdfUrl ?>" width="140" height="35" style="display:block; margin:0 auto;">
                <?php endif; ?>
            </td>
        </tr>

        <!-- 4. Barra inferior teal con marco inferior, izquierdo y derecho -->
        <tr height="5" style="background:#10757e; height:5pt;">
            <td colspan="6" style="background:#10757e; height:5pt; padding:0; border-bottom:1.5px solid #10757e; border-left:1.5px solid #10757e; border-right:1.5px solid #10757e; border-top:none;"></td>
        </tr>

        <!-- 5. Espacio de separación antes de los filtros o tabla de datos -->
        <tr height="10" style="height:10pt;">
            <td colspan="6" style="border:none;"></td>
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
