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
        <div class="page-header page-header-panel">
            <h1 class="page-title"><i class="bi bi-speedometer2"></i> Panel Principal</h1>
            <p class="page-sub">Resumen general de operaciones y gestión comercial</p>
        </div>

        <!-- KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="bi bi-file-earmark-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($totalCotizaciones) ?></div>
                    <div class="kpi-label"><?= $rol === 'admin' ? 'Cotizaciones Totales' : 'Mis Cotizaciones' ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-alt"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($cotizacionesMes) ?></div>
                    <div class="kpi-label">Cotizaciones este Mes</div>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-green"><i class="bi bi-building"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($totalClientes ?? 0) ?></div>
                    <div class="kpi-label">Total Clientes</div>
                </div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-blue"><i class="bi bi-box-seam-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($totalProductos ?? 0) ?></div>
                    <div class="kpi-label">Productos Activos</div>
                </div>
            </div>
        </div>

        <!-- Accesos rápidos -->
        <div class="quick-actions">
            <h2 class="section-title">Accesos Rápidos</h2>
            <div class="actions-grid">
                <a href="<?= $basePath ?>?module=cotizaciones&action=crear&nueva=1" class="action-card">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Nueva Cotización</span>
                </a>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="action-card">
                    <i class="bi bi-search"></i>
                    <span>Consultar Cotizaciones</span>
                </a>
                <a href="<?= $basePath ?>?module=ordenes&action=consultar" class="action-card">
                    <i class="bi bi-cart-check-fill"></i>
                    <span>Órdenes de Compra</span>
                </a>
                <?php if ($rol === 'admin'): ?>
                <a href="<?= $basePath ?>?module=clientes" class="action-card">
                    <i class="bi bi-building"></i>
                    <span>Clientes</span>
                </a>
                <a href="<?= $basePath ?>?module=productos" class="action-card">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Catálogo</span>
                </a>
                <a href="<?= $basePath ?>?module=usuarios" class="action-card">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
                <a href="<?= $basePath ?>?module=estadisticas" class="action-card">
                    <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Estadísticas</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
