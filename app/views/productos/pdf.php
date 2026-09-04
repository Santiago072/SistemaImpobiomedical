<?php
/**
 * Vista: PDF de Catálogo de Productos — IMPOMIN S.A.S / Impobiomedical
 * Generado con DomPDF optimizado para catálogo con imágenes y salto de página fluido.
 */

// Limpiar cualquier buffer previo
while (ob_get_level() > 0) { ob_end_clean(); }

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if (!function_exists('imgBase64')) {
    function imgBase64(string $ruta, int $maxWidth = 120, int $maxHeight = 120): string {
        if (!file_exists($ruta) || !is_readable($ruta)) return '';
        try {
            $info = @getimagesize($ruta);
            if (!$info || empty($info['mime'])) return '';
            $mime = $info['mime'];
            $origW = $info[0] ?? 0;
            $origH = $info[1] ?? 0;

            // Si GD está disponible, redimensionar imágenes grandes para evitar saturar memoria en Dompdf
            if (function_exists('imagecreatetruecolor') && $origW > 0 && $origH > 0) {
                $src = null;
                if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                    $src = @imagecreatefromjpeg($ruta);
                } elseif ($mime === 'image/png') {
                    $src = @imagecreatefrompng($ruta);
                } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                    $src = @imagecreatefromwebp($ruta);
                } elseif ($mime === 'image/gif') {
                    $src = @imagecreatefromgif($ruta);
                }

                if ($src) {
                    // Calcular dimensiones proporcionales
                    $ratio = min($maxWidth / $origW, $maxHeight / $origH, 1.0);
                    $newW = (int)round($origW * $ratio);
                    $newH = (int)round($origH * $ratio);

                    $dst = imagecreatetruecolor($newW, $newH);
                    // Fondo blanco limpio
                    $bg = imagecolorallocate($dst, 255, 255, 255);
                    imagefilledrectangle($dst, 0, 0, $newW, $newH, $bg);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                    imagedestroy($src);

                    ob_start();
                    imagejpeg($dst, null, 75);
                    $thumbData = ob_get_clean();
                    imagedestroy($dst);

                    if ($thumbData) {
                        return 'data:image/jpeg;base64,' . base64_encode($thumbData);
                    }
                }
            }

            if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                return '';
            }

            $d = @file_get_contents($ruta);
            if (!$d) return '';
            return 'data:' . $mime . ';base64,' . base64_encode($d);
        } catch (\Throwable $e) {
            return '';
        }
    }
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$uploadsDir = dirname(__DIR__, 3) . '/uploads/';

$imgLogoPdf = imgBase64($logoDir . 'logopdf.png');
$imgLogoImp = imgBase64($logoDir . 'logoimp.png');

$fechaSoloFecha = date('d/m/Y');
$totalRegistros = count($productos ?? []);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Catálogo de Productos — Impobiomedical</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5px;
    color: #1f2937;
    padding: 20px 24px;
    background: #ffffff;
}

/* ── Encabezado Corporativo ── */
.hdr-wrap {
    border: 1.5px solid #10757e;
    border-radius: 4px;
    margin-bottom: 8px;
    overflow: hidden;
    background: #ffffff;
}

/* ── Filtros Badges ── */
.meta-bar {
    margin-bottom: 8px;
    text-align: center;
}
.filter-badge {
    display: inline-block;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 8.5px;
    font-weight: bold;
    margin: 0 4px;
}

/* ── Tabla Principal de Productos ── */
table.prod-table {
    width: 100%;
    border-collapse: collapse;
    border: 1.5px solid #10757e;
    page-break-inside: auto;
}
table.prod-table thead {
    display: table-header-group;
}
table.prod-table thead tr {
    background-color: #10757e;
}
table.prod-table th {
    background-color: #10757e;
    color: #ffffff;
    font-weight: bold;
    padding: 7px 6px;
    text-align: center;
    font-size: 8.5px;
    text-transform: uppercase;
    border: 1px solid #0d5c63;
}
table.prod-table tr {
    page-break-inside: avoid;
}
table.prod-table td {
    border: 1px solid #cbd5e1;
    padding: 6px 8px;
    vertical-align: middle;
    font-size: 8px;
}

/* Filas alternas */
table.prod-table tbody tr:nth-child(even) { background-color: #f8fafc; }

/* Separador de Categoría */
tr.cat-header-row td {
    background-color: #e6f4f5 !important;
    border-top: 1.5px solid #10757e;
    border-bottom: 1.5px solid #10757e;
    border-left: 4px solid #10757e;
    padding: 6px 10px;
    font-size: 9.5px;
    font-weight: bold;
    color: #0d5c63;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td.col-nombre {
    font-weight: bold;
    color: #1e293b;
    text-align: left;
    vertical-align: middle;
    line-height: 1.35;
    font-size: 8.5px;
}
td.col-iva {
    text-align: center;
    vertical-align: middle;
    font-weight: bold;
    font-size: 8px;
}
.badge-iva-si {
    display: inline-block;
    background-color: #e6f4ea;
    color: #137333;
    border: 1px solid #ceead6;
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: bold;
    font-size: 7.5px;
}
.badge-iva-no {
    display: inline-block;
    background-color: #f1f3f4;
    color: #5f6368;
    border: 1px solid #dadce0;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 7.5px;
}
td.col-img {
    text-align: center;
    vertical-align: middle;
    padding: 4px;
}
td.col-desc {
    text-align: left;
    font-size: 8px;
    color: #334155;
    line-height: 1.45;
    vertical-align: middle;
    word-wrap: break-word;
}

.img-thumb {
    max-width: 58px;
    max-height: 58px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 2px;
    background: #ffffff;
}
.no-img {
    font-size: 7.5px;
    color: #94a3b8;
    font-style: italic;
    display: inline-block;
    padding: 4px;
}

.footer-table {
    margin-top: 14px;
    border-top: 1px solid #cbd5e1;
    padding-top: 5px;
    width: 100%;
}
.footer-table td {
    font-size: 7.5px;
    color: #64748b;
    border: none;
    text-align: left;
}
</style>
</head>
<body>

<?php
$esCatalogoCompleto = isset($modo) && $modo === 'completo';
$incluirImagenes = !$esCatalogoCompleto;
$totalCols = $incluirImagenes ? 4 : 3;
?>

<!-- ENCABEZADO CORPORATIVO CON LOGOS -->
<div class="hdr-wrap">
  <div style="background:#10757e; height:4px;"></div>
  <table style="width:100%; border-collapse:collapse; background:#ffffff;">
    <tr>
      <!-- COL 1: Logo IMPOMIN + Datos -->
      <td style="width:34%; padding:6px 10px; vertical-align:top; border-right:1px solid #e2e8f0; text-align:left;">
        <?php if ($imgLogoImp): ?>
          <img src="<?= $imgLogoImp ?>" style="height:28px; margin-bottom:2px;"><br>
        <?php endif; ?>
        <div style="font-size:9.5px; font-weight:bold; color:#1f3864;">IMPOMIN S.A.S</div>
        <div style="font-size:7.5px; font-weight:bold; color:#10757e;">Nit. 900.535.843-3</div>
        <div style="font-size:6.8px; color:#475569; margin-top:1px; line-height:1.2;">
          Cra 10 No. 9-80 Barrio Cooperativa - Florencia<br>
          Calle 33A No 71 A 27 - Laureles - Medellín<br>
          impobiomedical@impomin.com
        </div>
      </td>

      <!-- COL 2: Título y Filtros -->
      <td style="width:36%; text-align:center; vertical-align:middle; padding:5px 8px; border-right:1px solid #e2e8f0;">
        <div style="font-size:12px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">Catálogo de Productos</div>
        <div style="font-size:8.5px; font-weight:bold; color:#10757e; margin-top:1px;">Sistema Impobiomedical</div>
        <div style="font-size:7.5px; color:#64748b; margin-top:3px;">Fecha: <?= $fechaSoloFecha ?> | Registros: <?= $totalRegistros ?></div>
      </td>

      <!-- COL 3: Logo IMPOBIOMEDICAL -->
      <td style="width:30%; text-align:center; vertical-align:middle; padding:5px 8px;">
        <?php if ($imgLogoPdf): ?>
          <img src="<?= $imgLogoPdf ?>" style="max-width:150px; max-height:55px;">
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <div style="background:#10757e; height:2px;"></div>
</div>

<?php if (!empty($categoriaSel) || !empty($busqueda)): ?>
<div class="meta-bar">
    <?php if (!empty($categoriaSel)): ?>
        <span class="filter-badge">Categoría: <?= htmlspecialchars($categoriaSel) ?></span>
    <?php endif; ?>
    <?php if (!empty($busqueda)): ?>
        <span class="filter-badge">Búsqueda: "<?= htmlspecialchars($busqueda) ?>"</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<table class="prod-table">
    <thead>
        <tr>
            <th style="width: <?= $incluirImagenes ? '30%' : '36%' ?>;">Nombre</th>
            <th style="width: <?= $incluirImagenes ? '12%' : '14%' ?>;">Aplica IVA</th>
            <?php if ($incluirImagenes): ?>
            <th style="width: 14%;">Imagen</th>
            <?php endif; ?>
            <th style="width: <?= $incluirImagenes ? '44%' : '50%' ?>;">Descripción</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $ultimaCategoria = null;
        foreach ($productos as $p): 
            $catActual = trim($p['categoria'] ?? '');
            if ($catActual === '') {
                $catActual = 'Sin Categoría';
            }

            // Separador de categoría cuando cambia o inicia un grupo nuevo
            if ($catActual !== $ultimaCategoria):
                $ultimaCategoria = $catActual;
        ?>
        <tr class="cat-header-row">
            <td colspan="<?= $totalCols ?>">
                &bull; <?= htmlspecialchars(mb_strtoupper($catActual)) ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php 
            $imgProd = '';
            if ($incluirImagenes) {
                $fotoNombre = $p['foto'] ?? '';
                if (!empty($fotoNombre) && file_exists($uploadsDir . $fotoNombre)) {
                    $imgProd = imgBase64($uploadsDir . $fotoNombre, 90, 90);
                }
            }
            $aplicaIva = strtolower($p['iva'] ?? '') === 'si';
            $porcIva = (float)($p['porcentaje_iva'] ?? 19);
        ?>
        <tr>
            <td class="col-nombre">
                <?= htmlspecialchars($p['titulo'] ?? '') ?>
            </td>
            <td class="col-iva">
                <?php if ($aplicaIva): ?>
                    <span class="badge-iva-si">Sí (<?= $porcIva ?>%)</span>
                <?php else: ?>
                    <span class="badge-iva-no">No</span>
                <?php endif; ?>
            </td>
            <?php if ($incluirImagenes): ?>
            <td class="col-img">
                <?php if ($imgProd): ?>
                    <img src="<?= $imgProd ?>" class="img-thumb">
                <?php else: ?>
                    <span class="no-img">Sin imagen</span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="col-desc"><?= nl2br(htmlspecialchars($p['descripcion'] ?? '')) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="footer-table">
  <tr>
    <td>Documento generado automáticamente por el Sistema Impobiomedical | Fecha: <?= $fechaSoloFecha ?></td>
  </tr>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isPhpEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');

try {
    $dompdf->render();
} catch (\Throwable $e) {
    error_log('Error renderizando catalogo PDF: ' . $e->getMessage());
    // Fallback de seguridad sin imagenes de productos si alguna imagen corrompida falla
    $htmlSinImgProd = preg_replace('/<img[^>]+uploads\/[^>]+>/i', '', $html);
    try {
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlSinImgProd, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    } catch (\Throwable $e2) {
        $htmlSinImg = preg_replace('/<img[^>]+>/i', '', $html);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlSinImg, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    }
}

while (ob_get_level()) ob_end_clean();

$nombreArchivo = 'Catalogo_Productos.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit();
