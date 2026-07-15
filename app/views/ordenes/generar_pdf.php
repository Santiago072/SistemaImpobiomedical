<?php
/**
 * Vista: PDF de Orden de Compra — IMPOMIN S.A.S
 * Diseño fiel a la imagen de referencia.
 */

try {
    $data = $ctrl->generarPdf();
} catch (Exception $e) {
    http_response_code(500);
    die('Error: ' . htmlspecialchars($e->getMessage()));
}

$orden  = $data['orden'];
$items  = $data['items'];
$forzar = $data['forzar'];

if (empty($items)) die('La orden no tiene ítems.');

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

date_default_timezone_set('America/Bogota');
$meses    = ['January','February','March','April','May','June',
             'July','August','September','October','November','December'];
$fechaObj = new DateTime($fecha);
$fechaFmt = $meses[(int)$fechaObj->format('n') - 1] . ' ' . $fechaObj->format('j') . ', ' . $fechaObj->format('Y');

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if (!function_exists('imgB64OC2')) {
    function imgB64OC2(string $ruta): string {
        if (!file_exists($ruta)) return '';
        $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : 'png';
        $raw  = @file_get_contents($ruta);
        return $raw ? 'data:image/' . $mime . ';base64,' . base64_encode($raw) : '';
    }
}

$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgB64OC2($logoDir . 'logopdf.png');

// Cálculos
$subtotal = 0; $totalIva = 0;
foreach ($items as $it) {
    $pu     = (float)$it['precio_unit'];
    $qty    = (int)$it['cantidad'];
    $pct    = (float)($it['porcentaje_iva'] ?? 19);
    $aplica = strtolower($it['iva']) === 'si';
    $sub    = $pu * $qty;
    $subtotal += $sub;
    $totalIva += $aplica ? $sub * ($pct / 100) : 0;
}
$retencion = $subtotal * ($retencionPct / 100);
$total     = $subtotal + $totalIva - $retencion;

// Formato número colombiano: punto miles, sin decimales
if (!function_exists('fmt')) {
    function fmt(float $n): string {
        return '$&nbsp;' . number_format($n, 0, ',', '.');
    }
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>P.O. <?= $po ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial, sans-serif; font-size:8.5px; color:#000; padding:10px 14px; }
table { width:100%; border-collapse:collapse; }

/* Paleta fiel a imagen de referencia */
/* Azul oscuro encabezados */ .h-azul { background:#1f3864; color:#fff; font-weight:bold; }
/* Azul medio filas pares  */ .f-azul  { background:#b8cce4; }
/* Azul claro filas impares*/ .f-lblue { background:#dce6f1; }
/* Amarillo totales        */ .f-amari { background:#ffffcc; }
/* Sin fondo               */ .f-blanc { background:#ffffff; }

/* Bordes */
.b  { border:1px solid #7f7f7f; }
.tc { text-align:center; } .tr { text-align:right; } .tl { text-align:left; }
.vm { vertical-align:middle; } .vt { vertical-align:top; }
.nw { white-space:nowrap; }

/* Cabeceras de la tabla de ítems */
.th {
    background:#1f3864; color:#fff; font-weight:bold;
    font-size:8px; padding:4px 4px;
    border:1px solid #7f7f7f; text-align:center;
}

/* Totales: etiqueta */
.tot-lbl { font-weight:bold; text-align:right; padding:3px 8px; border:1px solid #7f7f7f; background:#ffffcc; font-size:8.5px; }
/* Totales: valor */
.tot-val { text-align:right; padding:3px 8px; border:1px solid #7f7f7f; background:#fff; font-size:9.5px; font-weight:bold; }
/* Total final: etiqueta */
.tot-grand-lbl { font-weight:bold; text-align:right; padding:4px 8px; border:1px solid #7f7f7f; background:#1f3864; color:#fff; font-size:9px; }
/* Total final: valor */
.tot-grand-val { text-align:right; padding:4px 8px; border:1px solid #7f7f7f; background:#dce6f1; font-size:10.5px; font-weight:bold; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table style="border:1px solid #7f7f7f; margin-bottom:7px;">
  <tr>
    <td style="width:22%; padding:5px 7px; border-right:1px solid #7f7f7f; text-align:center; vertical-align:middle;">
      <?php if ($imgLogoPdf): ?>
      <img src="<?= $imgLogoPdf ?>" style="max-height:46px; max-width:120px;">
      <?php endif; ?>
    </td>
    <td style="width:48%; padding:7px; border-right:1px solid #7f7f7f; text-align:center; vertical-align:middle;">
      <span style="font-size:14px; font-weight:bold;">Orden de Compra</span>
    </td>
    <td style="width:30%; padding:0; vertical-align:top;">
      <table style="width:100%; border-collapse:collapse; font-size:8px; height:100%;">
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><b>Codigo:</b>SGC-AP-GFI-FT-005</td></tr>
        <tr><td style="padding:3px 7px; border-bottom:1px solid #ccc;"><b>Fecha:</b> <?= htmlspecialchars($fechaFmt) ?></td></tr>
        <tr><td style="padding:3px 7px;"><b>Version:</b>001</td></tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ EMPRESA + PO BOX ══ -->
<table style="margin-bottom:7px;">
  <tr>
    <td style="vertical-align:top;">
      <div style="font-size:9.5px; font-weight:bold;">Reg.Mercantil No.121529 // Nit.900.535.843-3</div>
      <div style="font-size:8px; font-style:italic; margin-top:2px;">Cra 10 No. 9-80 Barrio Cooperativa</div>
      <div style="font-size:8px; font-style:italic; margin-top:1px;">Telefax: (4) 322 27 79 &nbsp;Cél.317 3453644</div>
      <div style="font-size:8px; margin-top:1px;">&nbsp;&nbsp;&nbsp;&nbsp;Florencia-Caquetá- Colombia</div>
      <div style="font-size:7.5px; margin-top:2px;">https://impobiomedical.impomin.com/ &nbsp;contabilidad@impomin.com &nbsp;impobiomedical@impomin.com</div>
    </td>
    <td style="vertical-align:top; text-align:right; white-space:nowrap; width:160px;">
      <table style="border-collapse:collapse; margin-left:auto;">
        <tr>
          <td class="h-azul" style="padding:5px 12px; border:1px solid #1f3864; width:46px; text-align:center; font-size:9px;">P.O</td>
          <td style="font-size:14px; font-weight:bold; padding:3px 14px; border:1px solid #7f7f7f; border-left:none; text-align:center; min-width:72px;"><?= $po ?></td>
        </tr>
        <tr>
          <td class="h-azul" style="padding:5px 12px; border:1px solid #1f3864; border-top:none; text-align:center; font-size:9px;">DATE:</td>
          <td style="font-size:8px; padding:4px 14px; border:1px solid #7f7f7f; border-top:none; border-left:none; text-align:center;"><?= htmlspecialchars($fechaFmt) ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- ══ TO / TIPO CONTRIBUYENTE ══ -->
<table style="margin-bottom:6px;">
  <tr>
    <td class="h-azul" style="padding:4px 8px; border:1px solid #7f7f7f; width:68px; text-align:center; vertical-align:middle; font-size:9px;">TO:</td>
    <td style="padding:4px 10px; border:1px solid #7f7f7f; border-left:none; font-size:10px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($proveedor)) ?>
    </td>
    <td class="h-azul" style="padding:4px 5px; border:1px solid #7f7f7f; border-left:none; width:30px; text-align:center; vertical-align:middle; font-size:9px;">NIT</td>
    <td style="padding:4px 9px; border:1px solid #7f7f7f; border-left:none; font-size:9px; font-weight:bold; text-align:center; vertical-align:middle; width:115px;">
      <?= htmlspecialchars($proveedorNit) ?>
    </td>
  </tr>
  <tr>
    <td class="h-azul" style="padding:4px 5px; border:1px solid #7f7f7f; border-top:none; text-align:center; vertical-align:middle; line-height:1.4; font-size:8px;">TIPO DE<br>CONTRIBUYENTE</td>
    <td colspan="3" style="padding:4px 10px; border:1px solid #7f7f7f; border-top:none; border-left:none; font-size:9.5px; font-weight:bold; text-align:center; vertical-align:middle;">
      <?= mb_strtoupper(htmlspecialchars($tipoContrib)) ?>
    </td>
  </tr>
</table>

<!-- ══ TABLA ITEMS ══ -->
<!-- Fila PURCHASE DEPARTMENT | IVA | PAYMENT TERMS -->
<table>
<tr>
  <td colspan="4" class="h-azul" style="padding:4px; border:1px solid #1f3864; text-align:center; font-size:9px; letter-spacing:0.5px;">PURCHASE DEPARTMENT</td>
  <td class="h-azul" style="padding:4px; border:1px solid #1f3864; text-align:center; font-size:9px;">IVA</td>
  <td colspan="2" class="h-azul" style="padding:4px; border:1px solid #1f3864; text-align:center; font-size:9px; letter-spacing:0.5px;">PAYMENT TERMS</td>
</tr>
<tr>
  <td colspan="4" class="f-lblue" style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none;"><?= htmlspecialchars($deptCompras) ?></td>
  <td class="f-lblue" style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none; border-left:none;"><?= htmlspecialchars($iva) ?></td>
  <td colspan="2" class="f-lblue" style="text-align:center; font-size:8px; padding:2px 4px; border:1px solid #7f7f7f; border-top:none; border-left:none;"><?= htmlspecialchars($condicionesPago) ?></td>
</tr>

<!-- Cabeceras columnas — igual que imagen: COD | CANT | DESCRIPCION Y REQUISITOS | %IVA | (imagen) | UNIT | TOTAL -->
<tr>
  <th class="th" style="width:9%;">COD</th>
  <th class="th" style="width:6%;">CANT</th>
  <th class="th" style="width:42%;" colspan="2">DESCRIPCION Y REQUISITOS</th>
  <th class="th" style="width:5%;">% IVA</th>
  <th class="th" style="width:16%;">UNIT</th>
  <th class="th" style="width:16%;">TOTAL</th>
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
    $cls     = ($idx % 2 === 0) ? 'f-azul' : 'f-lblue';
    $idx++;
    $codProv  = trim($it['codigo_proveedor'] ?? '');
    $fotoVal  = $it['foto'] ?? '';
    $imgItem  = !empty($fotoVal) ? imgB64OC2(dirname(__DIR__, 3) . '/uploads/' . $fotoVal) : '';
?>
<tr class="<?= $cls ?>">
  <td class="b tc vm" style="padding:7px 3px; font-size:8px; font-weight:bold;"><?= htmlspecialchars($codProv) ?></td>
  <td class="b tc vm" style="padding:7px 2px; font-size:10.5px; font-weight:bold;"><?= $qty ?></td>
  <!-- Título del producto — en mayúsculas, centrado, sin descripción -->
  <td class="b tc vm" style="padding:7px 8px; font-size:9px; font-weight:bold;" <?= $imgItem ? '' : 'colspan="2"' ?>>
    <?= mb_strtoupper(htmlspecialchars($it['titulo'])) ?>
  </td>
  <?php if ($imgItem): ?>
  <td class="b tc vm" style="padding:3px; width:58px;">
    <img src="<?= $imgItem ?>" style="max-height:52px; max-width:62px;">
  </td>
  <?php endif; ?>
  <td class="b tc vm nw" style="padding:6px 3px;"><?= $aplica ? number_format($pct, 0) . '%' : '0%' ?></td>
  <td class="b tr vm nw" style="padding:6px 6px;"><?= fmt($pu) ?></td>
  <td class="b tr vm nw" style="padding:6px 6px; font-weight:bold;"><?= fmt($totFila) ?></td>
</tr>
<?php endforeach; ?>

<!-- Fila nota + totales -->
<!-- nota ocupa cols 1-4, rowspan 4; totales en las 3 cols de la derecha -->
<tr>
  <td colspan="4" rowspan="4"
      style="border:1px solid #7f7f7f; padding:8px 10px; font-size:8px; vertical-align:top; background:#fff; line-height:1.75;">
    <?= nl2br(htmlspecialchars($nota)) ?>
  </td>
  <td class="tot-lbl">SUBTOTAL</td>
  <td colspan="2" class="tot-val"><?= fmt($subtotal) ?></td>
</tr>
<tr>
  <td class="tot-lbl">IVA</td>
  <td colspan="2" class="tot-val"><?= fmt($totalIva) ?></td>
</tr>
<tr>
  <td class="tot-lbl">RET <?= number_format($retencionPct, 1) ?>%</td>
  <td colspan="2" class="tot-val"><?= fmt($retencion) ?></td>
</tr>
<tr>
  <td class="tot-grand-lbl">TOTAL</td>
  <td colspan="2" class="tot-grand-val"><?= fmt($total) ?></td>
</tr>
</table>

<!-- ══ FOOTER ══ -->
<table style="margin-top:6px; border:1px solid #7f7f7f;">
  <tr>
    <td style="width:30%; padding:4px 7px; border-right:1px solid #7f7f7f; font-size:7.5px; vertical-align:middle;">
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

while (ob_get_level()) ob_end_clean();

$dompdf->stream("orden_compra_PO{$po}.pdf", ['Attachment' => (bool)$forzar]);
exit();
