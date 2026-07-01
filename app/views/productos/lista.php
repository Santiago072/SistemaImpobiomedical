<?php
/**
 * Vista: Lista de productos
 * Variables: $productos, $busqueda, $paginaActual, $totalPaginas, $total,
 *            $mensajeExito, $mensajeError, $urlBase
 */
$pageTitle = 'Lista de Productos';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Lista de Productos';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Lista de Productos</h1></div>

    <?php if ($mensajeExito): ?>
    <div class="success-box">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensajeExito) ?>
    </div>
    <?php endif; ?>
    <?php if ($mensajeError): ?>
    <?php $clase = (strpos($mensajeError, 'asociado') !== false) ? 'warning-box' : 'error-box'; ?>
    <div class="<?= $clase ?>">
        <i class="<?= $clase === 'warning-box' ? 'bi bi-exclamation-triangle' : 'fas fa-exclamation-triangle' ?>"></i>
        <?= htmlspecialchars($mensajeError) ?>
    </div>
    <?php endif; ?>

    <div class="filter-panel">
        <i class="bi bi-search filter-icon"></i>
        <form action="<?= $basePath ?>" method="GET" class="formulario-busqueda" style="display:flex; flex:1; gap:10px; align-items:center;">
            <input type="hidden" name="module" value="productos">
            <input type="hidden" name="action" value="lista">
            <input type="text" name="busqueda" class="filter-input" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar producto..." maxlength="60">
            <button type="submit" style="display:none;"></button>
            <?php if ($busqueda): ?>
            <a href="<?= $basePath ?>?module=productos&action=lista" class="boton-limpiar" style="white-space: nowrap;">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="grid-cards">
        <?php foreach ($productos as $p): ?>
        <div class="card-item product-card">
            <?php if (!empty(trim($p['foto']))): ?>
                <img src="<?= $basePath ?>uploads/<?= htmlspecialchars(trim($p['foto'])) ?>" 
                     class="product-img" alt="Foto" 
                     onerror="this.onerror=null; this.outerHTML='<div class=\'product-icon\'><i class=\'bi bi-image\'></i></div>';">
            <?php else: ?>
                <div class="product-icon"><i class="bi bi-box-seam"></i></div>
            <?php endif; ?>
            <div class="product-price">$<?= number_format($p['precio'], 0, '', '.') ?></div>
            <div class="card-title"><?= htmlspecialchars($p['titulo']) ?></div>
            <div class="card-subtitle">
                <i class="bi bi-boxes text-gold"></i> Stock: <?= intval($p['cantidad']) ?>
            </div>
            <div class="card-actions">
                <a href="<?= $basePath ?>?module=productos&action=editar&id=<?= intval($p['id']) ?>" class="boton-editar">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="<?= $basePath ?>?module=productos&action=eliminar&id=<?= intval($p['id']) ?>"
                   class="boton-eliminar"
                   data-confirm="¿Está seguro de eliminar este producto?">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($productos)): ?>
        <div class="empty-state-card" style="grid-column: 1 / -1;">
            <i class="bi bi-search"></i>
            <p>No se encontraron productos.</p>
        </div>
        <?php endif; ?>
    </div>

    <?php include dirname(__DIR__) . '/partials/paginacion.php'; ?>
    <?php if ($total > 0): ?>
    <p class="pag-info">Mostrando página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $total ?> productos)</p>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
