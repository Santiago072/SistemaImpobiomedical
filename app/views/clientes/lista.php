<?php
/**
 * Vista: Lista de Clientes — Sistema Impobiomedical
 * Variables: $clientes, $busqueda, $paginaActual, $totalPaginas, $total, $mensajeExito, $mensajeError
 */
$pageTitle = 'Gestión de Clientes';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-building"></i> Gestión de Clientes</h1>
            <a href="<?= $basePath ?>?module=clientes&action=crear" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Cliente
            </a>
        </div>

        <?php if (!empty($mensajeExito)): ?>
        <div class="alerta alerta-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensajeError)): ?>
        <div class="alerta alerta-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="card-panel">
            <div class="table-toolbar">
                <form method="GET" action="<?= $basePath ?>index.php" class="search-form">
                    <input type="hidden" name="module" value="clientes">
                    <div class="search-group">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="busqueda" placeholder="Buscar por nombre, NIT o municipio..."
                               value="<?= htmlspecialchars($busqueda) ?>" class="input-search">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        <?php if ($busqueda !== ''): ?>
                        <a href="<?= $basePath ?>?module=clientes" class="btn btn-outline" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="tabla-responsive">
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>Nombre/Entidad</th>
                            <th>NIT / CC</th>
                            <th>Ubicación</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding:40px; color:rgba(255,255,255,.4);">
                                <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                                No se encontraron clientes
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($c['nit']) ?></td>
                            <td><?= htmlspecialchars($c['municipio'] . ', ' . $c['departamento']) ?></td>
                            <td><?= htmlspecialchars($c['nombre_contacto']) ?></td>
                            <td><?= htmlspecialchars($c['telefono']) ?></td>
                            <td>
                                <div class="acciones-celda">
                                    <a href="<?= $basePath ?>?module=clientes&action=editar&id=<?= $c['id'] ?>"
                                       class="btn-icon btn-edit" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <button onclick="confirmarEliminar(<?= $c['id'] ?>)" class="btn-icon btn-delete" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPaginas > 1): ?>
            <div class="paginacion">
                <?php
                $urlBusq = $busqueda !== '' ? '&busqueda=' . urlencode($busqueda) : '';
                for ($i = 1; $i <= $totalPaginas; $i++):
                    $clase = $i === $paginaActual ? 'active' : '';
                ?>
                <a href="<?= $basePath ?>?module=clientes&pagina=<?= $i . $urlBusq ?>" class="page-link <?= $clase ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Está seguro de eliminar este cliente?')) {
        window.location.href = '<?= $basePath ?>?module=clientes&action=eliminar&id=' + id;
    }
}
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
