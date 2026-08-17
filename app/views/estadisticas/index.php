<?php
/**
 * Vista de Estadísticas — Módulo Analítico Avanzado
 */
$pageTitle = 'Estadísticas del Sistema';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';

$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php 
    $usuario = ['nombre' => $_SESSION['usuario_nombre'] ?? '', 'rol' => $_SESSION['rol'] ?? ''];
    include __DIR__ . '/../layout/topbar.php'; 
    ?>
    <main class="contenido-principal">
        
        <div class="estadisticas-container">
            <div class="page-header page-header-filters">
                <div>
                    <h1 class="page-title"><i class="bi bi-bar-chart-fill"></i> Panel de Estadísticas</h1>
                    <p class="page-sub">Análisis de rendimiento, cotizaciones y productos.</p>
                </div>
                
                <form method="GET" action="<?= $basePath ?>" class="filter-form">
                    <input type="hidden" name="module" value="estadisticas">
                    <div class="filter-group">
                        <label>Desde:</label>
                        <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                    </div>
                    <div class="filter-group">
                        <label>Hasta:</label>
                        <input type="date" name="fecha_fin" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
                        <?php if ($fechaInicio || $fechaFin): ?>
                            <a href="<?= $basePath ?>?module=estadisticas" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Limpiar</a>
                        <?php endif; ?>
                        <a href="<?= $basePath ?>?module=estadisticas&action=reporte_pdf<?= $fechaInicio ? '&fecha_inicio='.urlencode($fechaInicio) : '' ?><?= $fechaFin ? '&fecha_fin='.urlencode($fechaFin) : '' ?>"
                           class="btn btn-pdf" target="_blank" title="Exportar PDF del reporte actual">
                            <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                        </a>
                    </div>
                </form>
            </div>

        <!-- KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-green"><i class="bi bi-currency-dollar"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num">$<?= number_format($kpis['monto_cotizado_mes'], 0) ?></div>
                    <div class="kpi-label">Monto Cotizado<?= ($fechaInicio && $fechaFin) ? '' : ' (Mes)' ?></div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon"><i class="bi bi-bag-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num">$<?= number_format($kpis['monto_vendido'] ?? 0, 0) ?></div>
                    <div class="kpi-label">Monto Vendido (Ventas Reales)</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-file-earmark-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($kpis['total_cotizaciones']) ?></div>
                    <div class="kpi-label">Cotizaciones Totales</div>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-purple"><i class="bi bi-cart-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($kpis['total_ordenes']) ?></div>
                    <div class="kpi-label">Órdenes de Compra</div>
                </div>
            </div>

        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            
            <!-- Rendimiento Mensual (Barras y Líneas) -->
            <div class="chart-container">
                <h2 class="section-title"><i class="bi bi-graph-up"></i> Evolución Mensual: Cotizaciones Totales vs Concluidas</h2>
                <div class="chart-wrapper chart-wrapper-lg">
                    <canvas id="evolucionChart"></canvas>
                </div>
            </div>

            <!-- Top Productos (Doughnut) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Productos Cotizados</h2>
                <div class="chart-wrapper chart-wrapper-md">
                    <canvas id="productosChart"></canvas>
                </div>
            </div>

            <!-- Top Clientes (Barras Horizontales) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Clientes Recurrentes</h2>
                <div class="chart-wrapper chart-wrapper-md">
                    <canvas id="clientesChart"></canvas>
                </div>
            </div>

            <!-- Top Vendedores (Barras Horizontales) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Vendedores (Órdenes)</h2>
                <div class="chart-wrapper chart-wrapper-md">
                    <canvas id="vendedoresChart"></canvas>
                </div>
            </div>
            
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no se ha podido cargar.');
        return;
    }
    
    const formatMes = mesStr => {
        if (!mesStr || typeof mesStr !== 'string') return '';
        const parts = mesStr.split('-');
        if (parts.length < 2) return mesStr;
        const [year, month] = parts;
        return new Date(parseInt(year, 10), parseInt(month, 10) - 1).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
    };

    // ── 1. Gráfico de Evolución (Cotizaciones Totales vs Concluidas) ──
    const ctxEvolucion = document.getElementById('evolucionChart').getContext('2d');
    const evolucionData = <?= json_encode($evolucion) ?>;
    const labelsEvolucion = (evolucionData.meses || []).map(formatMes);

    new Chart(ctxEvolucion, {
        type: 'bar',
        data: {
            labels: labelsEvolucion.length ? labelsEvolucion : ['Sin datos'],
            datasets: [
                {
                    type: 'bar',
                    label: 'Cotizaciones Totales',
                    data: (evolucionData.cotizaciones && evolucionData.cotizaciones.length) ? evolucionData.cotizaciones : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.85)', // Azul vibrante
                    borderColor: '#2563eb',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    maxBarThickness: 40
                },
                {
                    type: 'bar',
                    label: 'Cotizaciones Concluidas 🟢',
                    data: (evolucionData.concluidas && evolucionData.concluidas.length) ? evolucionData.concluidas : [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.9)', // Verde esmeralda
                    borderColor: '#059669',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    maxBarThickness: 40
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { weight: 'bold' } } }
            },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // ── 2. Top Productos (Doughnut) ──
    const ctxProd = document.getElementById('productosChart').getContext('2d');
    const prodData = <?= json_encode($topProductos) ?>;

    new Chart(ctxProd, {
        type: 'doughnut',
        data: {
            labels: prodData.labels.length ? prodData.labels : ['Sin datos'],
            datasets: [{
                data: prodData.data.length ? prodData.data : [1],
                backgroundColor: ['#10757e', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#cbd5e1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right', 
                    labels: { boxWidth: 12, padding: 20, font: { size: 12 } } 
                }
            },
            cutout: '70%',
            layout: { padding: 20 }
        }
    });

    // ── 3. Top Clientes (Bar Horizontal) ──
    const ctxClientes = document.getElementById('clientesChart').getContext('2d');
    const clientData = <?= json_encode($topClientes) ?>;

    new Chart(ctxClientes, {
        type: 'bar',
        data: {
            labels: clientData.labels.length ? clientData.labels : ['Sin datos'],
            datasets: [{
                label: 'Cotizaciones emitidas',
                data: clientData.data.length ? clientData.data : [0],
                backgroundColor: 'rgba(139, 92, 246, 0.8)', // Morado
                borderRadius: 4,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y', // Hace que las barras sean horizontales
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // ── 4. Top Vendedores (Bar Horizontal) ──
    const ctxVend = document.getElementById('vendedoresChart').getContext('2d');
    const vendData = <?= json_encode($topVendedores) ?>;

    new Chart(ctxVend, {
        type: 'bar',
        data: {
            labels: vendData.labels.length ? vendData.labels : ['Sin datos'],
            datasets: [{
                label: 'Monto Vendido ($)',
                data: vendData.data.length ? vendData.data : [0],
                backgroundColor: 'rgba(245, 158, 11, 0.8)', // Naranja
                borderRadius: 4,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y', 
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Monto Vendido: $' + context.raw.toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: { x: { beginAtZero: true } }
        }
    });

});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
