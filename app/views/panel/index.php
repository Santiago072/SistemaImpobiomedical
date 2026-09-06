<?php
/**
 * Panel principal (Dashboard) — Sistema Impobiomedical
 * Variables: $totalCotizaciones (int), $cotizacionesMes (int)
 */
$pageTitle = 'Panel Principal';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';

$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
$rol      = $_SESSION['rol'] ?? 'usuario';
?>

<div class="layout-main">
    <?php 
    $esDashboard = true;
    $usuario = [
        'nombre' => $_SESSION['usuario_nombre'] ?? '', 
        'rol'    => $_SESSION['rol'] ?? '',
        'codigo' => $_SESSION['usuario_codigo'] ?? ''
    ];
    include __DIR__ . '/../layout/topbar.php'; 
    ?>

    <main class="contenido-principal">
        
        <!-- ── Banner de Bienvenida Estilo HDMI (Paleta Teal Impobiomedical) ── -->
        <section class="banner-welcome-corp">
            <div class="banner-welcome-header">
                <div class="banner-header-block">
                    <div class="banner-welcome-tag">
                        <i class="bi bi-geo-alt-fill"></i> Sedes Florencia, Caquetá &bull; Sabaneta / Medellín
                    </div>
                    <h2 class="banner-title-corp">
                        Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?>
                    </h2>
                    <p class="banner-subtitle-corp">
                        Plataforma de gestión comercial, cálculo de rentabilidad, estructuración de cotizaciones y emisión de órdenes de compra (P.O.) de <strong>IMPOMIN S.A.S &bull; Impobiomedical</strong>.
                    </p>
                </div>

                <div class="banner-actions-group">
                    <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="btn-banner-action-white">
                        <i class="bi bi-plus-circle-fill"></i> Nueva Cotización
                    </a>
                    <a href="<?= $basePath ?>?module=ordenes&action=consultar" class="btn-banner-action-translucent">
                        <i class="bi bi-cart-check-fill"></i> Órdenes de Compra
                    </a>
                </div>
            </div>
        </section>

        <!-- ── Métricas KPI en Tiempo Real (Estructura HDMI con Estilo Teal) ── -->
        <section class="section-kpis-wrap">
            <div class="section-header-row">
                <h3 class="section-title-dash">
                    <i class="bi bi-graph-up-arrow icon-kpi-title"></i> Métricas del Sistema Comercial
                </h3>
                <span class="txt-kpi-subhead">Actualizado automáticamente</span>
            </div>

            <div class="kpi-grid-hdmi">
                <!-- KPI 1: Cotizaciones Totales -->
                <div class="kpi-card-hdmi">
                    <div>
                        <div class="kpi-card-header-row">
                            <span class="kpi-label-hdmi"><?= $rol === 'admin' ? 'Cotizaciones' : 'Mis Cotizaciones' ?></span>
                            <div class="kpi-icon-box-hdmi kpi-box-teal">
                                <i class="bi bi-file-earmark-check-fill"></i>
                            </div>
                        </div>
                        <div class="kpi-value-row">
                            <span class="kpi-number-hdmi"><?= number_format($totalCotizaciones) ?></span>
                            <span class="kpi-status-badge badge-teal-status">Histórico</span>
                        </div>
                    </div>
                    <p class="kpi-footer-note">Propuestas comerciales emitidas</p>
                </div>

                <!-- KPI 2: Cotizaciones este Mes -->
                <div class="kpi-card-hdmi">
                    <div>
                        <div class="kpi-card-header-row">
                            <span class="kpi-label-hdmi">Cotizaciones Mes</span>
                            <div class="kpi-icon-box-hdmi kpi-box-amber">
                                <i class="bi bi-calendar-check-fill"></i>
                            </div>
                        </div>
                        <div class="kpi-value-row">
                            <span class="kpi-number-hdmi"><?= number_format($cotizacionesMes) ?></span>
                            <span class="kpi-status-badge badge-amber-status">Mes Actual</span>
                        </div>
                    </div>
                    <p class="kpi-footer-note">Actividad registrada en el periodo</p>
                </div>

                <!-- KPI 3: Clientes Activos -->
                <div class="kpi-card-hdmi">
                    <div>
                        <div class="kpi-card-header-row">
                            <span class="kpi-label-hdmi">Clientes</span>
                            <div class="kpi-icon-box-hdmi kpi-box-cyan">
                                <i class="bi bi-building-check"></i>
                            </div>
                        </div>
                        <div class="kpi-value-row">
                            <span class="kpi-number-hdmi"><?= number_format($totalClientes ?? 0) ?></span>
                            <span class="kpi-status-badge badge-cyan-status">Directorio</span>
                        </div>
                    </div>
                    <p class="kpi-footer-note">Instituciones y clientes activos</p>
                </div>

                <!-- KPI 4: Catálogo de Productos -->
                <div class="kpi-card-hdmi">
                    <div>
                        <div class="kpi-card-header-row">
                            <span class="kpi-label-hdmi">Inventario Catálogo</span>
                            <div class="kpi-icon-box-hdmi kpi-box-blue">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                        </div>
                        <div class="kpi-value-row">
                            <span class="kpi-number-hdmi"><?= number_format($totalProductos ?? 0) ?></span>
                            <span class="kpi-status-badge badge-blue-status">En Catálogo</span>
                        </div>
                    </div>
                    <p class="kpi-footer-note">Productos biomédicos disponibles</p>
                </div>
            </div>
        </section>

        <!-- ── Sección Inferior: Resumen y Accesos Directos ── -->
        <section class="grid-dashboard-bottom-hdmi">
            
            <!-- Tarjeta 1: Últimos Productos del Catálogo Biomédico -->
            <div class="card-dash-panel">
                <div class="card-panel-header">
                    <div>
                        <h4 class="card-panel-title">
                            <i class="bi bi-box-seam-fill icon-card-panel"></i> Últimos Productos del Catálogo
                        </h4>
                        <p class="card-panel-subtitle">Equipos médicos y suministros disponibles</p>
                    </div>
                    <a href="<?= $basePath ?>?module=productos" class="link-view-all">
                        Ver catálogo &rarr;
                    </a>
                </div>

                <div class="recent-list-group">
                    <?php if (empty($ultimosProductos)): ?>
                        <div class="empty-list-notice">
                            <i class="bi bi-box"></i>
                            <p>No hay productos registrados en el catálogo aún.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ultimosProductos as $p): ?>
                            <div class="recent-list-item">
                                <div class="recent-prod-left">
                                    <div class="recent-prod-img-box">
                                        <?php if (!empty($p['foto'])): ?>
                                            <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($p['foto']) ?>" alt="<?= htmlspecialchars($p['titulo']) ?>" class="recent-prod-thumb">
                                        <?php else: ?>
                                            <i class="bi bi-box-seam text-muted-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="recent-item-info">
                                        <div class="recent-item-title-row">
                                            <?php if (!empty($p['codigo_producto'])): ?>
                                                <span class="badge-num-po"><?= htmlspecialchars($p['codigo_producto']) ?></span>
                                            <?php endif; ?>
                                            <span class="recent-prod-full-name">
                                                <?= htmlspecialchars($p['titulo']) ?>
                                            </span>
                                        </div>
                                        <div class="recent-item-meta">
                                            <span><i class="bi bi-tag"></i> <?= htmlspecialchars(!empty($p['categoria']) ? $p['categoria'] : 'Biomédico') ?></span>
                                            <?php if (!empty($p['proveedor'])): ?>
                                                <span>&bull;</span>
                                                <span><i class="bi bi-truck"></i> <?= htmlspecialchars($p['proveedor']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta 2: Últimas Cotizaciones Registradas -->
            <div class="card-dash-panel">
                <div class="card-panel-header">
                    <div>
                        <h4 class="card-panel-title">
                            <i class="bi bi-clock-history icon-card-panel"></i> Últimas Cotizaciones
                        </h4>
                        <p class="card-panel-subtitle">Movimientos y propuestas recientes</p>
                    </div>
                    <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="link-view-all">
                        Ver todas &rarr;
                    </a>
                </div>

                <div class="recent-list-group">
                    <?php if (empty($cotizacionesRecientes)): ?>
                        <div class="empty-list-notice">
                            <i class="bi bi-inbox"></i>
                            <p>No hay cotizaciones registradas recientemente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cotizacionesRecientes as $c): ?>
                            <div class="recent-list-item">
                                <div class="recent-item-info">
                                    <div class="recent-item-title-row">
                                        <span class="badge-num-po"><?= htmlspecialchars($c['numero_cotizacion'] ?? 'BORRADOR') ?></span>
                                        <span class="recent-client-name"><?= htmlspecialchars($c['cliente_nombre'] ?? 'Cliente General') ?></span>
                                    </div>
                                    <div class="recent-item-meta">
                                        <span><i class="bi bi-person"></i> <?= htmlspecialchars($c['asesor_nombre'] ?? ($c['nombre_usuario'] ?? '')) ?></span>
                                        <span>&bull;</span>
                                        <span><i class="bi bi-calendar3"></i> <?= htmlspecialchars($c['fecha_creacion'] ?? '') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </section>

    </main>
</div>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
