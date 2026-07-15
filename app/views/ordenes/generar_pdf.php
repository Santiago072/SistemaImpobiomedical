<?php
/**
 * Vista: PDF de Orden de Compra — IMPOMIN S.A.S
 * Variables disponibles: $ctrl (OrdenCompraController)
 */

// ── Limpiar cualquier buffer previo (incluyendo el ob_start() de index.php) ──
while (ob_get_level()) {
    ob_end_clean();
}

try {
    $data = $ctrl->generarPdf();
} catch (Exception $e) {
    http_response_code(500);
    die('Error al generar la orden: ' . htmlspecialchars($e->getMessage()));
}

$orden  = $data['orden'];
$items  = $data['items'];
$forzar = $data['forzar'];

if (empty($items)) {
    die('La orden de compra no tiene ítems.');
}

// ── Datos ─────────────────────────────────────────────────────────────────────
$po                = (int)$orden['numero_po'];
$fecha             = $orden['fecha'] ?? date('Y-m-d');
$proveedor         = $orden['proveedor'] ?? '';
$proveedorNit      = $orden['proveedor_nit'] ?? '';
$tipoContrib       = $orden['tipo_contribuyente'] ?? '';
$condicionesPago   = $orden['condiciones_pago'] ?? 'Según acuerdo';
$iva               = $orden['iva'] ?? '19%';
$deptCompras       = $orden['departamento_compras'] ?? '';
$nota              = $orden['nota'] ?? '';
$retencionPct      = (float)($orden['retencion'] ?? 2.5);

// ── Fecha formateada en inglés (igual que la imagen) ─────────────────────────
date_default_timezone_set('America/Bogota');
$meses    = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$fechaObj = new DateTime($fecha);
$fechaFmt = $meses[(int)$fechaObj->format('n') - 1] . ' ' . $fechaObj->format('j') . ', ' . $fechaObj->format('Y');

// ── DomPDF ────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Helper: imagen a base64
function imgB64PO(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : 'png';
    $raw  = @file_get_contents($ruta);
    return $raw ? 'data:image/' . $mime . ';base64,' . base64_encode($raw) : '';
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgB64PO($logoDir . 'logopdf.png');
$imgLogoMin = imgB64PO($logoDir . 'logoimp.png');

// ── Cálculos ──────────────────────────────────────────────────────────────────
$subtotal = 0;
$totalIva = 0;
foreach ($items as $it) {
    $pu      = (float)$it['precio_unit'];
    $qty     = (int)$it['cantidad'];
    $pct     = (float)($it['porcentaje_iva'] ?? 19);
    $aplica  = strtolower($it['iva']) === 'si';
    $sub     = $pu * $qty;
    $subtotal += $sub;
    $totalIva += $aplica ? $sub * ($pct / 100) : 0;
}
$retencion = $subtotal * ($retencionPct / 100);
$total     = $subtotal + $totalIva - $retencion;

// ── HTML del PDF ──────────────────────────────────────────────────────────────
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>P.O. <?= $po ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 9px; color: #000; padding: 14px 18px; }
table { width:100%; border-collapse:collapse; }

/* colores principales */
.azul   { background:#3b5ea6; color:#fff; }
.lblue  { background:#c5c8e8; }
.lblue2 { background:#dde2f0; }
.amarillo { background:#e8e4b0; }

/* bordes */
.b  { border:1px solid #555; }
.bb { border-bottom:1px solid #555; }
.bl { border-left:1px solid #555; }
.br { border-right:1px solid #555; }
.bt { border-top:1px solid #555; }

/* alineación */
.tc { text-align:center; }
.tr { text-align:right;  }
.tl { text-align:left;   }
.vm { vertical-align:middle; }
.vt { vertical-align:top; }
.nw { white-space:nowrap; }
.bold { font-weight:bold; }

/* cabeceras de tabla de ítems */
.th {
    background:#3b5ea6; color:#fff;
    font-weight:bold; font-size:8.5px;
    padding:4px 3px; border:1px solid #3b5ea6;
    text-align:center;
}
.row-par  { background:#c5c8e8; }
.row-impar{ background:#e0e4f0; }

/* totales */
.tot-lbl  { font-weight:bold; text-align:right; padding:4px 7px; border:1px solid #555; background:#e8e4b0; font-size:9px; }
.tot-val  { text-align:right; padding:4px 7px; border:1px solid #555; font-size:9.5px; font-weight:bold; }
.grand-lbl{ font-weight:bold; text-align:right; padding:4px 7px; border:1px solid #555; background:#3b5ea6; color:#fff; font-size:9.5px; }
.grand-val{ text-align:right; padding:4px 7px; border:1px solid #555; font-size:10px; font-weight:bold; background:#c5ddf4; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table style="border:1.5px solid #555; margin-bottom:7px;">
  <tr>
    <td style="width:24%; padding:6px 8px; border-right:1px solid #555; text-align:center; vertical-align:middle;">
      <?php if ($imgLogoPdf): ?>
      <img src="<?= $imgLogoPdf ?>" style="max-height:52px; max-width:130px;">
      <?php else: ?>
      <strong style="font-size:10px; color:#3b5ea6;">IMPOBIOMEDICAL</strong>
      <?php endif; ?>
    </td>
    <td style="width:48%; padding:8px; border-right:1px solid #555; text-align:center; vertical-align:middle;">
      <div style="font-size:15px; font-weight:bold; letter-spacing:1px;">Orden de Compra</div>
    </td>
    <td style="width:28%; vertical-align:top; padding:0;">
      <table style="width:100%; border-collapse:collapse; font-size:8px;">
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><strong>Codigo:</strong> SGC-AP-GFI-FT-005</td></tr>
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><strong>Fecha:</strong> <?= htmlspecialchars($fechaFmt) ?></td></tr>
        <tr><td style="padding:3px 7px;"><strong>Version:</strong> 001</td></tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ EMPRESA + P.O. BOX ══ -->
<table style="margin-bottom:7px;">
  <tr>
    <td style="vertical-align:top;">
      <div style="font-size:10px; font-weight:bold;">Reg.Mercantil No.121529 // Nit.900.535.843-3</div>
      <div style="font-size:8.5px; font-style:italic; margin-top:2px;">Cra 10 No. 9-80 Barrio Cooperativa</div>
      <div style="font-size:8.5px; font-style:italic; margin-top:1px;">Telefax: (4) 322 27 79 &nbsp; Cél.317 3453644</div>
      <div style="font-size:8.5px; margin-top:1px;">Florencia-Caquetá- Colombia</div>
      <div style="font-size:7.5px; margin-top:2px;">
        https://impobiomedical.impomin.com/&nbsp;&nbsp;
        contabilidad@impomin.com&nbsp;&nbsp;
        impobiomedical@impomin.com
      </div>
    </td>
    <td style="vertical-align:top; text-align:right; white-space:nowrap; width:170px;">
      <table style="border-collapse:collapse; margin-left:auto;">
        <tr>
          <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 12px; border:1.5px solid #3b5ea6; text-align:center; width:50px;">P.O</td>
          <td style="font-size:13px; font-weight:bold; padding:4px 16px; border:1.5px solid #555; border-left:none; text-align:center; min-width:75px;"><?= $po ?></td>
        </tr>
        <tr>
          <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9px; padding:5px 12px; border:1.5px solid #3b5ea6; border-top:none; text-align:center;">DATE:</td>
          <td style="font-size:8.5px; padding:5px 16px; border:1.5px solid #555; border-top:none; border-left:none; text-align:center;"><?= htmlspecialchars($fechaFmt) ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ TO / TIPO CONTRIBUYENTE ══ -->
<table style="margin-bottom:6px;">
  <tr>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:8.5px; padding:5px 8px; border:1px solid #555; width:75px; text-align:center; vertical-align:middle;">TO:</td>
    <td style="padding:5px 10px; border:1px solid #555; border-left:none; font-size:10px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($proveedor)) ?>
    </td>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:8.5px; padding:5px 6px; border:1px solid #555; border-left:none; width:35px; text-align:center; vertical-align:middle;">NIT</td>
    <td style="padding:5px 10px; border:1px solid #555; border-left:none; font-size:9.5px; font-weight:bold; text-align:center; vertical-align:middle; width:120px;">
      <?= htmlspecialchars($proveedorNit) ?>
    </td>
  </tr>
  <tr>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:8px; padding:5px 5px; border:1px solid #555; border-top:none; text-align:center; vertical-align:middle; line-height:1.4;">TIPO DE<br>CONTRIBUYENTE</td>
    <td colspan="3" style="padding:5px 10px; border:1px solid #555; border-top:none; border-left:none; font-size:10px; font-weight:bold; text-align:center; vertical-align:middle; color:#3b5ea6;">
      <?= mb_strtoupper(htmlspecialchars($tipoContrib)) ?>
    </td>
  </tr>
</table>

<!-- ══ TABLA DE ÍTEMS ══ -->
<table>
  <!-- Fila PURCHASE DEPARTMENT / IVA / PAYMENT TERMS -->
  <tr>
    <td colspan="4" style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9.5px; padding:4px; border:1px solid #3b5ea6; text-align:center; letter-spacing:0.5px;">PURCHASE DEPARTMENT</td>
    <td style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9.5px; padding:4px; border:1px solid #3b5ea6; text-align:center;">IVA</td>
    <td colspan="3" style="background:#3b5ea6; color:#fff; font-weight:bold; font-size:9.5px; padding:4px; border:1px solid #3b5ea6; text-align:center; letter-spacing:0.5px;">PAYMENT TERMS</td>
  </tr>
  <tr>
    <td colspan="4" style="text-align:center; font-size:8.5px; padding:3px; border:1px solid #555; border-top:none; background:#dde2f0;"><?= htmlspecialchars($deptCompras) ?></td>
    <td style="text-align:center; font-size:8.5px; padding:3px; border:1px solid #555; border-top:none; border-left:none; background:#dde2f0;"><?= htmlspecialchars($iva) ?></td>
    <td colspan="3" style="text-align:center; font-size:8.5px; padding:3px; border:1px solid #555; border-top:none; border-left:none; background:#dde2f0;"><?= htmlspecialchars($condicionesPago) ?></td>
  </tr>

  <!-- Cabeceras columnas -->
  <tr>
    <th class="th" style="width:7%;">COD</th>
    <th class="th" style="width:6%;">CANT</th>
    <th class="th" style="width:37%;" colspan="2">DESCRIPCION Y REQUISITOS</th>
    <th class="th" style="width:5%;">% IVA</th>
    <th class="th" style="width:15%;">UNIT</th>
    <th class="th" style="width:15%;">TOTAL</th>
    <th class="th" style="width:10%;">RET <?= number_format($retencionPct, 1) ?>%</th>
  </tr>

  <!-- Filas de ítems -->
  <?php $idx = 0; foreach ($items as $it):
      $pu      = (float)$it['precio_unit'];
      $qty     = (int)$it['cantidad'];
      $pct     = (float)($it['porcentaje_iva'] ?? 19);
      $aplica  = strtolower($it['iva']) === 'si';
      $sub     = $pu * $qty;
      $ivaFila = $aplica ? $sub * ($pct / 100) : 0;
      $totFila = $sub + $ivaFila;
      $retFila = $sub * ($retencionPct / 100);
      $cls     = ($idx % 2 === 0) ? 'row-par' : 'row-impar';
      $idx++;

      // Imagen del producto desde uploads
      $fotoVal = $it['foto'] ?? '';
      $imgItem = '';
      if (!empty($fotoVal)) {
          $imgItem = imgB64PO(dirname(__DIR__, 3) . '/uploads/' . $fotoVal);
      }
  ?>
  <tr class="<?= $cls ?>">
    <td class="b tc vm" style="padding:5px 2px; font-size:8.5px;"><?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?></td>
    <td class="b tc vm" style="padding:5px 2px; font-size:10px; font-weight:bold;"><?= $qty ?></td>
    <td class="b tl vt" style="padding:6px 7px;" <?= $imgItem ? '' : 'colspan="2"' ?>>
      <strong style="font-size:9px;"><?= mb_strtoupper(htmlspecialchars($it['titulo'])) ?></strong>
      <?php if (!empty($it['descripcion'])): ?>
      <br><span style="font-size:7.5px; color:#333;"><?= nl2br(htmlspecialchars(mb_strimwidth($it['descripcion'], 0, 200, '…'))) ?></span>
      <?php endif; ?>
    </td>
    <?php if ($imgItem): ?>
    <td class="b tc vm" style="padding:3px; width:65px;">
      <img src="<?= $imgItem ?>" style="max-height:60px; max-width:75px;">
    </td>
    <?php endif; ?>
    <td class="b tc vm nw" style="padding:4px 4px;"><?= $aplica ? number_format($pct, 0) . '%' : '0%' ?></td>
    <td class="b tr vm nw" style="padding:4px 5px;">&nbsp;$&nbsp; <?= number_format($pu, 0, ',', '.') ?></td>
    <td class="b tr vm nw" style="padding:4px 5px; font-weight:bold;">&nbsp;$&nbsp; <?= number_format($totFila, 0, ',', '.') ?></td>
    <td class="b tr vm nw" style="padding:4px 5px; color:#444;">&nbsp;$&nbsp; <?= number_format($retFila, 0, ',', '.') ?></td>
  </tr>
  <?php endforeach; ?>

  <!-- Fila de nota (izquierda) + totales (derecha) -->
  <tr>
    <td colspan="5" rowspan="4"
        style="border:1px solid #555; padding:8px 10px; font-size:8px; vertical-align:top; background:#fefefe; line-height:1.6;">
      <?= nl2br(htmlspecialchars($nota)) ?>
    </td>
    <td class="tot-lbl">SUBTOTAL</td>
    <td colspan="2" class="tot-val">&nbsp;$&nbsp; <?= number_format($subtotal, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-lbl">IVA</td>
    <td colspan="2" class="tot-val">&nbsp;$&nbsp; <?= number_format($totalIva, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-lbl">RET <?= number_format($retencionPct, 1) ?>%</td>
    <td colspan="2" class="tot-val" style="color:#b91c1c;">&nbsp;$&nbsp; <?= number_format($retencion, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="grand-lbl">TOTAL</td>
    <td colspan="2" class="grand-val">&nbsp;$&nbsp; <?= number_format($total, 0, ',', '.') ?></td>
  </tr>
</table>

<!-- ══ FOOTER ══ -->
<table style="margin-top:7px; border:1px solid #555;">
  <tr>
    <td style="width:32%; padding:5px 8px; border-right:1px solid #555; font-size:7.5px; color:#333; vertical-align:middle;">
      Documentos de: Sistema de Gestión de Calidad
    </td>
    <td style="width:36%; padding:5px 8px; border-right:1px solid #555; text-align:center; font-size:8px; vertical-align:middle;">
      Fecha: <?= htmlspecialchars($fechaFmt) ?>
    </td>
    <td style="width:32%; padding:5px 8px; text-align:center; font-size:9px; font-weight:bold; letter-spacing:1px; vertical-align:middle;">
      DOCUMENTO CONTROLADO
    </td>
  </tr>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

// ── Generar PDF con DomPDF ────────────────────────────────────────────────────
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$options->set('isPhpEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Limpiar cualquier buffer residual antes de stream
while (ob_get_level()) {
    ob_end_clean();
}

$dompdf->stream("orden_compra_PO{$po}.pdf", ['Attachment' => (bool)$forzar]);
exit();
