<?php
/**
 * Vista: PDF de Orden de Compra — IMPOMIN S.A.S
 * Variables disponibles: $ctrl (OrdenCompraController)
 */

// Limpiar cualquier buffer previo (incluyendo el ob_start() de index.php)
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
$po              = (int)$orden['numero_po'];
$fecha           = $orden['fecha'] ?? date('Y-m-d');
$proveedor       = $orden['proveedor'] ?? '';
$proveedorNit    = $orden['proveedor_nit'] ?? '';
$tipoContrib     = $orden['tipo_contribuyente'] ?? '';
$condicionesPago = $orden['condiciones_pago'] ?? 'Según acuerdo';
$iva             = $orden['iva'] ?? '19%';
$deptCompras     = $orden['departamento_compras'] ?? '';
$nota            = $orden['nota'] ?? '';
$retencionPct    = (float)($orden['retencion'] ?? 2.5);

// ── Fecha en inglés ───────────────────────────────────────────────────────────
date_default_timezone_set('America/Bogota');
$meses    = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$fechaObj = new DateTime($fecha);
$fechaFmt = $meses[(int)$fechaObj->format('n') - 1] . ' ' . $fechaObj->format('j') . ', ' . $fechaObj->format('Y');

// ── DomPDF ────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

function imgB64PO(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : 'png';
    $raw  = @file_get_contents($ruta);
    return $raw ? 'data:image/' . $mime . ';base64,' . base64_encode($raw) : '';
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgB64PO($logoDir . 'logopdf.png');

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

// ── Paleta de colores (fiel a imagen de referencia) ───────────────────────────
// Azul encabezados:  #2f5496
// Azul filas pares:  #b8cce4
// Azul filas impares:#dce6f1
// Amarillo totales:  #ffffcc
// Azul total final:  #2f5496 fondo / blanco texto

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>P.O. <?= $po ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: Arial, sans-serif;
    font-size: 8.5px;
    color: #000;
    padding: 10px 14px;   /* margen compacto */
}
table { width:100%; border-collapse:collapse; }

/* Bordes */
.b  { border:1px solid #7f7f7f; }
.bb { border-bottom:1px solid #7f7f7f; }
.bl { border-left:1px solid #7f7f7f; }
.br { border-right:1px solid #7f7f7f; }
.bt { border-top:1px solid #7f7f7f; }

/* Alineación */
.tc { text-align:center; }
.tr { text-align:right;  }
.tl { text-align:left;   }
.vm { vertical-align:middle; }
.vt { vertical-align:top;    }
.nw { white-space:nowrap;    }
.bold { font-weight:bold;    }

/* ── Colores principales (paleta imagen referencia) ── */
.bg-azul    { background:#2f5496; color:#fff; }   /* encabezados */
.bg-fila-a  { background:#b8cce4; color:#000; }   /* filas pares */
.bg-fila-b  { background:#dce6f1; color:#000; }   /* filas impares */
.bg-amarillo{ background:#ffffcc; color:#000; }   /* totales sub/iva/ret */
.bg-total   { background:#2f5496; color:#fff; }   /* fila TOTAL */
.bg-total-v { background:#dce6f1; color:#000; }   /* valor TOTAL */

/* ── Cabeceras de tabla ── */
.th {
    background:#2f5496; color:#fff;
    font-weight:bold; font-size:8px;
    padding:4px 3px;
    border:1px solid #7f7f7f;
    text-align:center;
}

/* ── Totales ── */
.tot-lbl {
    font-weight:bold; text-align:right;
    padding:3px 7px; border:1px solid #7f7f7f;
    background:#ffffcc; font-size:8.5px;
}
.tot-val {
    text-align:right; padding:3px 7px;
    border:1px solid #7f7f7f;
    font-size:9px; font-weight:bold;
    background:#fff;
}
.grand-lbl {
    font-weight:bold; text-align:right;
    padding:4px 7px; border:1px solid #7f7f7f;
    background:#2f5496; color:#fff; font-size:9px;
}
.grand-val {
    text-align:right; padding:4px 7px;
    border:1px solid #7f7f7f;
    font-size:10px; font-weight:bold;
    background:#dce6f1;
}
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table style="border:1px solid #7f7f7f; margin-bottom:6px;">
  <tr>
    <!-- Logo -->
    <td style="width:22%; padding:5px 7px; border-right:1px solid #7f7f7f; text-align:center; vertical-align:middle;">
      <?php if ($imgLogoPdf): ?>
      <img src="<?= $imgLogoPdf ?>" style="max-height:48px; max-width:125px;">
      <?php else: ?>
      <strong style="font-size:9px; color:#2f5496;">IMPOBIOMEDICAL</strong>
      <?php endif; ?>
    </td>
    <!-- Título centrado -->
    <td style="width:48%; padding:6px; border-right:1px solid #7f7f7f; text-align:center; vertical-align:middle;">
      <div style="font-size:14px; font-weight:bold;">Orden de Compra</div>
    </td>
    <!-- Código/Fecha/Versión -->
    <td style="width:30%; vertical-align:top; padding:0;">
      <table style="width:100%; border-collapse:collapse; font-size:8px;">
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><strong>Codigo:</strong> SGC-AP-GFI-FT-005</td></tr>
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><strong>Fecha:</strong> <?= htmlspecialchars($fechaFmt) ?></td></tr>
        <tr><td style="padding:3px 7px;"><strong>Version:</strong> 001</td></tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ EMPRESA + P.O. BOX ══ -->
<table style="margin-bottom:6px;">
  <tr>
    <!-- Datos empresa izquierda -->
    <td style="vertical-align:top; padding-right:12px;">
      <div style="font-size:9.5px; font-weight:bold;">Reg.Mercantil No.121529 // Nit.900.535.843-3</div>
      <div style="font-size:8px; font-style:italic; margin-top:2px;">Cra 10 No. 9-80 Barrio Cooperativa</div>
      <div style="font-size:8px; font-style:italic; margin-top:1px;">Telefax: (4) 322 27 79 &nbsp; Cél.317 3453644</div>
      <div style="font-size:8px; margin-top:1px;">Florencia-Caquetá- Colombia</div>
      <div style="font-size:7.5px; margin-top:2px;">
        https://impobiomedical.impomin.com/&nbsp;&nbsp;
        contabilidad@impomin.com&nbsp;&nbsp;
        impobiomedical@impomin.com
      </div>
    </td>
    <!-- P.O. + DATE box derecha -->
    <td style="vertical-align:top; text-align:right; white-space:nowrap; width:160px;">
      <table style="border-collapse:collapse; margin-left:auto;">
        <tr>
          <td class="bg-azul" style="font-weight:bold; font-size:9px; padding:5px 12px; border:1px solid #2f5496; text-align:center; width:48px;">P.O</td>
          <td style="font-size:13px; font-weight:bold; padding:3px 14px; border:1px solid #7f7f7f; border-left:none; text-align:center; min-width:70px;"><?= $po ?></td>
        </tr>
        <tr>
          <td class="bg-azul" style="font-weight:bold; font-size:9px; padding:5px 12px; border:1px solid #2f5496; border-top:none; text-align:center;">DATE:</td>
          <td style="font-size:8px; padding:4px 14px; border:1px solid #7f7f7f; border-top:none; border-left:none; text-align:center;"><?= htmlspecialchars($fechaFmt) ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ TO / TIPO CONTRIBUYENTE ══ -->
<table style="margin-bottom:5px;">
  <tr>
    <td class="bg-azul" style="font-weight:bold; font-size:8.5px; padding:4px 8px; border:1px solid #7f7f7f; width:70px; text-align:center; vertical-align:middle;">TO:</td>
    <td style="padding:4px 10px; border:1px solid #7f7f7f; border-left:none; font-size:9.5px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($proveedor)) ?>
    </td>
    <td class="bg-azul" style="font-weight:bold; font-size:8.5px; padding:4px 5px; border:1px solid #7f7f7f; border-left:none; width:32px; text-align:center; vertical-align:middle;">NIT</td>
    <td style="padding:4px 9px; border:1px solid #7f7f7f; border-left:none; font-size:9px; font-weight:bold; text-align:center; vertical-align:middle; width:115px;">
      <?= htmlspecialchars($proveedorNit) ?>
    </td>
  </tr>
  <tr>
    <td class="bg-azul" style="font-weight:bold; font-size:8px; padding:4px 5px; border:1px solid #7f7f7f; border-top:none; text-align:center; vertical-align:middle; line-height:1.4;">TIPO DE<br>CONTRIBUYENTE</td>
    <td colspan="3" style="padding:4px 10px; border:1px solid #7f7f7f; border-top:none; border-left:none; font-size:9.5px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($tipoContrib)) ?>
    </td>
  </tr>
</table>

<!-- ══ TABLA DE ÍTEMS ══ -->
<table>
  <!-- PURCHASE DEPARTMENT / IVA / PAYMENT TERMS -->
  <tr>
    <td colspan="4" class="bg-azul" style="font-weight:bold; font-size:9px; padding:4px; border:1px solid #2f5496; text-align:center; letter-spacing:0.5px;">PURCHASE DEPARTMENT</td>
    <td class="bg-azul" style="font-weight:bold; font-size:9px; padding:4px; border:1px solid #2f5496; text-align:center;">IVA</td>
    <td colspan="3" class="bg-azul" style="font-weight:bold; font-size:9px; padding:4px; border:1px solid #2f5496; text-align:center; letter-spacing:0.5px;">PAYMENT TERMS</td>
  </tr>
  <tr>
    <td colspan="4" style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none; background:#dce6f1;"><?= htmlspecialchars($deptCompras) ?></td>
    <td style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none; border-left:none; background:#dce6f1;"><?= htmlspecialchars($iva) ?></td>
    <td colspan="3" style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none; border-left:none; background:#dce6f1;"><?= htmlspecialchars($condicionesPago) ?></td>
  </tr>

  <!-- Cabeceras columnas -->
  <tr>
    <th class="th" style="width:8%;">COD</th>
    <th class="th" style="width:6%;">CANT</th>
    <th class="th" style="width:40%;" colspan="2">DESCRIPCION Y REQUISITOS</th>
    <th class="th" style="width:5%;">% IVA</th>
    <th class="th" style="width:14%;">UNIT</th>
    <th class="th" style="width:14%;">TOTAL</th>
    <th class="th" style="width:13%;">RET <?= number_format($retencionPct, 1) ?>%</th>
  </tr>

  <!-- Filas de ítems — solo título + cod proveedor, sin descripción larga -->
  <?php $idx = 0; foreach ($items as $it):
      $pu      = (float)$it['precio_unit'];
      $qty     = (int)$it['cantidad'];
      $pct     = (float)($it['porcentaje_iva'] ?? 19);
      $aplica  = strtolower($it['iva']) === 'si';
      $sub     = $pu * $qty;
      $ivaFila = $aplica ? $sub * ($pct / 100) : 0;
      $totFila = $sub + $ivaFila;
      $retFila = $sub * ($retencionPct / 100);
      $cls     = ($idx % 2 === 0) ? 'bg-fila-a' : 'bg-fila-b';
      $idx++;

      // Imagen del producto
      $fotoVal = $it['foto'] ?? '';
      $imgItem = '';
      if (!empty($fotoVal)) {
          $imgItem = imgB64PO(dirname(__DIR__, 3) . '/uploads/' . $fotoVal);
      }
      $codProv = trim($it['codigo_proveedor'] ?? '');
  ?>
  <tr class="<?= $cls ?>">
    <!-- COD -->
    <td class="b tc vm" style="padding:6px 3px; font-size:8px; font-weight:bold;"><?= htmlspecialchars($codProv) ?></td>
    <!-- CANT -->
    <td class="b tc vm" style="padding:6px 2px; font-size:10px; font-weight:bold;"><?= $qty ?></td>
    <!-- DESCRIPCIÓN — solo título en mayúsculas -->
    <td class="b tl vm" style="padding:6px 8px;" <?= $imgItem ? '' : 'colspan="2"' ?>>
      <strong style="font-size:9px; letter-spacing:0.2px;"><?= mb_strtoupper(htmlspecialchars($it['titulo'])) ?></strong>
    </td>
    <!-- Imagen (si existe) -->
    <?php if ($imgItem): ?>
    <td class="b tc vm" style="padding:3px; width:60px;">
      <img src="<?= $imgItem ?>" style="max-height:55px; max-width:65px;">
    </td>
    <?php endif; ?>
    <!-- % IVA -->
    <td class="b tc vm nw" style="padding:4px 3px;"><?= $aplica ? number_format($pct, 0) . '%' : '0%' ?></td>
    <!-- UNIT -->
    <td class="b tr vm nw" style="padding:4px 5px;">&nbsp;$ <?= number_format($pu, 0, ',', '.') ?></td>
    <!-- TOTAL -->
    <td class="b tr vm nw" style="padding:4px 5px; font-weight:bold;">&nbsp;$ <?= number_format($totFila, 0, ',', '.') ?></td>
    <!-- RET -->
    <td class="b tr vm nw" style="padding:4px 5px;">&nbsp;$ <?= number_format($retFila, 0, ',', '.') ?></td>
  </tr>
  <?php endforeach; ?>

  <!-- Nota (izquierda) + Totales (derecha) -->
  <tr>
    <td colspan="5" rowspan="4"
        style="border:1px solid #7f7f7f; padding:7px 9px; font-size:8px; vertical-align:top; background:#fff; line-height:1.7;">
      <?= nl2br(htmlspecialchars($nota)) ?>
    </td>
    <td class="tot-lbl">SUBTOTAL</td>
    <td colspan="2" class="tot-val">&nbsp;$ <?= number_format($subtotal, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-lbl">IVA</td>
    <td colspan="2" class="tot-val">&nbsp;$ <?= number_format($totalIva, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="tot-lbl">RET <?= number_format($retencionPct, 1) ?>%</td>
    <td colspan="2" class="tot-val">&nbsp;$ <?= number_format($retencion, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td class="grand-lbl">TOTAL</td>
    <td colspan="2" class="grand-val">&nbsp;$ <?= number_format($total, 0, ',', '.') ?></td>
  </tr>
</table>

<!-- ══ FOOTER ══ -->
<table style="margin-top:6px; border:1px solid #7f7f7f;">
  <tr>
    <td style="width:30%; padding:4px 7px; border-right:1px solid #7f7f7f; font-size:7.5px; color:#333; vertical-align:middle;">
      Documentos de: Sistema de Gestión de Calidad
    </td>
    <td style="width:38%; padding:4px 7px; border-right:1px solid #7f7f7f; text-align:center; font-size:8px; vertical-align:middle;">
      Fecha: <?= htmlspecialchars($fechaFmt) ?>
    </td>
    <td style="width:32%; padding:4px 7px; text-align:center; font-size:8.5px; font-weight:bold; letter-spacing:0.8px; vertical-align:middle;">
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
$options->set('isPhpEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

while (ob_get_level()) {
    ob_end_clean();
}

$dompdf->stream("orden_compra_PO{$po}.pdf", ['Attachment' => (bool)$forzar]);
exit();
