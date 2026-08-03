<?php
/**
 * Vista: PDF Reporte de Órdenes de Compra — IMPOMIN S.A.S / Impobiomedical
 * Generado con DomPDF.
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
$imgLogoPdf = imgBase64($logoDir . 'logopdf.png');
$imgLogoImp = imgBase64($logoDir . 'logoimp.png');

$fechaSoloFecha = date('d/m/Y');
$totalRegistros = count($datosPdf ?? []);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Órdenes de Compra — Impobiomedical</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5px; color: #1f2937; padding: 15px 18px; }

/* ── Encabezado Corporativo Idéntico a los otros PDF ── */
.hdr-wrap {
    border: 1.5px solid #10757e;
    border-radius: 4px;
    margin-bottom: 12px;
    overflow: hidden;
    background: #ffffff;
}

/* ── Badges de Filtros ── */
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
table.ord-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    border: 2px solid #10757e;
}
table.ord-table th {
    background-color: #10757e;
    color: #ffffff;
    font-weight: bold;
    padding: 7px 6px;
    text-align: center;
    font-size: 8.5px;
    text-transform: uppercase;
    border: 1.5px solid #0d5c63;
}
table.ord-table td {
    border: 1.5px solid #cbd5e1;
    padding: 6px 6px;
    vertical-align: middle;
    font-size: 8px;
}

/* Filas alternas */
table.ord-table tbody tr:nth-child(even) { background-color: #f8fafc; }

.money { font-weight: bold; color: #059669; text-align: right; }
.footer-table { margin-top: 14px; border-top: 1px solid #cbd5e1; padding-top: 5px; width:100%; }
.footer-table td { font-size: 7.5px; color: #64748b; border:none; text-align: left; }
</style>
</head>
<body>

<!-- ENCABEZADO CORPORATIVO CON LOGOS IDÉNTICO AL PDF DE PRODUCTOS Y ESTADÍSTICAS -->
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

      <!-- COL 2: Título y Metadatos -->
      <td style="width:36%; text-align:center; vertical-align:middle; padding:6px 8px; border-right:1px solid #e2e8f0;">
        <div style="font-size:13px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">Reporte Órdenes de Compra</div>
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

<!-- FILTROS APLICADOS -->
<?php if (!empty($filtros)): ?>
<div class="meta-bar">
    <?php if (!empty($filtros['proveedor'])): ?>
        <span class="filter-badge">Proveedor: <?= htmlspecialchars($filtros['proveedor']) ?></span>
    <?php endif; ?>
    <?php if (!empty($filtros['cotizacion_numero'])): ?>
        <span class="filter-badge">Nº Cotización: <?= htmlspecialchars($filtros['cotizacion_numero']) ?></span>
    <?php endif; ?>
    <?php if (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin'])): ?>
        <span class="filter-badge">Fechas: 
        <?php 
            if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
                echo htmlspecialchars($filtros['fecha_inicio']) . ' a ' . htmlspecialchars($filtros['fecha_fin']);
            } elseif (!empty($filtros['fecha_inicio'])) {
                echo 'Desde ' . htmlspecialchars($filtros['fecha_inicio']);
            } elseif (!empty($filtros['fecha_fin'])) {
                echo 'Hasta ' . htmlspecialchars($filtros['fecha_fin']);
            }
        ?>
        </span>
    <?php endif; ?>
</div>
<?php endif; ?>

<table class="ord-table">
    <thead>
        <tr>
            <th style="width:20%;">Nombre del Proveedor</th>
            <th style="width:9%;">Número de Orden</th>
            <th style="width:12%;">Nombre de Banco</th>
            <th style="width:14%;">Número de Cuenta</th>
            <th style="width:10%;">Tipo de Cuenta</th>
            <th style="width:12%;">NIT / Identificación</th>
            <th style="width:11%;">Valor a Pagar ($)</th>
            <th style="width:12%;">Cliente a Entregar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($datosPdf as $row): ?>
        <tr>
            <td style="text-align:left; font-weight:bold;"><?= htmlspecialchars($row['proveedor'] ?? '') ?></td>
            <td style="text-align:center;"><?= (int)$row['numero_po'] ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($row['banco_nombre'] ?? '') ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($row['banco_cuenta'] ?? '') ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($row['banco_tipo_cuenta'] ?? '') ?></td>
            <td style="text-align:center;"><?= htmlspecialchars($row['nit'] ?? '') ?></td>
            <td class="money">$ <?= number_format((float)$row['valor_pagar'], 2, ',', '.') ?></td>
            <td style="text-align:left;"><?= htmlspecialchars($row['cliente'] ?? '') ?></td>
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

$nombreArchivo = 'Reporte_Ordenes_Compra_' . date('Ymd_His') . '.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit();

