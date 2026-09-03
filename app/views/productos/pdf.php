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
    function imgBase64(string $ruta): string {
        if (!file_exists($ruta) || !is_readable($ruta)) return '';
        try {
            $info = @getimagesize($ruta);
            if (!$info || empty($info['mime'])) return '';
            $mime = $info['mime'];

            // Si es WebP, convertir a JPEG para DomPDF
            if ($mime === 'image/webp' && function_exists('imagecreatefromwebp') && function_exists('imagejpeg')) {
                $im = @imagecreatefromwebp($ruta);
                if ($im) {
                    ob_start();
                    imagejpeg($im, null, 80);
                    $jpegData = ob_get_clean();
                    imagedestroy($im);
                    if ($jpegData) {
                        return 'data:image/jpeg;base64,' . base64_encode($jpegData);
                    }
                }
                return '';
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
@page {
    margin: 15px 18px 25px 18px;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8.5px;
    color: #1f2937;
}

/* Master Table para encabezado repetitivo limpio */
table.master-table {
    width: 100%;
    border-collapse: collapse;
    border: none;
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
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 8px;
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
table.prod-table thead tr {
    background-color: #10757e;
}
table.prod-table th {
    background-color: #10757e;
    color: #ffffff;
    font-weight: bold;
    padding: 6px 4px;
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
    padding: 5px 6px;
    vertical-align: middle;
    font-size: 8px;
}

/* Filas alternas */
table.prod-table tbody tr:nth-child(even) { background-color: #f8fafc; }

td.col-codigo { font-weight: bold; color: #0f766e; text-align: center; width: 14%; font-size: 8px; }
td.col-categoria { color: #475569; font-weight: bold; text-align: center; width: 16%; font-size: 7.5px; }
td.col-img { text-align: center; vertical-align: middle; padding: 3px; width: 12%; }
td.col-nombre { font-weight: bold; color: #1e293b; text-align: left; vertical-align: top; width: 26%; }
td.col-desc { text-align: left; font-size: 7.5px; color: #475569; line-height: 1.3; vertical-align: top; width: 32%; word-wrap: break-word; }

.img-thumb {
    max-width: 55px;
    max-height: 55px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
    border: 1px solid #e2e8f0;
}
.no-img {
    font-size: 7px;
    color: #94a3b8;
    font-style: italic;
}

.footer-table {
    margin-top: 10px;
    border-top: 1px solid #cbd5e1;
    padding-top: 4px;
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

<table class="master-table">
  <thead style="display: table-header-group;">
    <tr>
      <td style="border:none; padding:0;">
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
      </td>
    </tr>
  </thead>

  <tbody>
    <tr>
      <td style="border:none; padding:0;">
        <table class="prod-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Código</th>
                    <th style="width: 16%;">Categoría</th>
                    <th style="width: 12%;">Imagen</th>
                    <th style="width: 26%;">Nombre</th>
                    <th style="width: 32%;">Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): 
                    $fotoNombre = $p['foto'] ?? '';
                    $imgProd = '';
                    if (!empty($fotoNombre) && file_exists($uploadsDir . $fotoNombre)) {
                        $imgProd = imgBase64($uploadsDir . $fotoNombre);
                    }
                ?>
                <tr>
                    <td class="col-codigo"><?= htmlspecialchars($p['codigo_producto'] ?? '') ?></td>
                    <td class="col-categoria"><?= htmlspecialchars($p['categoria'] ?? '') ?></td>
                    <td class="col-img">
                        <?php if ($imgProd): ?>
                            <img src="<?= $imgProd ?>" class="img-thumb">
                        <?php else: ?>
                            <span class="no-img">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-nombre"><?= htmlspecialchars($p['titulo'] ?? '') ?></td>
                    <td class="col-desc"><?= nl2br(htmlspecialchars($p['descripcion'] ?? '')) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </td>
    </tr>
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
