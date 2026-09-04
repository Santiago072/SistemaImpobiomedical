<?php
/**
 * Vista: PDF de Reporte de Estadísticas — IMPOMIN S.A.S / Impobiomedical
 * Generado con DomPDF. Incluye logos corporativos, KPIs, Top Clientes, Top Productos, Top Vendedores y Evolución mensual.
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

// Convertir imagen local a Base64 para DomPDF
function imgBase64(string $ruta): string {
    if (!file_exists($ruta)) return '';
    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'jpeg' : ($ext === 'png' ? 'png' : $ext);
    $d    = @file_get_contents($ruta);
    if (!$d) return '';
    return 'data:image/' . $mime . ';base64,' . base64_encode($d);
}

// Cargar logos de la carpeta logo/
$logoDir    = dirname(__DIR__, 3) . '/logo/';
$imgLogoPdf = imgBase64($logoDir . 'logopdf.png');
$imgLogoImp = imgBase64($logoDir . 'logoimp.png');

// Periodo del reporte
$periodoLabel = '';
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $periodoLabel = 'Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin));
} else {
    $periodoLabel = 'Período: Todos los registros';
}

$fechaGenerado = date('d/m/Y H:i');

// Máximos para barras proporcionales y total para porcentajes
$maxClientes   = !empty($topClientes['data'])   ? max($topClientes['data'])   : 1;
$maxProductos  = !empty($topProductos['data'])  ? max($topProductos['data'])  : 1;
$maxVendedores = !empty($topVendedores['data']) ? max($topVendedores['data']) : 1;
$totalVendidoPeriodo = !empty($kpis['monto_vendido']) && $kpis['monto_vendido'] > 0 
    ? (float)$kpis['monto_vendido'] 
    : (!empty($topClientes['data']) ? (float)array_sum($topClientes['data']) : 0);

// Meses formateados
$mesesFmt = array_map(function($m) {
    if (!$m || strpos($m, '-') === false) return $m;
    [$y, $mo] = explode('-', $m);
    $nombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    return $nombres[(int)$mo - 1] . ' ' . $y;
}, $evolucion['meses'] ?? []);

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Estadísticas — Impobiomedical</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 8.5px; color: #1f2937; padding: 15px 18px; }

h2 { 
    font-size: 10.5px; 
    font-weight: bold; 
    color: #1f3864; 
    margin-bottom: 6px; 
    border-bottom: 2px solid #10757e; 
    padding-bottom: 3px; 
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

table { width:100%; border-collapse:collapse; }

/* ── Encabezado Corporativo ── */
.hdr-wrap {
    border: 1.5px solid #10757e;
    border-radius: 4px;
    margin-bottom: 14px;
    overflow: hidden;
    background: #ffffff;
}

/* ── KPI Cards ── */
.kpi-table { margin-bottom: 14px; }
.kpi-cell  { width: 33.33%; padding: 0 4px; vertical-align: top; }
.kpi-box   { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; background: #f8fafc; }
.kpi-box.green  { border-left: 4px solid #10b981; background: #f0fdf4; }
.kpi-box.teal   { border-left: 4px solid #10757e; background: #f0fdfa; }
.kpi-box.blue   { border-left: 4px solid #3b82f6; background: #eff6ff; }
.kpi-box.purple { border-left: 4px solid #8b5cf6; background: #f5f3ff; }
.kpi-box.amber  { border-left: 4px solid #f59e0b; background: #fffbeb; }
.kpi-box.cyan   { border-left: 4px solid #06b6d4; background: #ecfeff; }

.kpi-num { font-size: 14px; font-weight: bold; color: #0f172a; line-height: 1.1; }
.kpi-lbl { font-size: 7.5px; color: #475569; font-weight: bold; text-transform: uppercase; margin-top: 2px; letter-spacing: 0.2px; }

/* ── Tablas de Tops ── */
.top-table th { background: #1f3864; color: #ffffff; padding: 5px 8px; font-size: 8px; text-align: left; font-weight: bold; }
.top-table td { padding: 4.5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8px; vertical-align: middle; }
.top-table tr:nth-child(even) td { background: #f8fafc; }
.bar-outer { background: #e2e8f0; border-radius: 3px; height: 7px; width: 100%; overflow: hidden; }
.bar-inner { background: #10757e; border-radius: 3px; height: 7px; }
.bar-inner.blue   { background: #3b82f6; }
.bar-inner.amber  { background: #f59e0b; }

/* ── Evolución mensual ── */
.evo-table th { background: #1f3864; color: #ffffff; padding: 5px 8px; font-size: 8px; text-align: center; font-weight: bold; }
.evo-table td { padding: 4.5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8px; text-align: center; vertical-align: middle; }
.evo-table tr:nth-child(even) td { background: #f8fafc; }
.evo-table td.mes-col { text-align: left; font-weight: bold; color: #1e293b; }
.evo-table th.mes-col { text-align: left; color: #ffffff !important; }
.evo-badge-total { display:inline-block; background:#3b82f6; color:#ffffff; border-radius:4px; padding:2px 8px; font-weight:bold; }
.evo-badge-conc  { display:inline-block; background:#10b981; color:#ffffff; border-radius:4px; padding:2px 8px; font-weight:bold; }
.evo-badge-pct   { display:inline-block; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:7.5px; }
.evo-badge-pct.green { background:#dcfce7; color:#15803d; }
.evo-badge-pct.gray  { background:#f1f5f9; color:#64748b; }
.evo-table tfoot td { background:#f1f5f9; font-weight:bold; color:#0f172a; border-top:1.5px solid #cbd5e1; }

/* ── Layout 2 columnas ── */
.two-col { width: 100%; margin-bottom: 10px; }
.two-col .col-l { width: 50%; padding-right: 6px; vertical-align: top; }
.two-col .col-r { width: 50%; padding-left: 6px; vertical-align: top; }

.section-spacer { height: 12px; }

/* ── Ventas Mensuales por Cliente ── */
.mes-badge { display: inline-block; background: #1f3864; color: #ffffff; padding: 2px 7px; border-radius: 4px; font-weight: bold; font-size: 8px; }
.tbl-mes-cliente th { background: #1f3864; color: #ffffff; padding: 5px 8px; font-size: 8px; text-align: left; font-weight: bold; }
.tbl-mes-cliente td { padding: 4.5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8px; vertical-align: middle; }
.tbl-mes-cliente tr:nth-child(even) td { background: #f8fafc; }
.tbl-mes-header-td { background: #f1f5f9 !important; font-weight: bold; color: #1e293b; border-top: 1.5px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; padding: 6px 8px !important; }

/* ── Footer ── */
.footer-table { margin-top: 14px; border-top: 1px solid #cbd5e1; padding-top: 5px; }
.footer-table td { font-size: 7.5px; color: #64748b; }
</style>
</head>
<body>

<!-- ══ ENCABEZADO CORPORATIVO CON LOGOS ══ -->
<div class="hdr-wrap">
  <!-- Barra superior teal -->
  <div style="background:#10757e; height:5px;"></div>

  <table style="width:100%; border-collapse:collapse; background:#ffffff;">
    <tr>
      <!-- COL 1: Logo IMPOMIN + Datos de contacto -->
      <td style="width:34%; padding:7px 10px; vertical-align:top; border-right:1px solid #e2e8f0;">
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

      <!-- COL 2: Título del reporte y Filtro de fechas -->
      <td style="width:36%; text-align:center; vertical-align:middle; padding:6px 8px; border-right:1px solid #e2e8f0;">
        <div style="font-size:13px; font-weight:bold; color:#1f3864; text-transform:uppercase; letter-spacing:0.3px;">Reporte de Estadísticas</div>
        <div style="font-size:9px; font-weight:bold; color:#10757e; margin-top:1px;">Sistema Impobiomedical</div>
        <div style="margin-top:6px; display:inline-block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; padding:2px 8px; border-radius:10px; font-size:7.5px; font-weight:bold;">
          <?= htmlspecialchars($periodoLabel) ?>
        </div>
      </td>

      <!-- COL 3: Logo IMPOBIOMEDICAL grande -->
      <td style="width:30%; text-align:center; vertical-align:middle; padding:6px 8px;">
        <?php if ($imgLogoPdf): ?>
          <img src="<?= $imgLogoPdf ?>" style="max-width:170px; max-height:65px; object-fit:contain;">
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- Barra inferior teal -->
  <div style="background:#10757e; height:3px;"></div>
</div>

<!-- ══ METRICAS PRINCIPALES (KPIs) ══ -->
<table class="kpi-table">
  <tr>
    <td class="kpi-cell">
      <div class="kpi-box green">
        <div class="kpi-num"><?= fmtR($kpis['monto_cotizado_mes'] ?? 0) ?></div>
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
        <div class="kpi-num"><?= number_format($kpis['total_cotizaciones'] ?? 0) ?></div>
        <div class="kpi-lbl">Cotizaciones Finalizadas</div>
      </div>
    </td>
  </tr>
  <tr><td colspan="3" style="height:6px;"></td></tr>
  <tr>
    <td class="kpi-cell">
      <div class="kpi-box purple">
        <div class="kpi-num"><?= number_format($kpis['total_ordenes'] ?? 0) ?></div>
        <div class="kpi-lbl">Órdenes de Compra</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box amber">
        <div class="kpi-num"><?= number_format($kpis['total_clientes'] ?? 0) ?></div>
        <div class="kpi-lbl">Clientes Registrados</div>
      </div>
    </td>
    <td class="kpi-cell">
      <div class="kpi-box cyan">
        <div class="kpi-num"><?= number_format($kpis['total_productos'] ?? 0) ?></div>
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
      <h2>Top Clientes (Monto Vendido $)</h2>
      <table class="top-table">
        <thead>
          <tr>
            <th style="width:18px;">#</th>
            <th>Cliente</th>
            <th style="width:26%;">Proporción</th>
            <th style="width:32px; text-align:center;">%</th>
            <th style="width:62px; text-align:right;">Monto</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($topClientes['labels'])): ?>
          <tr><td colspan="5" style="text-align:center; color:#9ca3af; padding:10px;">Sin datos registrados</td></tr>
          <?php else: ?>
          <?php foreach ($topClientes['labels'] as $i => $label): 
              $montoCliente = (float)($topClientes['data'][$i] ?? 0);
              $pctCliente   = $totalVendidoPeriodo > 0 ? round(($montoCliente / $totalVendidoPeriodo) * 100, 1) : 0;
          ?>
          <tr>
            <td style="font-weight:bold; color:#10757e;"><?= $i+1 ?></td>
            <td><?= htmlspecialchars($label) ?></td>
            <td>
              <div class="bar-outer">
                <div class="bar-inner" style="width:<?= barPct((int)$montoCliente, (int)$maxClientes) ?>%;"></div>
              </div>
            </td>
            <td style="text-align:center; font-weight:bold; color:#10757e; font-size:7.5px;"><?= number_format($pctCliente, 1, ',', '.') ?>%</td>
            <td style="text-align:right; font-weight:bold; color:#0f172a; font-size:7.5px;"><?= fmtR($montoCliente) ?></td>
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
            <th style="width:22px;">#</th>
            <th>Producto</th>
            <th style="width:32%;">Frecuencia</th>
            <th style="width:35px; text-align:right;">Veces</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($topProductos['labels'])): ?>
          <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:10px;">Sin datos registrados</td></tr>
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
            <td style="text-align:right; font-weight:bold; color:#0f172a;"><?= $topProductos['data'][$i] ?></td>
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
<h2>Top Vendedores (por Monto Vendido)</h2>
<table class="top-table">
  <thead>
    <tr>
      <th style="width:25px;">#</th>
      <th>Vendedor</th>
      <th>Proporción de ventas</th>
      <th style="width:110px; text-align:right;">Monto Vendido</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($topVendedores['labels'])): ?>
    <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:10px;">Sin datos registrados</td></tr>
    <?php else: ?>
    <?php foreach ($topVendedores['labels'] as $i => $label): ?>
    <tr>
      <td style="font-weight:bold; color:#f59e0b;"><?= $i+1 ?></td>
      <td style="font-weight:bold; color:#334155;"><?= htmlspecialchars($label) ?></td>
      <td>
        <div class="bar-outer">
          <div class="bar-inner amber" style="width:<?= barPct($topVendedores['data'][$i], $maxVendedores) ?>%;"></div>
        </div>
      </td>
      <td style="text-align:right; font-weight:bold; color:#0f172a;"><?= fmtR($topVendedores['data'][$i]) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<div class="section-spacer"></div>

<!-- ══ Evolución Mensual ══ -->
<h2>Evolución Mensual — Cotizaciones Totales vs Concluidas</h2>
<table class="evo-table">
  <thead>
    <tr>
      <th class="mes-col" style="text-align:left; color:#ffffff;">Mes</th>
      <th style="width:25%;">Cotizaciones Totales</th>
      <th style="width:25%;">Cotizaciones Concluidas</th>
      <th style="width:22%;">% Efectividad (Ventas)</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($evolucion['meses'])): ?>
    <tr><td colspan="4" style="text-align:center; color:#9ca3af; padding:10px;">Sin datos registrados en este período</td></tr>
    <?php else: ?>
    <?php 
      $sumTotalCot = 0;
      $sumTotalConc = 0;
      foreach ($evolucion['meses'] as $i => $mes): 
        $totMes  = (int)($evolucion['cotizaciones'][$i] ?? 0);
        $concMes = (int)($evolucion['concluidas'][$i] ?? 0);
        $sumTotalCot += $totMes;
        $sumTotalConc += $concMes;
        $pctMes = $totMes > 0 ? round(($concMes / $totMes) * 100, 1) : 0;
    ?>
    <tr>
      <td class="mes-col"><?= htmlspecialchars($mesesFmt[$i] ?? $mes) ?></td>
      <td>
        <div class="evo-badge-total">
          <?= number_format($totMes) ?>
        </div>
      </td>
      <td>
        <div class="evo-badge-conc">
          <?= number_format($concMes) ?>
        </div>
      </td>
      <td>
        <div class="evo-badge-pct <?= $pctMes > 0 ? 'green' : 'gray' ?>">
          <?= number_format($pctMes, 1) ?>%
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
  <?php if (!empty($evolucion['meses'])): 
    $pctGlobal = $sumTotalCot > 0 ? round(($sumTotalConc / $sumTotalCot) * 100, 1) : 0;
  ?>
  <tfoot>
    <tr>
      <td style="text-align:left;">TOTAL / EFECTIVIDAD GLOBAL</td>
      <td><?= number_format($sumTotalCot) ?></td>
      <td><?= number_format($sumTotalConc) ?></td>
      <td>
        <div class="evo-badge-pct <?= $pctGlobal > 0 ? 'green' : 'gray' ?>" style="font-size:8px;">
          <?= number_format($pctGlobal, 1) ?>%
        </div>
      </td>
    </tr>
  </tfoot>
  <?php endif; ?>
</table>

<div class="section-spacer"></div>

<!-- ══ Ventas por Cliente por Mes (Monto y % del Mes) ══ -->
<h2>Ventas a Clientes por Mes (Desglose y % de Participación Mensual)</h2>
<?php if (empty($ventasClientesMes['meses'])): ?>
<table class="top-table">
  <tr><td style="text-align:center; color:#9ca3af; padding:10px;">Sin ventas registradas por cliente en este período</td></tr>
</table>
<?php else: ?>
<table class="tbl-mes-cliente" style="width:100%;">
  <thead>
    <tr>
      <th style="width:22px;">#</th>
      <th>Cliente</th>
      <th style="width:24%;">Proporción</th>
      <th style="width:38px; text-align:center;">% Mes</th>
      <th style="width:80px; text-align:right;">Monto Vendido</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    // Mostrar los meses más recientes primero
    $mesesOrdenados = array_reverse($ventasClientesMes['meses']);
    foreach ($mesesOrdenados as $mKey):
        $clientesDelMes = $ventasClientesMes['porMes'][$mKey] ?? [];
        if (empty($clientesDelMes)) continue;

        // Calcular el total vendido en este mes específico
        $totalMesVendido = array_sum(array_column($clientesDelMes, 'monto'));
        $maxMontoMes = !empty($clientesDelMes) ? max(array_column($clientesDelMes, 'monto')) : 1;

        // Formatear nombre de mes (ej. Ene 2026)
        $partesM = explode('-', $mKey);
        $nombresM = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $labelMes = isset($partesM[1]) ? ($nombresM[(int)$partesM[1] - 1] . ' ' . $partesM[0]) : $mKey;
    ?>
    <tr>
      <td colspan="5" class="tbl-mes-header-td">
        <span class="mes-badge"><?= htmlspecialchars($labelMes) ?></span>
        <span style="font-size:8px; color:#475569; margin-left:8px; font-weight:normal;">
          Total facturado en el mes: <strong style="color:#0f172a;"><?= fmtR($totalMesVendido) ?></strong> (<?= count($clientesDelMes) ?> cliente<?= count($clientesDelMes) > 1 ? 's' : '' ?>)
        </span>
      </td>
    </tr>
    <?php foreach ($clientesDelMes as $idx => $c): 
        $montoC = (float)($c['monto'] ?? 0);
        $pctC   = $totalMesVendido > 0 ? round(($montoC / $totalMesVendido) * 100, 1) : 0;
    ?>
    <tr>
      <td style="font-weight:bold; color:#10757e;"><?= $idx + 1 ?></td>
      <td><?= htmlspecialchars($c['cliente']) ?></td>
      <td>
        <div class="bar-outer">
          <div class="bar-inner" style="width:<?= barPct((int)$montoC, (int)$maxMontoMes) ?>%;"></div>
        </div>
      </td>
      <td style="text-align:center; font-weight:bold; color:#059669; font-size:7.5px;"><?= number_format($pctC, 1, ',', '.') ?>%</td>
      <td style="text-align:right; font-weight:bold; color:#0f172a; font-size:7.5px;"><?= fmtR($montoC) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<!-- ══ FOOTER ══ -->
<table class="footer-table">
  <tr>
    <td style="font-size:7.5px; color:#64748b;">Documento generado automáticamente por el Sistema Impobiomedical</td>
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

$nombreArchivo = 'Reporte_Estadisticas.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit();
