<?php
/**
 * Vista: PDF de Orden de Compra
 * Diseño basado en la imagen de referencia de IMPOMIN S.A.S
 * Variables: $orden, $items, $forzar
 */

try {
    $data   = $ctrl->generarPdf();
} catch (Exception $e) {
    die('Error al generar la orden: ' . htmlspecialchars($e->getMessage()));
}

$orden  = $data['orden'];
$items  = $data['items'];
$forzar = $data['forzar'];

if (empty($items)) {
    die('La orden de compra no tiene ítems.');
}

// ── Datos de la orden ────────────────────────────────────────────────────────
$po                 = (int)$orden['numero_po'];
$fecha              = $orden['fecha'] ?? date('Y-m-d');
$proveedor          = $orden['proveedor'] ?? '';
$proveedorNit       = $orden['proveedor_nit'] ?? '';
$tipoContribuyente  = $orden['tipo_contribuyente'] ?? '';
$condicionesPago    = $orden['condiciones_pago'] ?? 'Según acuerdo';
$iva                = $orden['iva'] ?? '19%';
$deptCompras        = $orden['departamento_compras'] ?? '';
$nota               = $orden['nota'] ?? '';
$retencionPct       = (float)($orden['retencion'] ?? 2.5);

// ── Fecha formateada ──────────────────────────────────────────────────────────
date_default_timezone_set('America/Bogota');
$meses    = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$fechaObj = new DateTime($fecha);
$fechaFmt = $meses[(int)$fechaObj->format('n') - 1] . ' ' . $fechaObj->format('j') . ', ' . $fechaObj->format('Y');

// ── DomPDF ────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

function imgBase64OC(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
    $d    = @file_get_contents($ruta);
    return $d ? ('data:image/' . $mime . ';base64,' . base64_encode($d)) : '';
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgBase64OC($logoDir . 'logopdf.png');  // IMPOBIOMEDICAL grande
$imgLogoMin = imgBase64OC($logoDir . 'logoimp.png');  // IMPOMIN pequeño

// ── Cálculos ──────────────────────────────────────────────────────────────────
$subtotal   = 0;
$totalIva   = 0;

foreach ($items as $it) {
    $pu       = (float)$it['precio_unit'];
    $qty      = (int)$it['cantidad'];
    $pct      = (float)($it['porcentaje_iva'] ?? 19);
    $aplica   = strtolower($it['iva']) === 'si';
    $sub      = $pu * $qty;
    $ivaFila  = $aplica ? $sub * ($pct / 100) : 0;
    $subtotal += $sub;
    $totalIva += $ivaFila;
}

$retencion = $subtotal * ($retencionPct / 100);
$total     = $subtotal + $totalIva - $retencion;

// ── Imágenes de productos ─────────────────────────────────────────────────────
function getItemImg(string $foto): string {
    if (empty($foto)) return '';
    $ruta = dirname(__DIR__, 3) . '/uploads/' . $foto;
    return imgBase64OC($ruta);
}

ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>P.O. <?= $po ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: Arial, sans-serif;
    font-size: 9px;
    color: #000;
    padding: 16px 20px;
}
table { width:100%; border-collapse:collapse; }
.b   { border:1px solid #555; }
.bt  { border-top:1px solid #555; }
.bb  { border-bottom:1px solid #555; }
.bl  { border-left:1px solid #555; }
.br  { border-right:1px solid #555; }
.tc  { text-align:center; }
.tr  { text-align:right; }
.tl  { text-align:left; }
.vm  { vertical-align:middle; }
.vt  { vertical-align:top; }
.nw  { white-space:nowrap; }
.bold { font-weight:bold; }

/* Colores de la PO (azul corporativo + lila de las filas) */
.c-blue   { background:#3b5ea6; color:#fff; }
.c-lblue  { background:#b8c9e8; color:#000; }
.c-purple { background:#c5c8e8; color:#000; }
.c-header { background:#3b5ea6; color:#fff; font-weight:bold; }
.c-yellow { background:#e8e4b0; }

/* ── ENCABEZADO ── */
.hdr-outer {
    border:1.5px solid #555;
    margin-bottom:8px;
}
.hdr-top-row { display:flex; }
.hdr-logo-cell {
    width:22%; padding:6px 8px; border-right:1px solid #555;
    vertical-align:middle; text-align:center;
}
.hdr-title-cell {
    width:50%; padding:6px 8px; border-right:1px solid #555;
    vertical-align:middle; text-align:center;
}
.hdr-title-cell h1 { font-size:15px; font-weight:bold; letter-spacing:1px; }
.hdr-code-cell {
    width:28%; padding:4px 6px;
    vertical-align:top; font-size:8px;
}
.hdr-code-cell div { padding:2px 0; border-bottom:1px solid #ccc; }
.hdr-code-cell div:last-child { border-bottom:none; }
.hdr-code-label { font-weight:bold; }

/* Empresa info */
.empresa-wrap { padding:6px 0; margin-bottom:8px; }
.empresa-name { font-size:11px; font-weight:bold; }
.empresa-sub  { font-size:8.5px; color:#333; margin-top:2px; }

/* PO box */
.po-box { border:2px solid #555; }
.po-row  { display:flex; }
.po-label { background:#3b5ea6; color:#fff; font-weight:bold; font-size:9.5px; padding:4px 10px; width:55px; text-align:center; }
.po-value { font-size:14px; font-weight:bold; padding:2px 14px; text-align:center; }
.po-date-label { background:#3b5ea6; color:#fff; font-weight:bold; font-size:9.5px; padding:4px 10px; width:55px; text-align:center; }
.po-date-value { font-size:9.5px; padding:4px 14px; text-align:center; }

/* TO / TIPO */
.to-block { margin-bottom:8px; }
.to-row { display:flex; align-items:stretch; }
.to-label { background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 8px; width:80px; text-align:center; display:flex; align-items:center; justify-content:center; border:1px solid #555; }
.to-value { flex:1; padding:5px 10px; border:1px solid #555; border-left:none; font-size:10px; font-weight:bold; text-align:center; }
.nit-label { background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 8px; width:45px; text-align:center; border:1px solid #555; border-left:none; display:flex; align-items:center; justify-content:center; }
.nit-value { padding:5px 10px; border:1px solid #555; border-left:none; font-size:10px; font-weight:bold; text-align:center; width:130px; }

/* Tabla ítems */
.col-cod  { width:7%; }
.col-cant { width:5%; }
.col-desc { width:38%; }
.col-iva  { width:5%; }
.col-img  { width:9%; }
.col-unit { width:13%; }
.col-tot  { width:13%; }
.col-ret  { width:10%; }

.th-item {
    background:#3b5ea6;
    color:#fff;
    font-weight:bold;
    padding:5px 4px;
    border:1px solid #3b5ea6;
    text-align:center;
    font-size:9px;
}
.row-even { background:#c5c8e8; }
.row-odd  { background:#e8eaf4; }

/* Totales */
.tot-label { font-weight:bold; text-align:right; padding:4px 8px; border:1px solid #555; background:#e8e4b0; font-size:9.5px; }
.tot-value { text-align:right; padding:4px 8px; border:1px solid #555; font-size:10px; font-weight:bold; background:#fff; }
.grand-label { font-weight:bold; text-align:right; padding:5px 8px; border:1px solid #555; background:#3b5ea6; color:#fff; font-size:10px; }
.grand-value { text-align:right; padding:5px 8px; border:1px solid #555; font-size:11px; font-weight:bold; background:#c5ddf4; }

/* Footer */
.foot-wrap { border:1px solid #555; margin-top:6px; }
.foot-left  { padding:5px 8px; font-size:8px; color:#333; border-right:1px solid #555; vertical-align:top; }
.foot-mid   { padding:5px 8px; text-align:center; font-size:8.5px; vertical-align:middle; }
.foot-right { padding:5px 8px; text-align:center; font-size:9px; font-weight:bold; letter-spacing:1px; vertical-align:middle; }
</style>
</head>
<body>

<!-- ══════════════════════════════
     ENCABEZADO
══════════════════════════════ -->
<table style="border:1.5px solid #555; margin-bottom:8px;">
  <tr>
    <!-- Logo -->
    <td style="width:22%; padding:6px 8px; border-right:1px solid #555; text-align:center; vertical-align:middle;">
      <?php if ($imgLogoPdf): ?>
      <img src="<?= $imgLogoPdf ?>" style="max-height:55px; max-width:130px; object-fit:contain;">
      <?php endif; ?>
    </td>
    <!-- Título -->
    <td style="width:50%; padding:8px; border-right:1px solid #555; text-align:center; vertical-align:middle;">
      <div style="font-size:16px; font-weight:bold; letter-spacing:1.5px;">Orden de Compra</div>
    </td>
    <!-- Código / Fecha / Versión -->
    <td style="width:28%; padding:0; vertical-align:top; font-size:8px;">
      <table style="width:100%; border-collapse:collapse; height:100%;">
        <tr>
          <td style="padding:4px 8px; border-bottom:1px solid #bbb;">
            <span style="font-weight:bold;">Codigo:</span> SGC-AP-GFI-FT-005
          </td>
        </tr>
        <tr>
          <td style="padding:4px 8px; border-bottom:1px solid #bbb;">
            <span style="font-weight:bold;">Fecha:</span> <?= htmlspecialchars($fechaFmt) ?>
          </td>
        </tr>
        <tr>
          <td style="padding:4px 8px;">
            <span style="font-weight:bold;">Version:</span> 001
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══════════════════════════════
     EMPRESA + P.O.
══════════════════════════════ -->
<table style="margin-bottom:8px;">
  <tr>
    <!-- Datos empresa -->
    <td style="vertical-align:top; padding-right:16px;">
      <div style="font-size:10.5px; font-weight:bold;">Reg.Mercantil No.121529 // Nit.900.535.843-3</div>
      <div style="font-size:9px; font-style:italic; margin-top:3px;">Cra 10 No. 9-80 Barrio Cooperativa</div>
      <div style="font-size:9px; font-style:italic; margin-top:1px;">Telefax: (4) 322 27 79 &nbsp; Cél.317 3453644</div>
      <div style="font-size:9px; margin-top:1px;">Florencia-Caquetá- Colombia</div>
      <div style="font-size:8px; margin-top:2px;">
        https://impobiomedical.impomin.com/ &nbsp;&nbsp;
        contabilidad@impomin.com &nbsp;&nbsp;
        impobiomedical@impomin.com
      </div>
    </td>
    <!-- PO + Date box -->
    <td style="vertical-align:top; text-align:right; white-space:nowrap;">
      <table style="border-collapse:collapse; margin-left:auto;">
        <tr>
          <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 14px; border:1.5px solid #3b5ea6; text-align:center; width:55px;">P.O</td>
          <td style="font-size:13px; font-weight:bold; padding:4px 18px; border:1.5px solid #555; border-left:none; text-align:center; min-width:80px;"><?= $po ?></td>
        </tr>
        <tr>
          <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 14px; border:1.5px solid #3b5ea6; border-top:none; text-align:center;">DATE:</td>
          <td style="font-size:9px; padding:5px 18px; border:1.5px solid #555; border-top:none; border-left:none; text-align:center;"><?= htmlspecialchars($fechaFmt) ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══════════════════════════════
     TO / TIPO DE CONTRIBUYENTE
══════════════════════════════ -->
<table style="margin-bottom:7px;">
  <tr>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 10px; border:1px solid #555; width:80px; text-align:center; vertical-align:middle;">TO:</td>
    <td style="padding:5px 12px; border:1px solid #555; border-left:none; font-size:10.5px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($proveedor)) ?>
    </td>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 8px; border:1px solid #555; border-left:none; width:40px; text-align:center; vertical-align:middle;">NIT</td>
    <td style="padding:5px 10px; border:1px solid #555; border-left:none; font-size:10px; font-weight:bold; text-align:center; vertical-align:middle; width:130px;">
      <?= htmlspecialchars($proveedorNit) ?>
    </td>
  </tr>
  <tr>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:8.5px; padding:5px 6px; border:1px solid #555; border-top:none; text-align:center; vertical-align:middle; line-height:1.3;">TIPO DE<br>CONTRIBUYENTE</td>
    <td colspan="3" style="padding:5px 12px; border:1px solid #555; border-top:none; border-left:none; font-size:10.5px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($tipoContribuyente)) ?>
    </td>
  </tr>
</table>

<!-- ══════════════════════════════
     PURCHASE DEPARTMENT + HEADERS TABLA
══════════════════════════════ -->
<table>
  <!-- Fila Purchase Department -->
  <tr>
    <th colspan="4" style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:10px; padding:5px; border:1px solid #3b5ea6; text-align:center; letter-spacing:1px;">
      PURCHASE DEPARTMENT
    </th>
    <th style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:10px; padding:5px; border:1px solid #3b5ea6; text-align:center; letter-spacing:1px;">
      IVA
    </th>
    <th colspan="3" style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:10px; padding:5px; border:1px solid #3b5ea6; text-align:center; letter-spacing:1px;">
      PAYMENT TERMS
    </th>
  </tr>
  <tr>
    <td colspan="4" style="text-align:center; font-size:9px; padding:3px; border:1px solid #555; border-top:none; background:#e8eaf4;">
      <?= htmlspecialchars($deptCompras) ?>
    </td>
    <td style="text-align:center; font-size:9px; padding:3px; border:1px solid #555; border-top:none; border-left:none; background:#e8eaf4;">
      <?= htmlspecialchars($iva) ?>
    </td>
    <td colspan="3" style="text-align:center; font-size:9px; padding:3px; border:1px solid #555; border-top:none; border-left:none; background:#e8eaf4;">
      <?= htmlspecialchars($condicionesPago) ?>
    </td>
  </tr>

  <!-- Cabeceras columnas ítems -->
  <tr>
    <th class="th-item col-cod">COD</th>
    <th class="th-item col-cant">CANT</th>
    <th class="th-item col-desc" colspan="2">DESCRIPCION Y REQUISITOS</th>
    <th class="th-item col-iva">% IVA</th>
    <th class="th-item col-unit">UNIT</th>
    <th class="th-item col-tot">TOTAL</th>
    <th class="th-item col-ret">RET <?= number_format($retencionPct, 1) ?>%</th>
  </tr>

  <!-- Ítems -->
  <?php
  $rowIdx = 0;
  foreach ($items as $it):
      $pu     = (float)$it['precio_unit'];
      $qty    = (int)$it['cantidad'];
      $pct    = (float)($it['porcentaje_iva'] ?? 19);
      $aplica = strtolower($it['iva']) === 'si';
      $sub    = $pu * $qty;
      $ivaFila= $aplica ? $sub * ($pct / 100) : 0;
      $totFila= $sub + $ivaFila;
      $retFila= $sub * ($retencionPct / 100);
      $rowCls = ($rowIdx % 2 === 0) ? 'row-even' : 'row-odd';
      $rowIdx++;
      $imgProd= !empty($it['foto'] ?? '') ? getItemImg($it['foto'] ?? '') : '';
  ?>
  <tr class="<?= $rowCls ?>">
    <td class="b tc vm" style="padding:5px 3px; font-size:9px;"><?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?></td>
    <td class="b tc vm" style="padding:5px 3px; font-size:10px; font-weight:bold;"><?= $qty ?></td>
    <td class="b tl vt" style="padding:6px 8px;" colspan="<?= $imgProd ? '1' : '2' ?>">
      <strong style="font-size:9.5px;"><?= mb_strtoupper(htmlspecialchars($it['titulo'])) ?></strong>
      <?php if (!empty($it['descripcion'])): ?>
      <br><span style="font-size:8px; color:#333;"><?= nl2br(htmlspecialchars($it['descripcion'])) ?></span>
      <?php endif; ?>
    </td>
    <?php if ($imgProd): ?>
    <td class="b tc vm" style="padding:3px; width:70px;">
      <img src="<?= $imgProd ?>" style="max-height:65px; max-width:80px; object-fit:contain;">
    </td>
    <?php endif; ?>
    <td class="b tc vm nw" style="padding:4px 5px;"><?= $aplica ? $pct . '%' : '0%' ?></td>
    <td class="b tr vm nw" style="padding:4px 6px;">$ <?= number_format($pu, 0, ',', '.') ?></td>
    <td class="b tr vm nw" style="padding:4px 6px; font-weight:bold;">$ <?= number_format($totFila, 0, ',', '.') ?></td>
    <td class="b tr vm nw" style="padding:4px 6px; color:#555;">$ <?= number_format($retFila, 0, ',', '.') ?></td>
  </tr>
  <?php endforeach; ?>

  <!-- Fila Nota + Totales -->
  <tr>
    <td colspan="5" rowspan="4"
        style="border:1px solid #555; padding:8px; font-size:8px; vertical-align:top; background:#fafafa;">
      <?= nl2br(htmlspecialchars($nota)) ?>
    </td>
    <td class="tot-label">SUBTOTAL</td>
    <td colspan="2" class="tot-value">$ <?= number_format($subtotal, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-label">IVA</td>
    <td colspan="2" class="tot-value">$ <?= number_format($totalIva, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-label">RET <?= number_format($retencionPct, 1) ?>%</td>
    <td colspan="2" class="tot-value" style="color:#c0392b;">$ <?= number_format($retencion, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="grand-label">TOTAL</td>
    <td colspan="2" class="grand-value">$ <?= number_format($total, 0, ',', '.') ?></td>
  </tr>
</table>

<!-- ══════════════════════════════
     FOOTER
══════════════════════════════ -->
<table class="foot-wrap" style="margin-top:8px;">
  <tr>
    <td class="foot-left" style="width:32%; border-right:1px solid #555;">
      Documentos de: Sistema de Gestión de Calidad
    </td>
    <td class="foot-mid" style="width:36%; border-right:1px solid #555;">
      Fecha: <?= htmlspecialchars($fechaFmt) ?>
    </td>
    <td class="foot-right" style="width:32%;">
      DOCUMENTO CONTROLADO
    </td>
  </tr>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

while (ob_get_level()) {
    ob_end_clean();
}

$dompdf->stream("orden_compra_PO{$po}.pdf", ['Attachment' => $forzar]);
exit();
