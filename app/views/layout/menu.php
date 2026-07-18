<?php
/**
 * Menú lateral — partial puro.
 * Requiere sesión activa con $_SESSION['rol'] y $_SESSION['usuario_nombre'].
 */
if (!isset($_SESSION['usuario_nombre'])) {
    $base = defined('BASE_URL') ? BASE_URL : '/';
    header('Location: ' . $base);
    exit();
}
$rol      = $_SESSION['rol'];
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>
<nav class="menu-principal">
    <div class="menu-lateral" id="menuLateral">
            <div class="logo-simple-wrap" style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 20px 0;">
                <div class="ecg-container-menu" style="width: 120px; height: 60px;">
                    <svg viewBox="0 0 500 100" preserveAspectRatio="none" style="width: 100%; height: 100%; stroke: #10757e; stroke-width: 4; fill: none; stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: dash 4s linear infinite; filter: drop-shadow(0 0 5px rgba(16,117,126,0.5));">
                        <polyline points="0,50 150,50 170,20 190,80 210,10 230,90 250,50 500,50" />
                    </svg>
                </div>
            </div>

        <ul class="lista-menu-lateral">
            <li>
                <a href="<?= $basePath ?>?module=panel" title="Panel">
                    <i class="bi bi-house-door-fill"></i>
                </a>
            </li>
            <?php if ($rol === 'admin'): ?>
            <li class="menu-desplegable" data-panel="admin" title="Administración">
                <a href="#"><i class="bi bi-gear-fill"></i></a>
            </li>
            <?php endif; ?>
            <li class="menu-desplegable" data-panel="cotizaciones" title="Cotizaciones">
                <a href="#"><i class="bi bi-file-earmark-text-fill"></i></a>
            </li>
            <li>
                <a href="<?= $basePath ?>?action=logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="panel-flotante" id="panel">
    <ul class="submenu" id="submenu-dinamico"></ul>
</div>

<script>
(function () {
    const panel   = document.getElementById('panel');
    const submenu = document.getElementById('submenu-dinamico');
    let   timeout;

    const menus = {
        admin: `
            <h3>Administración</h3>
            <li><a href="<?= $basePath ?>?module=estadisticas"><i class="fas fa-chart-bar"></i> Estadísticas</a></li>
            <li><a href="<?= $basePath ?>?module=usuarios"><i class="fas fa-users"></i> Gestión de Usuarios</a></li>
            <li><a href="<?= $basePath ?>?module=productos"><i class="fas fa-box-open"></i> Gestión de Productos</a></li>
        `,
        cotizaciones: `
            <h3>Cotizaciones</h3>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=crear"><i class="fas fa-plus-circle"></i> Nueva Cotización</a></li>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=consultar"><i class="fas fa-search"></i> Consultar</a></li>
            <li><a href="<?= $basePath ?>?module=ordenes&action=consultar"><i class="fas fa-cart-arrow-down"></i> Órdenes de Compra</a></li>
            <li><a href="<?= $basePath ?>?module=clientes"><i class="fas fa-building"></i> Gestión de Clientes</a></li>
        `
    };

    document.querySelectorAll('.menu-desplegable').forEach(item => {
        const tipo = item.dataset.panel;
        item.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            submenu.innerHTML = menus[tipo] || '';
            panel.classList.add('visible');
        });
        item.addEventListener('mouseleave', () => {
            timeout = setTimeout(() => panel.classList.remove('visible'), 300);
        });
    });

    panel.addEventListener('mouseenter', () => clearTimeout(timeout));
    panel.addEventListener('mouseleave', () => panel.classList.remove('visible'));
})();
</script>
