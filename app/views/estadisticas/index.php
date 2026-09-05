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
            
            <!-- Rendimiento Mensual (Barras y Líneas con Selector de Vendedor) -->
            <div class="chart-container">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    <h2 class="section-title" style="margin: 0; border: none; padding: 0;">
                        <i class="bi bi-graph-up"></i> Evolución Mensual: Cotizaciones Totales vs Concluidas
                    </h2>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="selectVendedorEvolucion" style="font-size: 12px; font-weight: 600; color: #4b5563;">Vendedor:</label>
                        <select id="selectVendedorEvolucion" style="padding: 6px 12px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; font-weight: 500; color: #1e293b; background: #f8fafc; outline: none; cursor: pointer;">
                            <option value="todos">Todos los usuarios</option>
                            <?php if (!empty($usuarios)): ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
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

            <!-- Top Clientes por Monto Vendido (Barras Horizontales) -->
            <div class="chart-container">
                <h2 class="section-title"><i class="bi bi-cash-stack" style="color: #8b5cf6;"></i> Top 5 Clientes (Monto Vendido $)</h2>
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

            <!-- Ventas a Clientes por Mes (Valor Total Vendido $$) -->
            <div class="chart-container" style="grid-column: 1 / -1;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    <div>
                        <h2 class="section-title" style="margin: 0; border: none; padding: 0;">
                            <i class="bi bi-wallet2" style="color: #059669;"></i> Ventas por Cliente por Mes (Monto Real Vendido $)
                        </h2>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;">
                            Visualiza a qué clientes se les vendió y el valor total facturado en cada período mensual.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="selectMesVentasClientes" style="font-size: 12px; font-weight: 600; color: #4b5563;">Mes:</label>
                        <select id="selectMesVentasClientes" style="padding: 6px 14px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; font-weight: 600; color: #0f172a; background: #f8fafc; outline: none; cursor: pointer;">
                            <option value="todos">Todos los meses (Acumulado)</option>
                            <?php if (!empty($ventasClientesMes['meses'])): ?>
                                <?php 
                                // Invertir para que el mes más reciente aparezca primero
                                $mesesRev = array_reverse($ventasClientesMes['meses']);
                                foreach ($mesesRev as $m): 
                                    $parts = explode('-', $m);
                                    $nombresMes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                    $nombreMes = isset($parts[1]) ? ($nombresMes[(int)$parts[1] - 1] . ' ' . $parts[0]) : $m;
                                ?>
                                    <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($nombreMes) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="chart-wrapper chart-wrapper-lg" style="min-height: 350px;">
                    <canvas id="ventasClientesMesChart"></canvas>
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
    const evolucionGeneral = <?= json_encode($evolucion) ?>;
    const evolucionPorUsuario = <?= json_encode($evolucionPorUsuario ?? []) ?>;
    const mesesBase = evolucionGeneral.meses || [];
    const labelsEvolucion = mesesBase.map(formatMes);

    const chartEvolucion = new Chart(ctxEvolucion, {
        type: 'bar',
        data: {
            labels: labelsEvolucion.length ? labelsEvolucion : ['Sin datos'],
            datasets: [
                {
                    type: 'bar',
                    label: 'Cotizaciones Totales',
                    data: (evolucionGeneral.cotizaciones && evolucionGeneral.cotizaciones.length) ? evolucionGeneral.cotizaciones : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.85)', // Azul vibrante
                    borderColor: '#2563eb',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    maxBarThickness: 38,
                    minBarLength: 5
                },
                {
                    type: 'bar',
                    label: 'Cotizaciones Concluidas',
                    data: (evolucionGeneral.concluidas && evolucionGeneral.concluidas.length) ? evolucionGeneral.concluidas : [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.85)', // Verde esmeralda
                    borderColor: '#059669',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    maxBarThickness: 38,
                    minBarLength: 5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { weight: 'bold' } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.dataset.label}: ${context.raw} cotización(es)`;
                        },
                        afterBody: function(tooltipItems) {
                            if (tooltipItems.length >= 2) {
                                const total = tooltipItems[0].raw || 0;
                                const conc = tooltipItems[1].raw || 0;
                                const pct = total > 0 ? ((conc / total) * 100).toFixed(1) : 0;
                                return ` Efectividad: ${pct}% de éxito`;
                            }
                            return '';
                        }
                    }
                }
            },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    // Event listener para el selector de vendedor
    const selVendedor = document.getElementById('selectVendedorEvolucion');
    if (selVendedor) {
        selVendedor.addEventListener('change', function() {
            const val = this.value;
            if (val === 'todos') {
                chartEvolucion.data.datasets[0].data = evolucionGeneral.cotizaciones || [0];
                chartEvolucion.data.datasets[1].data = evolucionGeneral.concluidas || [0];
            } else {
                const uid = parseInt(val, 10);
                const userEvo = evolucionPorUsuario[uid] || {};
                const cotis = mesesBase.map(m => userEvo[m] ? userEvo[m].cotizaciones : 0);
                const concs = mesesBase.map(m => userEvo[m] ? userEvo[m].concluidas : 0);
                chartEvolucion.data.datasets[0].data = cotis;
                chartEvolucion.data.datasets[1].data = concs;
            }
            chartEvolucion.update();
        });
    }

    // ── 2. Top Productos (Doughnut) ──
    const ctxProd = document.getElementById('productosChart').getContext('2d');
    const prodData = <?= json_encode($topProductos) ?>;

    new Chart(ctxProd, {
        type: 'doughnut',
        data: {
            labels: prodData.labels.length ? prodData.labels : ['Sin datos'],
            datasets: [{
                data: prodData.data.length ? prodData.data : [1],
                backgroundColor: ['#10757e', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right', 
                    labels: { 
                        boxWidth: 12, 
                        padding: 14, 
                        font: { size: 11.5, weight: '500' },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const meta = chart.getDatasetMeta(0);
                                    const style = meta.controller.getStyle(i);
                                    const val = data.datasets[0].data[i] || 0;
                                    const labelCorto = label.length > 25 ? label.substring(0, 22) + '...' : label;
                                    return {
                                        text: `${labelCorto} (${val})`,
                                        fillStyle: style.backgroundColor,
                                        strokeStyle: style.borderColor,
                                        lineWidth: style.borderWidth,
                                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    } 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const val = context.raw || 0;
                            return ` ${label}: ${val} unidad(es)`;
                        }
                    }
                }
            },
            cutout: '65%',
            layout: { padding: { top: 10, bottom: 10, left: 10, right: 10 } }
        }
    });

    // ── 3. Top Clientes (Monto Vendido $$ - Bar Horizontal) ──
    const ctxClientes = document.getElementById('clientesChart').getContext('2d');
    const clientData = <?= json_encode($topClientes) ?>;

    new Chart(ctxClientes, {
        type: 'bar',
        data: {
            labels: clientData.labels.length ? clientData.labels : ['Sin datos'],
            datasets: [{
                label: 'Monto Comprado ($)',
                data: clientData.data.length ? clientData.data : [0],
                backgroundColor: 'rgba(139, 92, 246, 0.85)', // Morado
                borderColor: '#7c3aed',
                borderWidth: 1,
                borderRadius: 5,
                maxBarThickness: 32
            }]
        },
        options: {
            indexAxis: 'y', // Barras horizontales
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: { left: 10, right: 15, top: 5, bottom: 5 }
            },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].label || '';
                        },
                        label: function(context) {
                            return ' Total Comprado: $' + Number(context.raw || 0).toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: { 
                y: {
                    ticks: {
                        font: { size: 11.5, weight: '500' },
                        color: '#334155',
                        callback: function(val, index) {
                            const label = this.getLabelForValue(val);
                            if (typeof label === 'string' && label.length > 28) {
                                return label.substring(0, 25) + '...';
                            }
                            return label;
                        }
                    },
                    grid: { display: false }
                },
                x: { 
                    beginAtZero: true, 
                    ticks: { 
                        color: '#64748b',
                        callback: function(val) {
                            if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return '$' + (val / 1000).toFixed(0) + 'k';
                            return '$' + val;
                        }
                    },
                    grid: { color: '#f1f5f9' }
                } 
            }
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

    // ── 5. Ventas por Cliente por Mes (Gráfico de Barras con Valor Total $$) ──
    const canvasVentasMes = document.getElementById('ventasClientesMesChart');
    if (canvasVentasMes) {
        const ctxVentasMes = canvasVentasMes.getContext('2d');
        const dataVentasMes = <?= json_encode($ventasClientesMes['porMes'] ?? []) ?>;
        const selMes = document.getElementById('selectMesVentasClientes');

        const obtenerDatosMes = (mes) => {
            if (!mes || mes === 'todos') {
                // Acumular ventas de todos los meses por cliente
                const mapaClientes = {};
                Object.values(dataVentasMes).forEach(lista => {
                    lista.forEach(item => {
                        mapaClientes[item.cliente] = (mapaClientes[item.cliente] || 0) + item.monto;
                    });
                });
                // Ordenar clientes por monto descendente
                const ordenados = Object.entries(mapaClientes)
                    .sort((a, b) => b[1] - a[1]);
                return {
                    labels: ordenados.map(it => it[0]),
                    montos: ordenados.map(it => it[1])
                };
            }
            const items = dataVentasMes[mes] || [];
            return {
                labels: items.map(it => it.cliente),
                montos: items.map(it => it.monto)
            };
        };

        const mesInicial = selMes ? selMes.value : 'todos';
        const inicial = obtenerDatosMes(mesInicial);

        const chartVentasMes = new Chart(ctxVentasMes, {
            type: 'bar',
            data: {
                labels: inicial.labels.length ? inicial.labels : ['Sin ventas en este período'],
                datasets: [{
                    label: 'Total Vendido ($)',
                    data: inicial.montos.length ? inicial.montos : [0],
                    backgroundColor: 'rgba(16, 185, 129, 0.85)', // Verde corporativo
                    borderColor: '#059669',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    maxBarThickness: 42,
                    minBarLength: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                layout: {
                    padding: { top: 10, bottom: 10, left: 10, right: 15 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label || '';
                            },
                            label: function(context) {
                                return ' Monto Vendido: $' + Number(context.raw || 0).toLocaleString('es-CO');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: { size: 11, weight: '500' },
                            color: '#334155',
                            callback: function(val, index) {
                                const label = this.getLabelForValue(val);
                                if (typeof label === 'string' && label.length > 20) {
                                    return label.substring(0, 18) + '...';
                                }
                                return label;
                            }
                        },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#64748b',
                            callback: function(val) {
                                if (val >= 1000000) return '$' + (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return '$' + (val / 1000).toFixed(0) + 'k';
                                return '$' + val;
                            }
                        },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });

        if (selMes) {
            selMes.addEventListener('change', function() {
                const mesSel = this.value;
                const d = obtenerDatosMes(mesSel);
                chartVentasMes.data.labels = d.labels.length ? d.labels : ['Sin ventas en este período'];
                chartVentasMes.data.datasets[0].data = d.montos.length ? d.montos : [0];
                chartVentasMes.update();
            });
        }
    }

});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
