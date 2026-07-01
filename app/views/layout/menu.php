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
        <div class="logo-wrap">
            <div class="logo-ring-wrap">
                <div class="logo-ring"></div>
                <div class="logo-ring"></div>
                <div class="logo-ring"></div>
                <div class="logo-halo"></div>
                <div class="logo-halo"></div>
                <div class="logo-halo"></div>
                <div class="logo-dash"></div>
                <div class="logo-circle">
                    <img src="<?= $basePath ?>logo/logo.png" alt="Logo Impobiomedical">
                </div>
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
            <li class="menu-desplegable" data-panel="catalogo" title="Catálogo">
                <a href="#"><i class="bi bi-box-seam-fill"></i></a>
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
            <li><a href="<?= $basePath ?>?module=usuarios"><i class="fas fa-user-friends"></i> Lista Usuarios</a></li>
            <li><a href="<?= $basePath ?>?module=usuarios&action=crear"><i class="fas fa-user-plus"></i> Nuevo Usuario</a></li>
        `,
        cotizaciones: `
            <h3>Cotizaciones</h3>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=crear"><i class="fas fa-plus-circle"></i> Nueva Cotización</a></li>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=consultar"><i class="fas fa-search"></i> Consultar</a></li>
            <li><a href="<?= $basePath ?>?module=clientes"><i class="fas fa-building"></i> Clientes</a></li>
            <li><a href="<?= $basePath ?>?module=clientes&action=crear"><i class="fas fa-plus"></i> Nuevo Cliente</a></li>
        `,
        catalogo: `
            <h3>Catálogo</h3>
            <li><a href="<?= $basePath ?>?module=productos"><i class="fas fa-box-open"></i> Lista Productos</a></li>
            <?php if ($rol === 'admin'): ?>
            <li><a href="<?= $basePath ?>?module=productos&action=crear"><i class="fas fa-plus"></i> Nuevo Producto</a></li>
            <?php endif; ?>
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
