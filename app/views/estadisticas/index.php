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
    <main class="contenido-principal">
        <?php 
        $usuario = ['nombre' => $_SESSION['usuario_nombre'] ?? '', 'rol' => $_SESSION['rol'] ?? ''];
        include __DIR__ . '/../layout/topbar.php'; 
        ?>
        
        <div class="estadisticas-container">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
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
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
                    <?php if ($fechaInicio || $fechaFin): ?>
                        <a href="<?= $basePath ?>?module=estadisticas" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>

        <!-- KPIs Mejorados -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="bi bi-currency-dollar"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num">$<?= number_format($kpis['monto_cotizado_mes'], 0) ?></div>
                    <div class="kpi-label">Monto Cotizado (Mes)</div>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"><i class="bi bi-file-earmark-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($kpis['total_cotizaciones']) ?></div>
                    <div class="kpi-label">Cotizaciones Totales</div>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="bi bi-cart-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($kpis['total_ordenes']) ?></div>
                    <div class="kpi-label">Órdenes de Compra</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="bi bi-people-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($kpis['total_clientes']) ?></div>
                    <div class="kpi-label">Clientes Registrados</div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            
            <!-- Rendimiento Mensual (Barras y Líneas) -->
            <div class="chart-container" style="grid-column: span 2;">
                <h2 class="section-title">Evolución: Cotizaciones vs Órdenes (Últimos 6 meses)</h2>
                <div class="chart-wrapper" style="height: 350px;">
                    <canvas id="evolucionChart"></canvas>
                </div>
            </div>

            <!-- Top Productos (Doughnut) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Productos Cotizados</h2>
                <div class="chart-wrapper" style="height: 300px;">
                    <canvas id="productosChart"></canvas>
                </div>
            </div>

            <!-- Top Clientes (Barras Horizontales) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Clientes Recurrentes</h2>
                <div class="chart-wrapper" style="height: 300px;">
                    <canvas id="clientesChart"></canvas>
                </div>
            </div>

            <!-- Top Vendedores (Barras Horizontales) -->
            <div class="chart-container">
                <h2 class="section-title">Top 5 Vendedores (Órdenes)</h2>
                <div class="chart-wrapper" style="height: 300px;">
                    <canvas id="vendedoresChart"></canvas>
                </div>
            </div>
            
        </div>

    </main>
</div>

<style>
.estadisticas-container {
    max-width: 1400px;
    margin: 0 auto;
    padding-bottom: 40px;
}
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.kpi-card {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
}
.kpi-icon {
    width: 55px; height: 55px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    flex-shrink: 0;
}
.kpi-num { font-size: 24px; font-weight: 800; color: #1f2937; line-height: 1; margin-bottom: 4px; letter-spacing: -0.5px; }
.kpi-label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; }

.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
}
.chart-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}
.section-title {
    font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 20px;
}
.chart-wrapper { position: relative; width: 100%; }

.filter-form {
    display: flex; align-items: flex-end; gap: 10px;
    background: #fff; padding: 12px 16px; border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    border: 1px solid #e5e7eb;
    flex-wrap: wrap;
}
.filter-group { display: flex; flex-direction: column; gap: 4px; }
.filter-group label { font-size: 11px; font-weight: 700; color: #4b5563; text-transform: uppercase; }
.filter-group input { padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; font-size: 13px; }
.filter-group input:focus { border-color: #10757e; box-shadow: 0 0 0 2px rgba(16,117,126,0.1); }
.filter-form .btn { padding: 6px 12px; font-size: 13px; border-radius: 6px; }

@media (max-width: 1024px) {
    .charts-grid { grid-template-columns: 1fr; }
    .chart-container[style*="grid-column"] { grid-column: auto !important; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const formatMes = mesStr => {
        const [year, month] = mesStr.split('-');
        return new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
    };

    // ── 1. Gráfico de Evolución (Barra + Línea combinada) ──
    const ctxEvolucion = document.getElementById('evolucionChart').getContext('2d');
    const evolucionData = <?= json_encode($evolucion) ?>;
    const labelsEvolucion = evolucionData.meses.map(formatMes);

    new Chart(ctxEvolucion, {
        type: 'bar',
        data: {
            labels: labelsEvolucion.length ? labelsEvolucion : ['Sin datos'],
            datasets: [
                {
                    type: 'bar',
                    label: 'Cotizaciones Generadas',
                    data: evolucionData.cotizaciones.length ? evolucionData.cotizaciones : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)', // Azul
                    borderRadius: 4,
                    maxBarThickness: 45
                },
                {
                    type: 'bar',
                    label: 'Órdenes de Compra',
                    data: evolucionData.ordenes.length ? evolucionData.ordenes : [0],
                    backgroundColor: '#10b981', // Verde
                    borderRadius: 4,
                    maxBarThickness: 45
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
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
                label: 'Cotizaciones creadas',
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
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
