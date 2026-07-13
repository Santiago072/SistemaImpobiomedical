<?php
/**
 * generar_pdf.php — Vista de generación/descarga de PDFs para Impobiomedical.
 */

try {
    $data = $ctrl->generarPdf();
} catch (Exception $e) {
    die('Error al generar la cotización: ' . htmlspecialchars($e->getMessage()));
}

$cotizacion      = $data['cotizacion'];
$items           = $data['items'];
$forzar_descarga = $data['forzar'];

if (empty($items)) {
    die('La cotización no tiene ítems.');
}

$numero          = $cotizacion['numero_cotizacion'];
$fecha_raw       = $cotizacion['fecha_creacion'];
$dias_validez    = $cotizacion['dias_validez'];
$asesorNombre    = $cotizacion['asesor_nombre'];
$asesorCargo     = $cotizacion['asesor_cargo'];
$clienteNombre   = $cotizacion['cliente_nombre'];
$clienteTel      = $cotizacion['cliente_telefono'];
$clienteEmail    = $cotizacion['cliente_correo'];
$clienteContacto = $cotizacion['cliente_contacto'];
$condicionesPago = $cotizacion['condiciones_pago'];

// ── Fechas ────────────────────────────────────────────────────────────────────
date_default_timezone_set('America/Bogota');
$meses    = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$fechaObj = new DateTime($fecha_raw);
$mesTexto = mb_strtoupper($meses[(int)$fechaObj->format('n') - 1]);
$fechaFmt = $fechaObj->format('d') . ' ' . $mesTexto . ' DE ' . $fechaObj->format('Y');

// ── DomPDF ────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ── Helper: imagen a base64 ───────────────────────────────────────────────────
function imgBase64(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
    $d    = @file_get_contents($ruta);
    if (!$d) return '';
    return 'data:image/' . $mime . ';base64,' . base64_encode($d);
}

$logoDir      = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf   = imgBase64($logoDir . 'logopdf.png');      // IMPOBIOMEDICAL (logo grande derecha)
$imgLogoMin   = imgBase64($logoDir . 'logoimp.png');      // IMPOMIN (logo pequeño medio)

// ── Cálculos ──────────────────────────────────────────────────────────────────
$valorBase  = 0;
$valorIva   = 0;
foreach ($items as $it) {
    $pu   = (float)$it['precio'];
    $qty  = (int)$it['cantidad'];
    $pct  = (float)($it['porcentaje_iva'] ?? 19);
    $aplica = strtolower($it['iva']) === 'si';
    $subtotal = $pu * $qty;           // V/U × CANT = valor base fila
    $ivaFila  = $aplica ? $subtotal * ($pct / 100) : 0;
    $valorBase += $subtotal;
    $valorIva  += $ivaFila;
}
$total = $valorBase + $valorIva;

ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cotización <?= htmlspecialchars($numero) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: Arial, sans-serif;
    font-size: 9px;
    color: #000;
    padding: 20px 25px;
}
table { width:100%; border-collapse:collapse; }
.b { border:1px solid #555; }
.bt { border-top:1px solid #555; }
.bb { border-bottom:1px solid #555; }
.bl { border-left:1px solid #555; }
.br { border-right:1px solid #555; }
.tc { text-align:center; }
.tr { text-align:right; }
.tl { text-align:left; }
.vm { vertical-align:middle; }
.vt { vertical-align:top; }
.nowrap { white-space: nowrap; }

/* ── HEADER ── */
.hdr-wrap {
    border: 2px solid #1a8a8a;
    border-radius: 4px;
    margin-bottom: 8px;
    overflow: hidden;
}
.hdr-top {
    background: #1a8a8a;
    padding: 5px 10px;
}
.hdr-top-inner {
    display: flex;
    width: 100%;
}
.hdr-body {
    padding: 6px 10px;
    background: #fff;
}

/* Cabecera izquierda empresa */
.empresa-name { font-size:13px; font-weight:bold; color:#1a3a5c; }
.empresa-nit  { font-size:10px; font-weight:bold; color:#1a3a5c; }
.empresa-rep  { font-size:9px; color:#333; margin-top:4px; }
.empresa-addr { font-size:8.5px; color:#555; margin-top:3px; }

/* Número de cotización centrado */
.cot-num-box {
    text-align:center;
    padding: 6px 0;
}
.cot-num-label { font-size:10px; color:#555; letter-spacing:1px; }
.cot-num-value { font-size:19px; font-weight:bold; color:#1a8a8a; letter-spacing:1px; }

/* Columnas tabla items */
.col-cant  { width:4%;  }
.col-desc  { width:30%; }
.col-img   { width:10%; }
.col-piva  { width:5%;  }
.col-vu    { width:10%; }
.col-iva   { width:10%; }
.col-tiva  { width:10%; }
.col-vt    { width:10%; }
.col-te    { width:11%; }

/* Header de items */
.th-item {
    background-color:#1a8a8a;
    color:#fff;
    font-weight:bold;
    padding:5px 4px;
    border:1px solid #1a8a8a;
    text-align:center;
    font-size:9.5px;
}
/* filas alternas */
.row-even { background:#f4fafa; }
.row-odd  { background:#ffffff; }

/* Totals */
.totals-wrap { margin-top:0; }
.total-row td {
    border:1px solid #555;
    padding:4px 8px;
    font-size:10px;
}
.total-label { font-weight:bold; text-align:right; background:#e8f4f4; }
.total-value { font-weight:bold; text-align:right; background:#fff; }
.grand-label { font-weight:bold; text-align:right; background:#1a8a8a; color:#fff; }
.grand-value { font-weight:bold; text-align:right; background:#00c0f0; }

/* ── ASESOR BAR ── */
.asesor-th { background:#1a8a8a; color:#fff; border:1px solid #1a8a8a; padding:4px; text-align:center; font-size:9px; }
.asesor-td { border:1px solid #aaa; padding:4px; text-align:center; font-size:9.5px; }

/* Footer */
.foot-yellow { background:#e8f4f4; color:#000; text-align:center; font-weight:bold; padding:5px; border:1px solid #555; font-size:10px; }
.foot-green  { background:#e8f4f4; color:#000; text-align:center; font-weight:bold; padding:5px; border:1px solid #555; border-top:none; font-size:10px; }
</style>
</head>
<body>

<!-- ══════════════════════════════════════════════
     MASTER TABLE PARA ENCABEZADO REPETITIVO (FLAT)
════════════════════════════════════════════════ -->
<table style="width:100%; border-collapse:collapse; border:none; font-size:9.5px;">
<thead style="display:table-header-group;">
<tr><td colspan="9" style="padding:0; border:none; background:#fff; text-align:left;">

<!-- HEADER PRINCIPAL -->
<div class="hdr-wrap">
  <!-- Barra superior de color -->
  <div style="background:#1a8a8a; height:6px;"></div>

  <!-- Cuerpo del header: 3 columnas -->
  <table style="width:100%; border-collapse:collapse;">
    <tr>
      <!-- COL 1: Datos empresa -->
      <td style="width:36%; padding:8px 12px; vertical-align:top; border-right:1px solid #d0e8e8;">
        <div class="empresa-name">IMPOMIN S.A.S</div>
        <div class="empresa-nit">Nit. 900.535.843-3</div>
        <div class="empresa-addr" style="margin-top:5px;">
          Cra 10 No. 9-80 Barrio Cooperativa Florencia-Caquetá<br>
          Calle 33A No 71 A 27 - Laureles - Medellín - Colombia<br>
          Telefax: (4)322 27 79 &nbsp;Cel. 317 34 53 644 / 310 26 90 595<br>
          https://impobiomedical.impomin.com/ <br>
          Correo electrónico: impobiomedical@impomin.com
        </div>
      </td>

      <!-- COL 2: Número cotización centrado -->
      <td style="width:28%; text-align:center; vertical-align:middle; padding:8px; border-right:1px solid #d0e8e8;">
        <?php if ($imgLogoMin): ?>
        <img src="<?= $imgLogoMin ?>" style="height:38px; object-fit:contain; margin-bottom:6px;"><br>
        <?php endif; ?>
        <div class="cot-num-label">COTIZACIÓN N°</div>
        <div class="cot-num-value"><?= htmlspecialchars($numero) ?></div>
      </td>

      <!-- COL 3: Logo IMPOBIOMEDICAL -->
      <td style="width:36%; text-align:center; vertical-align:middle; padding:8px;">
        <?php if ($imgLogoPdf): ?>
        <img src="<?= $imgLogoPdf ?>" style="max-width:220px; max-height:100px; object-fit:contain;">
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- Barra inferior de color -->
  <div style="background:#1a8a8a; height:4px;"></div>
</div>
</td></tr>
</thead>

<tbody>
<tr><td colspan="9" style="padding:0; border:none; background:#fff; text-align:left;">

<!-- ══════════════════════════════════════════════
     DATOS CLIENTE + FECHA
════════════════════════════════════════════════ -->
<table style="margin-bottom:7px;">
  <tr>
    <td style="width:62%; padding:3px 0; vertical-align:top;">
      <table style="font-size:10px; width:100%;">
        <tr>
          <td style="font-weight:bold; width:75px; color:#1a3a5c;">CLIENTE:</td>
          <td><?= mb_strtoupper(htmlspecialchars($clienteNombre)) ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; color:#1a3a5c;">CELULAR:</td>
          <td><?= htmlspecialchars($clienteTel) ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; color:#1a3a5c;">CONTACTO:</td>
          <td><?= mb_strtoupper(htmlspecialchars($clienteContacto)) ?></td>
        </tr>
        <tr>
          <td style="font-weight:bold; color:#1a3a5c;">EMAIL:</td>
          <td><?= htmlspecialchars($clienteEmail) ?></td>
        </tr>
      </table>
    </td>
    <td style="width:38%; text-align:right; vertical-align:bottom; font-size:10px;">
      <strong style="color:#1a3a5c;">FECHA:&nbsp;&nbsp;&nbsp;FLORENCIA, <?= $fechaFmt ?></strong>
    </td>
  </tr>
</table>

<!-- ══════════════════════════════════════════════
     ASESOR / CARGO / PAGO / VALIDEZ
════════════════════════════════════════════════ -->
<table style="margin-bottom:7px; font-size:9.5px;">
  <tr>
    <th class="asesor-th" style="width:35%;">ASESOR</th>
    <th class="asesor-th" style="width:25%;">CARGO</th>
    <th class="asesor-th" style="width:25%;">PAGO</th>
    <th class="asesor-th" style="width:15%;">VALIDEZ</th>
  </tr>
  <tr>
    <td class="asesor-td"><?= mb_strtoupper(htmlspecialchars($asesorNombre)) ?></td>
    <td class="asesor-td"><?= mb_strtoupper(htmlspecialchars($asesorCargo)) ?></td>
    <td class="asesor-td"><?= mb_strtoupper(htmlspecialchars($condicionesPago)) ?></td>
    <td class="asesor-td"><?= mb_strtoupper($dias_validez . ' DÍAS') ?></td>
  </tr>
</table>

</td></tr>
</tbody>
<!-- Títulos de Columnas -->
<tr>
  <th class="th-item col-cant">CANT</th>
  <th class="th-item col-desc">DESCRIPCIÓN</th>
  <th class="th-item col-img">IMAGEN</th>
  <th class="th-item col-piva">% IVA</th>
  <th class="th-item col-vu">V/U</th>
  <th class="th-item col-iva">IVA</th>
  <th class="th-item col-tiva">T/IVA</th>
  <th class="th-item col-vt">V/T</th>
  <th class="th-item col-te">TIEMPO ENTREGA</th>
</tr>
<tbody>
<?php
$rowIdx = 0;
foreach ($items as $it):
    $pu      = (float)$it['precio'];
    $qty     = (int)$it['cantidad'];
    $pct     = (float)($it['porcentaje_iva'] ?? 19);
    $aplica  = strtolower($it['iva']) === 'si';

    // V/U = precio unitario
    $vu      = $pu;
    // IVA fila = V/U * CANT * pct%  (pero en la tabla mostramos por fila el iva del subtotal)
    $subtotal = $vu * $qty;
    $ivaFila  = $aplica ? $subtotal * ($pct / 100) : 0;
    
    // V/T = V/U × CANT (sin IVA, como la guía imagen 3)
    $vt      = $subtotal;
    // T/IVA = V/T + IVA fila (Total fila con IVA incluido)
    $tIva    = $vt + $ivaFila;

    $rowCls  = ($rowIdx % 2 === 0) ? 'row-even' : 'row-odd';
    $rowIdx++;

    $imgProd = !empty($it['foto']) ? imgBase64(dirname(__DIR__, 3) . '/uploads/' . $it['foto']) : '';
?>
  <tr class="<?= $rowCls ?>">
    <td class="b tc vm" style="padding:6px 2px;"><?= $qty ?></td>
    <td class="b tl vt" style="padding:7px 8px;">
      <strong style="font-size:10px;"><?= mb_strtoupper(htmlspecialchars($it['titulo'])) ?></strong><br><br>
      <span style="font-size:9px;"><?= nl2br(htmlspecialchars($it['descripcion'])) ?></span>
    </td>
    <td class="b tc vm" style="padding:4px;">
      <?php if ($imgProd): ?>
        <img src="<?= $imgProd ?>" style="max-height:90px; max-width:110px;">
      <?php endif; ?>
    </td>
    <td class="b tc vm nowrap" style="padding:4px 6px;"><?= $aplica ? $pct . '%' : '0%' ?></td>
    <td class="b tr vm nowrap" style="padding:4px 6px;">$ <?= number_format($vu, 0, ',', '.') ?></td>
    <td class="b tr vm nowrap" style="padding:4px 6px;">$ <?= number_format($ivaFila, 0, ',', '.') ?></td>
    <td class="b tr vm nowrap" style="padding:4px 6px;">$ <?= number_format($tIva, 0, ',', '.') ?></td>
    <td class="b tr vm nowrap" style="padding:4px 6px; font-weight:bold;">$ <?= number_format($vt, 0, ',', '.') ?></td>
    <td class="b tc vm" style="padding:5px 6px; font-size:9px;"><?= nl2br(htmlspecialchars($it['tiempo_entrega'] ?? '')) ?></td>
  </tr>
<?php endforeach; ?>

  <!-- Fila nota de entrega + totales en columnas derechas -->
  <tr>
    <td colspan="5" rowspan="3" style="background:#e8f4f4; border:1px solid #555; padding:6px 8px; font-size:8.5px; font-weight:bold; vertical-align:top;">
      *EL TIEMPO DE ENTREGA CUENTA A PARTIR DEL RECIBO DE LA ORDEN DE COMPRA. SUJETO A VERIFICACIÓN DE DISPONIBILIDAD DE EXISTENCIA EN EL MOMENTO DE CONFIRMACIÓN DE ENVÍO.*
    </td>
    <td colspan="2" style="padding:4px 6px; border:1px solid #555; background:#e8f4f4; font-size:9.5px; font-weight:bold; text-align:right;">VALOR BASE</td>
    <td colspan="2" style="padding:4px 6px; border:1px solid #555; text-align:right; font-size:10px; font-weight:bold; background:#fff;">$ <?= number_format($valorBase, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td colspan="2" style="padding:4px 6px; border:1px solid #555; background:#e8f4f4; font-size:9.5px; font-weight:bold; text-align:right;">VALOR IVA</td>
    <td colspan="2" style="padding:4px 6px; border:1px solid #555; text-align:right; font-size:10px; font-weight:bold; background:#fff;">$ <?= number_format($valorIva, 0, ',', '.') ?></td>
  </tr>
  <tr>
    <td colspan="2" style="padding:5px 6px; border:1px solid #555; font-size:10px; font-weight:bold; text-align:right; background:#e8f4f4; color:#000;">TOTAL</td>
    <td colspan="2" style="padding:5px 6px; border:1px solid #555; text-align:right; font-size:11px; font-weight:bold; background:#fff; color:#000;">$ <?= number_format($total, 0, ',', '.') ?></td>
  </tr>
</tbody>
</table>

<!-- ══════════════════════════════════════════════
     PIE
════════════════════════════════════════════════ -->
<div class="foot-yellow" style="margin-top:8px;">TRANSPORTE CONTRAENTREGA A TODO EL PAÍS</div>
<div class="foot-green">
  FAVOR CONSIGNAR A NOMBRE DE IMPOMIN S.A.S A LA CUENTA DE AHORROS<br>
  BANCOLOMBIA # 34 413745006
</div>

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

// Limpiar cualquier búfer de salida residual que pueda corromper el PDF
while (ob_get_level()) {
    ob_end_clean();
}

$dompdf->stream("cotizacion_{$numero}.pdf", ['Attachment' => $forzar_descarga]);
exit();
