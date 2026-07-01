<?php
/**
 * generar_pdf.php — Vista de generación/descarga de PDFs para Impobiomedical.
 *
 * Replica exactamente el formato de la cotización mostrada en la imagen:
 * - Encabezado: IMPOMIN SAS / IMPOBIOMEDICAL + Logo
 * - Datos empresa (izquierda) y datos cliente (derecha)
 * - Fila Asesor | Cargo | Pago | Validez
 * - Tabla: Cantidad | Descripción | Imagen | %IVA | Precio antes IVA | IVA | Total IVA | Tiempo Entrega
 * - Total, observaciones, notas de pago
 * - Footer con íconos de contacto
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

$numero        = $cotizacion['numero_cotizacion'];
$fecha_raw     = $cotizacion['fecha_creacion'];
$fecha_validez = $cotizacion['fecha_validez'];
$dias_validez  = $cotizacion['dias_validez'];
$asesorNombre  = $cotizacion['asesor_nombre'];
$asesorCargo   = $cotizacion['asesor_cargo'];
$clienteNombre = $cotizacion['cliente_nombre'];
$clienteNit    = $cotizacion['cliente_nit'];
$clienteDir    = $cotizacion['cliente_direccion'];
$clienteTel    = $cotizacion['cliente_telefono'];
$clienteEmail  = $cotizacion['cliente_correo'];
$clienteContacto = $cotizacion['cliente_contacto'];
$clienteCiudad = $cotizacion['cliente_ciudad'];
$condicionesPago = $cotizacion['condiciones_pago'];
$observaciones = $cotizacion['observaciones'];

// ── Fechas ────────────────────────────────────────────────────────────────────
date_default_timezone_set('America/Bogota');
$meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$fechaObj  = new DateTime($fecha_raw);
$fechaFmt  = $fechaObj->format('d/m/Y');
$validezFmt = $fecha_validez ? (new DateTime($fecha_validez))->format('d/m/Y') : '';

// ── DomPDF ────────────────────────────────────────────────────────────────────
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ── Helper: imagen a base64 ───────────────────────────────────────────────────
function imgBase64(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
    $data = @file_get_contents($ruta);
    if (!$data) return '';
    return 'data:image/' . $mime . ';base64,' . base64_encode($data);
}

$imgDir  = dirname(__DIR__, 3) . '/img/';
$logoDir = dirname(__DIR__, 3) . '/logo/';

$imgLogo    = imgBase64($logoDir . 'logo.png');
$imgLogoMin = imgBase64($logoDir . 'logo_small.png') ?: $imgLogo;

// ── Totales ───────────────────────────────────────────────────────────────────
$totalBase = 0;
$totalIva  = 0;
foreach ($items as $it) {
    $pu  = (float)$it['precio'];
    $qty = (int)$it['cantidad'];
    $pct = (float)($it['porcentaje_iva'] ?? 19);
    $totalBase += $pu * $qty;
    if ($it['iva'] === 'si') {
        $totalIva += $pu * $qty * ($pct / 100);
    }
}
$granTotal = $totalBase + $totalIva;

// ── Plantilla HTML ────────────────────────────────────────────────────────────
ob_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cotización <?= htmlspecialchars($numero) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #1a1a1a; }

/* ── ENCABEZADO ── */
.header-tabla { width:100%; border-collapse:collapse; margin-bottom:6px; }
.header-tabla td { vertical-align:middle; }
.cell-titulo { width:40%; }
.titulo-cot { font-size:22px; font-weight:bold; color:#1a7a7a; letter-spacing:1px; }
.cell-logo { width:60%; text-align:right; }
.logo-img { height:60px; }

/* ── INFO CABECERA ── */
.info-header { width:100%; border-collapse:collapse; margin-bottom:4px; }
.info-header td { padding:2px 4px; font-size:8.5px; vertical-align:top; }
.lbl { font-weight:bold; color:#555; }
.val-box {
    background:#2dd4d4;
    color:#fff;
    padding:2px 8px;
    border-radius:2px;
    font-weight:bold;
    font-size:9px;
    min-width:80px;
    display:inline-block;
}

/* ── SECCIÓN EMPRESAS ── */
.sect-empresas { width:100%; border-collapse:collapse; margin-bottom:4px; }
.sect-empresas td { border:1px solid #ccc; padding:4px 6px; vertical-align:top; width:50%; }
.empresa-titulo {
    background:#8a8a8a;
    color:#fff;
    font-weight:bold;
    font-size:8.5px;
    padding:3px 6px;
    margin-bottom:4px;
    text-transform:uppercase;
}
.empresa-fila { font-size:8px; margin-bottom:2px; }
.empresa-fila strong { color:#333; }

/* ── BARRA ASESOR ── */
.barra-asesor {
    width:100%;
    border-collapse:collapse;
    margin-bottom:4px;
    background:#d0d0d0;
}
.barra-asesor td {
    padding:3px 6px;
    font-size:8px;
    font-weight:bold;
    text-align:center;
    border-right:1px solid #bbb;
}
.barra-asesor td:last-child { border-right:none; }
.barra-asesor .lbl-bar { font-size:7px; font-weight:normal; color:#444; display:block; }

/* ── TABLA DE ÍTEMS ── */
.tabla-items { width:100%; border-collapse:collapse; margin-bottom:6px; }
.tabla-items thead tr { background:#8a8a8a; color:#fff; }
.tabla-items th {
    padding:4px 3px;
    font-size:8px;
    text-align:center;
    border:1px solid #999;
}
.tabla-items td {
    padding:3px;
    font-size:8px;
    border:1px solid #ccc;
    vertical-align:middle;
    text-align:center;
}
.td-desc { text-align:left !important; }
.td-img img { max-height:40px; max-width:60px; }
.fila-par { background:#f0f0f0; }
.fila-impar { background:#ffffff; }

/* ── TOTALES ── */
.tabla-totales { width:100%; border-collapse:collapse; margin-bottom:6px; }
.tabla-totales td { padding:3px 6px; font-size:8.5px; }
.observaciones-box { font-size:8px; color:#333; margin-bottom:6px; }
.observaciones-box strong { display:block; margin-bottom:2px; }

/* ── NOTAS PAGO ── */
.nota-entrega {
    width:100%;
    border-collapse:collapse;
    margin-bottom:4px;
}
.nota-entrega td {
    border:1px solid #ccc;
    padding:3px 6px;
    font-size:7.5px;
}
.nota-verde { background:#22c55e; color:#fff; font-weight:bold; text-align:center; }
.nota-amarilla { background:#fbbf24; color:#333; font-weight:bold; text-align:center; }
.total-final-row { background:#2dd4d4; }
.total-final-row td { color:#fff; font-weight:bold; font-size:10px; text-align:right; padding:4px 8px; }
.total-lbl { text-align:right; font-weight:bold; }
.total-val { background:#2dd4d4; color:#fff; font-weight:bold; padding:3px 8px; }

/* ── FOOTER ── */
.footer-bar {
    width:100%;
    background:#1a3a3a;
    color:#fff;
    border-collapse:collapse;
    margin-top:10px;
}
.footer-bar td {
    padding:6px 10px;
    font-size:8px;
    text-align:center;
    vertical-align:middle;
}
.footer-titulo { font-size:10px; font-weight:bold; color:#2dd4d4; text-align:center; display:block; }
</style>
</head>
<body>

<!-- ── TÍTULO + LOGO ── -->
<table class="header-tabla">
    <tr>
        <td class="cell-titulo">
            <div class="titulo-cot">COTIZACION</div>
        </td>
        <td class="cell-logo">
            <?php if ($imgLogo): ?>
            <img src="<?= $imgLogo ?>" class="logo-img" alt="Impobiomedical">
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- ── DATOS FECHA / VALIDEZ / N° COTIZACIÓN ── -->
<table class="info-header">
    <tr>
        <td class="lbl" width="120">Fecha de Cotización:</td>
        <td><span class="val-box"><?= $fechaFmt ?></span></td>
    </tr>
    <tr>
        <td class="lbl">Días Validez:</td>
        <td><span class="val-box"><?= $dias_validez ?></span></td>
    </tr>
    <tr>
        <td class="lbl">N. de Cotización:</td>
        <td><span class="val-box"><?= htmlspecialchars($numero) ?></span></td>
    </tr>
</table>

<!-- ── DATOS EMPRESA Y CLIENTE ── -->
<table class="sect-empresas">
    <tr>
        <td>
            <div class="empresa-titulo">IMPOMIN SAS - IMPOBIOMEDICAL</div>
            <div class="empresa-fila"><strong>Nit:</strong> 900.535.843-3</div>
            <div class="empresa-fila"><strong>Dirección:</strong> Cra 10 No. 9-80 Barrio Cooperativa Florencia-Caquetá</div>
            <div class="empresa-fila"><strong>Teléfono:</strong> 317 34 53 644 - 310 26 90 595</div>
            <div class="empresa-fila"><strong>Email:</strong> impobiomedical@impomin.com</div>
        </td>
        <td>
            <div class="empresa-titulo"><?= mb_strtoupper(htmlspecialchars($clienteNombre)) ?></div>
            <div class="empresa-fila"><strong>Nit:</strong> <?= htmlspecialchars($clienteNit) ?></div>
            <div class="empresa-fila"><strong>Dirección:</strong> <?= htmlspecialchars($clienteDir) ?></div>
            <div class="empresa-fila"><strong>Teléfono:</strong> <?= htmlspecialchars($clienteTel) ?></div>
            <div class="empresa-fila"><strong>Email:</strong> <?= htmlspecialchars($clienteEmail) ?></div>
            <div class="empresa-fila"><strong>Nombre:</strong> <?= htmlspecialchars($clienteContacto) ?></div>
        </td>
    </tr>
</table>

<!-- ── BARRA ASESOR ── -->
<table class="barra-asesor">
    <tr>
        <td width="30%">
            <span class="lbl-bar">ASESOR</span>
            <?= htmlspecialchars($asesorNombre) ?>
        </td>
        <td width="25%">
            <span class="lbl-bar">CARGO</span>
            <?= htmlspecialchars($asesorCargo) ?>
        </td>
        <td width="20%">
            <span class="lbl-bar">PAGO</span>
            <?= htmlspecialchars($condicionesPago) ?>
        </td>
        <td width="25%">
            <span class="lbl-bar">VALIDEZ</span>
            <?= $validezFmt ?>
        </td>
    </tr>
</table>

<!-- ── TABLA DE ÍTEMS ── -->
<table class="tabla-items">
    <thead>
        <tr>
            <th width="5%">Cantidad</th>
            <th width="30%">Descripción</th>
            <th width="12%">Imagen</th>
            <th width="8%">% IVA</th>
            <th width="14%">Precio Unitario antes de IVA</th>
            <th width="10%">IVA</th>
            <th width="12%">Total IVA incluido</th>
            <th width="9%">Tiempo de Entrega</th>
        </tr>
    </thead>
    <tbody>
    <?php $idx = 0; foreach ($items as $it):
        $pu  = (float)$it['precio'];
        $qty = (int)$it['cantidad'];
        $pct = (float)($it['porcentaje_iva'] ?? 19);
        $ivaU    = ($it['iva'] === 'si') ? $pu * ($pct / 100) : 0;
        $totalU  = ($pu + $ivaU) * $qty;
        $claseF  = ($idx++ % 2 === 0) ? 'fila-par' : 'fila-impar';
        $imgProd = !empty($it['foto'])
            ? imgBase64(dirname(__DIR__, 3) . '/uploads/' . $it['foto'])
            : '';
    ?>
        <tr class="<?= $claseF ?>">
            <td><?= $qty ?></td>
            <td class="td-desc">
                <strong><?= htmlspecialchars($it['titulo']) ?></strong><br>
                <span style="font-size:7.5px;color:#555;"><?= htmlspecialchars($it['descripcion']) ?></span>
            </td>
            <td class="td-img">
                <?php if ($imgProd): ?>
                <img src="<?= $imgProd ?>" alt="img">
                <?php else: ?>&mdash;<?php endif; ?>
            </td>
            <td><?= $it['iva'] === 'si' ? $pct . '%' : 'EXCL.' ?></td>
            <td>$ <?= number_format($pu, 0, ',', '.') ?></td>
            <td>$ <?= number_format($ivaU * $qty, 0, ',', '.') ?></td>
            <td>$ <?= number_format($totalU, 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($it['tiempo_entrega'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- ── OBSERVACIONES + TOTALES ── -->
<?php if (!empty($observaciones)): ?>
<div class="observaciones-box">
    <strong>Observaciones:</strong>
    <?= htmlspecialchars($observaciones) ?>
</div>
<?php endif; ?>

<table class="nota-entrega">
    <tr>
        <td width="70%">EL TIEMPO DE ENTREGA CUENTA A PARTIR DEL RECIBO<br>DE EXISTENCIA EN EL MOMENTO DE CONFIRMACIÓN DE ENVÍO</td>
        <td width="30%" class="total-lbl">Total</td>
    </tr>
    <tr>
        <td class="nota-verde">FAVOR CONSIGNAR A NOMBRE DE IMPOMIN S.A.S A LA CUENTA DE AHORROS<br>BANCOLOMBIA # 34 413745006</td>
        <td class="total-val" style="text-align:right; font-size:10px; font-weight:bold;">$ <?= number_format($granTotal, 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td class="nota-amarilla">TRANSPORTE CONTRAENTREGA A TODO EL PAÍS</td>
        <td></td>
    </tr>
</table>

<!-- ── FOOTER ── -->
<table class="footer-bar">
    <tr>
        <td colspan="4"><span class="footer-titulo">Contáctanos</span></td>
    </tr>
    <tr>
        <td>📱 WhatsApp<br>317 34 53 644</td>
        <td>✉️ E-mail<br>impobiomedical@impomin.com</td>
        <td>🌐 Facebook<br>Impobiomedical</td>
        <td>📍 Dirección<br>Florencia, Caquetá</td>
    </tr>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("cotizacion_{$numero}.pdf", ['Attachment' => $forzar_descarga]);
