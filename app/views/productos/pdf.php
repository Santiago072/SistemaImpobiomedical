<?php
/**
 * Vista: PDF de Catálogo de Productos — IMPOMIN S.A.S / Impobiomedical
 * Generado con DomPDF optimizado para catálogo con imágenes.
 */

// Limpiar cualquier buffer previo
while (ob_get_level() > 0) { ob_end_clean(); }

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

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
body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5px; color: #1f2937; padding: 15px 18px; }

/* ── Encabezado Corporativo ── */
.hdr-wrap {
    border: 1.5px solid #10757e;
    border-radius: 4px;
    margin-bottom: 12px;
    overflow: hidden;
    background: #ffffff;
}

/* ── Filtros Badges ── */
.meta-bar {
    margin-bottom: 10px;
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

/* ── Tabla Principal ── */
table.prod-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    border: 1.5px solid #10757e;
}
table.prod-table thead {
    display: table-header-group;
}
table.prod-table tr {
    page-break-inside: avoid;
}
table.prod-table th {
    background-color: #10757e;
    color: #ffffff;
    font-weight: bold;
    padding: 7px 6px;
    text-align: center;
    font-size: 9px;
    text-transform: uppercase;
    border: 1.5px solid #0d5c63;
}
table.prod-table td {
    border: 1.5px solid #cbd5e1;
    padding: 6px 6px;
    vertical-align: middle;
    font-size: 8px;
}

/* Filas alternas */
table.prod-table tbody tr:nth-child(even) { background-color: #f8fafc; }

td.col-codigo { font-weight: bold; color: #0f766e; text-align: center; font-size: 8.5px; }
td.col-categoria { color: #475569; font-style: italic; font-weight: bold; text-align: center; }
td.col-img { text-align: center; vertical-align: middle; padding: 4px; }
td.col-nombre { font-weight: bold; color: #1e293b; text-align: left; vertical-align: top; }
td.col-desc { text-align: left; font-size: 8px; color: #475569; line-height: 1.35; word-wrap: break-word; vertical-align: top; }

.img-thumb {
    max-width: 65px;
    max-height: 65px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 3px;
    border: 1px solid #e2e8f0;
}
.no-img {
    font-size: 7px;
    color: #94a3b8;
    font-style: italic;
    display: inline-block;
    padding: 4px;
}

.footer-table { margin-top: 14px; border-top: 1px solid #cbd5e1; padding-top: 5px; width:100%; }
.footer-table td { font-size: 7.5px; color: #64748b; border:none; text-align: left; }
</style>
</head>
<body>

<!-- ENCABEZADO CORPORATIVO CON LOGOS -->
<div class="hdr-wrap">
  <div style="background:#10757e; height:5px;"></div>
  <table style="width:100%; border-collapse:collapse; background:#ffffff;">
    <tr>
      <!-- COL 1: Logo IMPOMIN + Datos -->
      <td style="width:34%; padding:7px 10px; vertical-align:top; border-right:1px solid #e2e8f0; text-align:left;">
        <?php if ($imgLogoImp): ?>
          <img src="<?= $imgLogoImp ?>" style="height:32px; object-fit:contain; margin-bottom:3px;"><br>
        <?php endif; ?>
        <div style="font-size:10px; font-weight:bold; color:#1f3864;">IMPOMIN S.A.S</div>
        <div style="font-size:8px; font-weight:bold; color:#10757e;">Nit. 900.535.843-3</div>
        <div style="font-size:7px; color:#475569; margin-top:2px; line-height:1.2;">
          Cra 10 No. 9-80 Barrio Cooperativa - Florencia<br>
          Calle 33A No 71 A 27 - Laureles - Medellín<br>
          impobiomedical@impomin.com
        </div>
      </td>

      <!-- COL 2: Título y Filtros -->
      <td style="width:36%; text-align:center; vertical-align:middle; padding:6px 8px; border-right:1px solid #e2e8f0;">
        <div style="font-size:13px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">Catálogo de Productos</div>
        <div style="font-size:9px; font-weight:bold; color:#10757e; margin-top:1px;">Sistema Impobiomedical</div>
        <div style="font-size:8px; color:#64748b; margin-top:4px;">Fecha: <?= $fechaSoloFecha ?> | Registros: <?= $totalRegistros ?></div>
      </td>

      <!-- COL 3: Logo IMPOBIOMEDICAL -->
      <td style="width:30%; text-align:center; vertical-align:middle; padding:6px 8px;">
        <?php if ($imgLogoPdf): ?>
          <img src="<?= $imgLogoPdf ?>" style="max-width:170px; max-height:65px; object-fit:contain;">
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <div style="background:#10757e; height:3px;"></div>
</div>

<!-- MOSTRAR FILTROS SI SE ESTÁ EN UNA CATEGORÍA O BÚSQUEDA -->
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
            <th style="width: 12%;">Código</th>
            <th style="width: 15%;">Categoría</th>
            <th style="width: 13%;">Imagen</th>
            <th style="width: 25%;">Nombre</th>
            <th style="width: 35%;">Descripción</th>
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
$options->set('defaultFont', 'Arial');
$options->set('isPhpEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

while (ob_get_level()) ob_end_clean();

$nombreArchivo = 'Catalogo_Productos.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit();
