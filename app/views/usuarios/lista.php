<?php
/**
 * Vista: Lista de usuarios
 * Variables: $usuarios, $busqueda, $paginaActual, $totalPaginas, $total,
 *            $mensajeExito, $mensajeError, $urlBase
 */
$pageTitle = 'Lista Usuarios';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Gestión de Usuarios';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Gestión Usuarios</h1></div>

    <?php if ($mensajeExito): ?>
    <div class="success-box">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensajeExito) ?>
    </div>
    <?php endif; ?>
    <?php if ($mensajeError): ?>
    <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>

    <div class="filter-panel">
        <i class="bi bi-search filter-icon"></i>
        <form action="<?= $basePath ?>" method="GET" class="formulario-busqueda" style="display:flex; flex:1; gap:10px; align-items:center;">
            <input type="hidden" name="module" value="usuarios">
            <input type="hidden" name="action" value="lista">
            <input type="text" name="busqueda" class="filter-input" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar usuario..." maxlength="60">
            <button type="submit" style="display:none;"></button>
            <?php if ($busqueda): ?>
            <a href="<?= $basePath ?>?module=usuarios&action=lista" class="boton-limpiar" style="white-space: nowrap;">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="grid-cards">
        <?php foreach ($usuarios as $u): ?>
        <div class="card-item user-card" style="position:relative;">
            <!-- Status Badge in Top Right -->
            <div class="user-status-badge" style="position:absolute; top:20px; right:20px; display:flex; align-items:center; gap:6px; background:<?= strtolower($u['estado']) === 'activo' ? 'rgba(46, 213, 115, 0.15)' : 'rgba(255, 71, 87, 0.15)' ?>; padding:5px 10px; border-radius:20px; border: 1px solid <?= strtolower($u['estado']) === 'activo' ? 'rgba(46, 213, 115, 0.3)' : 'rgba(255, 71, 87, 0.3)' ?>;">
                <i class="bi bi-circle-fill" style="font-size:8px; color:<?= strtolower($u['estado']) === 'activo' ? '#2ed573' : '#ff4757' ?>; box-shadow: 0 0 8px <?= strtolower($u['estado']) === 'activo' ? '#2ed573' : '#ff4757' ?>; border-radius:50%;"></i> 
                <span style="font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color:<?= strtolower($u['estado']) === 'activo' ? '#2ed573' : '#ff4757' ?>;"><?= htmlspecialchars($u['estado']) ?></span>
            </div>

            <div class="user-avatar" style="margin-top: 10px;">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="card-title" style="margin-bottom: 8px; font-size: 20px;"><?= htmlspecialchars($u['nombre']) ?></div>
            <div class="card-subtitle" style="margin-bottom: 20px;">
                <span class="role-badge" style="font-size: 13px; padding: 6px 12px;"><i class="bi bi-shield-check"></i> Rol: <?= htmlspecialchars(ucfirst($u['rol'])) ?></span>
            </div>
            
            <div class="card-info card-subtitle" style="display: flex; flex-direction: column; gap: 8px; font-size: 13px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center;"><i class="bi bi-envelope" style="color: var(--gold); margin-right: 8px;"></i> <?= htmlspecialchars($u['correo']) ?></div>
                <div style="display: flex; align-items: center;"><i class="bi bi-telephone" style="color: var(--gold); margin-right: 8px;"></i> <?= htmlspecialchars($u['telefono']) ?></div>
            </div>

            <div class="card-actions">
                <a href="<?= $basePath ?>?module=usuarios&action=editar&id=<?= intval($u['id']) ?>" class="boton-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="<?= $basePath ?>?module=usuarios&action=eliminar&id=<?= intval($u['id']) ?>"
                   class="boton-eliminar"
                   data-confirm="¿Está seguro de eliminar este usuario?">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($usuarios)): ?>
        <div class="w-100 text-center p-30 text-gold" style="grid-column: 1 / -1;">
            <i class="bi bi-search" style="font-size:30px; display:block; margin-bottom:10px;"></i>
            No se encontraron usuarios.
        </div>
        <?php endif; ?>
    </div>

    <?php include dirname(__DIR__) . '/partials/paginacion.php'; ?>
    <?php if ($total > 0): ?>
    <p class="pag-info">Mostrando página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $total ?> usuarios)</p>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
