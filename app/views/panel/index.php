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
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-speedometer2"></i> Panel Principal</h1>
            <p class="page-sub">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                <span class="badge-codigo"><?= htmlspecialchars($_SESSION['usuario_codigo'] ?? '') ?></span>
            </p>
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
            <?php if ($rol === 'admin'): ?>
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-alt"><i class="bi bi-calendar-check-fill"></i></div>
                <div class="kpi-info">
                    <div class="kpi-num"><?= number_format($cotizacionesMes) ?></div>
                    <div class="kpi-label">Cotizaciones este Mes</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Accesos rápidos -->
        <div class="quick-actions">
            <h2 class="section-title">Accesos Rápidos</h2>
            <div class="actions-grid">
                <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="action-card">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Nueva Cotización</span>
                </a>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="action-card">
                    <i class="bi bi-search"></i>
                    <span>Consultar Cotizaciones</span>
                </a>
                <a href="<?= $basePath ?>?module=clientes" class="action-card">
                    <i class="bi bi-building"></i>
                    <span>Clientes</span>
                </a>
                <a href="<?= $basePath ?>?module=productos" class="action-card">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Catálogo</span>
                </a>
                <?php if ($rol === 'admin'): ?>
                <a href="<?= $basePath ?>?module=usuarios" class="action-card">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.badge-codigo {
    display: inline-block;
    background: var(--copper);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 20px;
    margin-left: 8px;
    vertical-align: middle;
}
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}
.kpi-card {
    background: var(--glass-bg-medium);
    border: 1px solid var(--glass-border-medium);
    border-radius: 16px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    backdrop-filter: blur(10px);
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-gold);
}
.kpi-icon {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--copper), var(--honey));
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    flex-shrink: 0;
}
.kpi-icon-alt {
    background: linear-gradient(135deg, #2d6a6a, var(--copper));
}
.kpi-num { font-size: 28px; font-weight: 700; color: var(--amber); line-height: 1; }
.kpi-label { font-size: 12px; color: rgba(255,255,255,.6); margin-top: 4px; }
.section-title {
    font-size: 15px; font-weight: 600;
    color: var(--gilt); margin-bottom: 16px;
}
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
}
.action-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    padding: 20px;
    display: flex; flex-direction: column;
    align-items: center; gap: 10px;
    text-decoration: none; color: #fff;
    font-size: 13px; font-weight: 500;
    text-align: center;
    transition: all .2s;
}
.action-card:hover {
    background: var(--glass-bg-medium);
    border-color: var(--copper);
    transform: translateY(-2px);
    color: var(--gilt);
}
.action-card i { font-size: 26px; color: var(--amber); }
</style>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
