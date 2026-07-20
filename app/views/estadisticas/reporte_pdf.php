<?php
/**
 * Vista: PDF de Reporte de Estadísticas — IMPOMIN S.A.S
 * Generado con DomPDF. Incluye KPIs, Top Clientes, Top Productos, Top Vendedores y Evolución mensual.
 */

// Limpiar cualquier buffer previo
while (ob_get_level() > 0) { ob_end_clean(); }

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// ── Helpers ──────────────────────────────────────────────────────────────────
function fmtR(float $n): string {
    return '$ ' . number_format($n, 0, ',', '.');
}

function barPct(int $val, int $max): int {
    return $max > 0 ? (int)min(100, ($val / $max) * 100) : 0;
}

// Periodo del reporte
$periodoLabel = '';
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $periodoLabel = 'Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin));
} else {
    $periodoLabel = 'Período: Todos los registros';
}

$fechaGenerado = date('d/m/Y H:i');

// Máximos para barras proporcionales
$maxClientes  = !empty($topClientes['data'])   ? max($topClientes['data'])   : 1;
$maxProductos = !empty($topProductos['data'])  ? max($topProductos['data'])  : 1;
$maxVendedores= !empty($topVendedores['data']) ? max($topVendedores['data']) : 1;

// Meses formateados
$mesesFmt = array_map(function($m) {
    [$y, $mo] = explode('-', $m);
    $nombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return $nombres[(int)$mo - 1] . ' ' . $y;
}, $evolucion['meses']);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Estadísticas — Impobiomedical</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 9px; color: #1f2937; padding: 18px 20px; }
h1  { font-size: 16px; font-weight: bold; color: #1f3864; }
h2  { font-size: 11px; font-weight: bold; color: #1f3864; margin-bottom: 8px; border-bottom: 2px solid #10757e; padding-bottom: 3px; }
table { width:100%; border-collapse:collapse; }

/* Encabezado */
.hdr-table td { vertical-align: middle; }
.hdr-logo { width: 160px; }
.hdr-title { text-align: center; }
.hdr-meta  { text-align: right; font-size: 8px; color: #6b7280; }
.hdr-bar   { height: 4px; background: linear-gradient(90deg, #10757e, #3b82f6); margin: 10px 0 16px; }

/* KPI Cards — tabla 3 columnas */
.kpi-table { margin-bottom: 20px; }
.kpi-cell  { width: 33.33%; padding: 0 6px 0 0; vertical-align: top; }
.kpi-box   { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 12px; background: #f9fafb; }
.kpi-box.green  { border-left: 4px solid #10b981; }
.kpi-box.blue   { border-left: 4px solid #3b82f6; }
.kpi-box.purple { border-left: 4px solid #8b5cf6; }
.kpi-box.amber  { border-left: 4px solid #f59e0b; }
.kpi-box.teal   { border-left: 4px solid #10757e; }
.kpi-num  { font-size: 16px; font-weight: bold; color: #111827; line-height: 1.1; }
.kpi-lbl  { font-size: 7.5px; color: #6b7280; font-weight: bold; text-transform: uppercase; margin-top: 2px; }

/* Tabla de tops */
.top-table th { background: #1f3864; color: #fff; padding: 5px 8px; font-size: 8px; text-align: left; }
.top-table td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 8.5px; vertical-align: middle; }
.top-table tr:nth-child(even) td { background: #f3f4f6; }
.bar-outer { background: #e5e7eb; border-radius: 3px; height: 8px; width: 100%; }
.bar-inner { background: #10757e; border-radius: 3px; height: 8px; }
.bar-inner.blue   { background: #3b82f6; }
.bar-inner.amber  { background: #f59e0b; }

/* Evolución mensual */
.evo-table th { background: #1f3864; color: #fff; padding: 5px 8px; font-size: 8px; text-align: center; }
.evo-table td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 8.5px; text-align: center; vertical-align: middle; }
.evo-table tr:nth-child(even) td { background: #f3f4f6; }
.evo-table .mes-col { text-align: left; font-weight: bold; }

/* Layout 2 columnas */
.two-col { width: 100%; }
.two-col .col-l { width: 50%; padding-right: 10px; vertical-align: top; }
.two-col .col-r { width: 50%; padding-left: 10px; vertical-align: top; }

/* Footer */
.footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #e5e7eb; font-size: 7.5px; color: #9ca3af; display: flex; justify-content: space-between; }
.badge-periodo { display: inline-block; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 3px 10px; border-radius: 20px; font-size: 8px; font-weight: bold; margin-bottom: 14px; }
.section-spacer { height: 16px; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══ -->
<table class="hdr-table" style="margin-bottom:6px;">
  <tr>
    <td class="hdr-title">
      <h1>Reporte de Estadísticas</h1>
      <div style="font-size:9px; color:#6b7280; margin-top:2px;">IMPOMIN S.A.S — Sistema Impobiomedical</div>
    </td>
    <td class="hdr-meta" style="width:180px;">
      <div>Generado: <?= $fechaGenerado ?></div>
      <div style="margin-top:3px; color:#10757e; font-weight:bold;"><?= $periodoLabel ?></div>
    </td>
  </tr>
</table>
<div class="hdr-bar"></div>

<!-- ══ KPIs ══ -->
<div class="badge-periodo"><?= $periodoLabel ?></div>
<table class="kpi-table">
  <tr>
    <td class="kpi-cell">
      <div class="kpi-box green">
        <div class="kpi-num"><?= fmtR($kpis['monto_cotizado_mes']) ?></div>
        <div class="kpi-lbl">Monto Cotizado</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box teal">
        <div class="kpi-num"><?= fmtR($kpis['monto_vendido'] ?? 0) ?></div>
        <div class="kpi-lbl">Monto Vendido (Reales)</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box blue">
        <div class="kpi-num"><?= number_format($kpis['total_cotizaciones']) ?></div>
        <div class="kpi-lbl">Cotizaciones Finalizadas</div>
      </div>
    </td>
  </tr>
  <tr><td colspan="3" style="height:8px;"></td></tr>
  <tr>
    <td class="kpi-cell">
      <div class="kpi-box purple">
        <div class="kpi-num"><?= number_format($kpis['total_ordenes']) ?></div>
        <div class="kpi-lbl">Órdenes de Compra</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box amber">
        <div class="kpi-num"><?= number_format($kpis['total_clientes']) ?></div>
        <div class="kpi-lbl">Clientes Registrados</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box green">
        <div class="kpi-num"><?= number_format($kpis['total_productos']) ?></div>
        <div class="kpi-lbl">Productos Activos</div>
      </div>
    </td>
  </tr>
</table>

<div class="section-spacer"></div>

<!-- ══ TOPS: Clientes + Productos ══ -->
<table class="two-col">
  <tr>
    <!-- Top Clientes -->
    <td class="col-l">
      <h2>Top Clientes Recurrentes</h2>
      <table class="top-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th style="width:35%;">Frecuencia</th>
            <th style="width:40px; text-align:right;">Cot.</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($topClientes['labels'])): ?>
          <tr><td colspan="4" style="text-align:center; color:#9ca3af;">Sin datos</td></tr>
          <?php else: ?>
          <?php foreach ($topClientes['labels'] as $i => $label): ?>
          <tr>
            <td style="font-weight:bold; color:#10757e;"><?= $i+1 ?></td>
            <td><?= htmlspecialchars($label) ?></td>
            <td>
              <div class="bar-outer">
                <div class="bar-inner" style="width:<?= barPct($topClientes['data'][$i], $maxClientes) ?>%;"></div>
              </div>
            </td>
            <td style="text-align:right; font-weight:bold;"><?= $topClientes['data'][$i] ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </td>

    <!-- Top Productos -->
    <td class="col-r">
      <h2>Top Productos Cotizados</h2>
      <table class="top-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Producto</th>
            <th style="width:35%;">Frecuencia</th>
            <th style="width:40px; text-align:right;">Veces</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($topProductos['labels'])): ?>
          <tr><td colspan="4" style="text-align:center; color:#9ca3af;">Sin datos</td></tr>
          <?php else: ?>
          <?php foreach ($topProductos['labels'] as $i => $label): ?>
          <tr>
            <td style="font-weight:bold; color:#3b82f6;"><?= $i+1 ?></td>
            <td><?= htmlspecialchars($label) ?></td>
            <td>
              <div class="bar-outer">
                <div class="bar-inner blue" style="width:<?= barPct($topProductos['data'][$i], $maxProductos) ?>%;"></div>
              </div>
            </td>
            <td style="text-align:right; font-weight:bold;"><?= $topProductos['data'][$i] ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </td>
  </tr>
</table>

<div class="section-spacer"></div>

<!-- ══ Top Vendedores ══ -->
<h2>Top Vendedores (por Órdenes Generadas)</h2>
<table class="top-table">
  <thead>
    <tr>
      <th style="width:30px;">#</th>
      <th>Vendedor</th>
      <th>Órdenes generadas</th>
      <th style="width:60px; text-align:right;">Total</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($topVendedores['labels'])): ?>
    <tr><td colspan="4" style="text-align:center; color:#9ca3af;">Sin datos</td></tr>
    <?php else: ?>
    <?php foreach ($topVendedores['labels'] as $i => $label): ?>
    <tr>
      <td style="font-weight:bold; color:#f59e0b;"><?= $i+1 ?></td>
      <td><?= htmlspecialchars($label) ?></td>
      <td>
        <div class="bar-outer">
          <div class="bar-inner amber" style="width:<?= barPct($topVendedores['data'][$i], $maxVendedores) ?>%;"></div>
        </div>
      </td>
      <td style="text-align:right; font-weight:bold;"><?= $topVendedores['data'][$i] ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<div class="section-spacer"></div>

<!-- ══ Evolución Mensual ══ -->
<h2>Evolución Mensual — Cotizaciones vs Órdenes</h2>
<table class="evo-table">
  <thead>
    <tr>
      <th class="mes-col" style="text-align:left;">Mes</th>
      <th>Cotizaciones Finalizadas</th>
      <th>Órdenes de Compra</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($evolucion['meses'])): ?>
    <tr><td colspan="3" style="text-align:center; color:#9ca3af;">Sin datos en este período</td></tr>
    <?php else: ?>
    <?php foreach ($evolucion['meses'] as $i => $mes): ?>
    <tr>
      <td class="mes-col"><?= htmlspecialchars($mesesFmt[$i] ?? $mes) ?></td>
      <td>
        <div style="display:inline-block; background:#3b82f6; color:#fff; border-radius:4px; padding:2px 8px; font-weight:bold;">
          <?= (int)$evolucion['cotizaciones'][$i] ?>
        </div>
      </td>
      <td>
        <div style="display:inline-block; background:#10b981; color:#fff; border-radius:4px; padding:2px 8px; font-weight:bold;">
          <?= (int)$evolucion['ordenes'][$i] ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<!-- ══ FOOTER ══ -->
<table style="margin-top:18px; border-top:1px solid #e5e7eb; padding-top:6px;">
  <tr>
    <td style="font-size:7.5px; color:#9ca3af;">Documento generado automáticamente por Sistema Impobiomedical</td>
    <td style="text-align:right; font-size:7.5px; color:#9ca3af;">Fecha de generación: <?= $fechaGenerado ?></td>
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

$nombreArchivo = 'reporte_estadisticas_' . date('Ymd_Hi') . '.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit();
