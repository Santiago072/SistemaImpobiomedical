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
    <main class="contenido-principal">
        <?php 
        $esDashboard = true;
        $usuario = ['nombre' => $_SESSION['usuario_nombre'] ?? '', 'rol' => $_SESSION['rol'] ?? ''];
        include __DIR__ . '/../layout/topbar.php'; 
        ?>
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
                <?php if ($rol === 'admin'): ?>
                <a href="<?= $basePath ?>?module=productos" class="action-card">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Catálogo</span>
                </a>
                <?php endif; ?>
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
    background: linear-gradient(135deg, #10757e, #0a4f55);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 20px;
    margin-left: 10px;
    vertical-align: middle;
    box-shadow: 0 2px 4px rgba(16, 117, 126, 0.3);
}
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}
.kpi-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(229, 231, 235, 0.5);
    border-radius: 20px;
    padding: 28px;
    display: flex;
    align-items: center;
    gap: 24px;
    transition: all .3s ease;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.04);
}
.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(16, 117, 126, 0.1);
    border-color: rgba(16, 117, 126, 0.2);
}
.kpi-icon {
    width: 65px; height: 65px;
    border-radius: 18px;
    background: linear-gradient(135deg, #10757e, #20b2aa);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 6px 15px rgba(16, 117, 126, 0.25);
}
.kpi-icon-alt {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 6px 15px rgba(245, 158, 11, 0.25);
}
.kpi-num { font-size: 36px; font-weight: 800; color: #1f2937; line-height: 1; letter-spacing: -1px; }
.kpi-label { font-size: 14px; color: #6b7280; margin-top: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
.section-title {
    font-size: 18px; font-weight: 700;
    color: #1f2937; margin-bottom: 20px;
}
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
}
.action-card {
    background: #ffffff;
    border: 1px solid #f3f4f6;
    border-radius: 16px;
    padding: 24px;
    display: flex; flex-direction: column;
    align-items: center; gap: 14px;
    text-decoration: none; color: #374151;
    font-size: 14px; font-weight: 600;
    text-align: center;
    transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}
.action-card:hover {
    background: #ffffff;
    border-color: #10757e;
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 20px rgba(16, 117, 126, 0.08);
    color: #10757e;
}
.action-card i { 
    font-size: 32px; 
    color: #9ca3af; 
    transition: all .3s ease; 
}
.action-card:hover i { 
    color: #10757e; 
    transform: scale(1.1);
}
</style>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
