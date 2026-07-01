<?php
/**
 * Vista: Crear/Editar Cliente
 * Variables (Crear): $mensajeError, $csrf_token
 * Variables (Editar): $cliente, $mensajeError, $csrf_token
 */
$esEditar = isset($cliente);
$pageTitle = $esEditar ? 'Editar Cliente' : 'Nuevo Cliente';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="page-header">
            <h1 class="page-title">
                <i class="bi <?= $esEditar ? 'bi-pencil-square' : 'bi-person-plus-fill' ?>"></i>
                <?= $pageTitle ?>
            </h1>
            <a href="<?= $basePath ?>?module=clientes" class="btn btn-outline">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <?php if (!empty($mensajeError)): ?>
        <div class="alerta alerta-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="card-panel" style="max-width:800px; margin:0 auto;">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre / Entidad *</label>
                        <input type="text" name="nombre" class="input-field" required maxlength="100"
                               value="<?= htmlspecialchars($cliente['nombre'] ?? ($_POST['nombre'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label>NIT / CC *</label>
                        <input type="text" name="nit" class="input-field" required maxlength="25"
                               value="<?= htmlspecialchars($cliente['nit'] ?? ($_POST['nit'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Departamento *</label>
                        <input type="text" name="departamento" class="input-field" required maxlength="60"
                               value="<?= htmlspecialchars($cliente['departamento'] ?? ($_POST['departamento'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Municipio *</label>
                        <input type="text" name="municipio" class="input-field" required maxlength="60"
                               value="<?= htmlspecialchars($cliente['municipio'] ?? ($_POST['municipio'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Dirección *</label>
                    <input type="text" name="direccion" class="input-field" required maxlength="100"
                           value="<?= htmlspecialchars($cliente['direccion'] ?? ($_POST['direccion'] ?? '')) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Contacto *</label>
                        <input type="text" name="nombre_contacto" class="input-field" required maxlength="60"
                               value="<?= htmlspecialchars($cliente['nombre_contacto'] ?? ($_POST['nombre_contacto'] ?? '')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="text" name="telefono" class="input-field" required maxlength="20"
                               value="<?= htmlspecialchars($cliente['telefono'] ?? ($_POST['telefono'] ?? '')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" class="input-field" maxlength="100"
                               value="<?= htmlspecialchars($cliente['correo'] ?? ($_POST['correo'] ?? '')) ?>">
                    </div>
                    <?php if ($esEditar): ?>
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="input-field">
                            <?php
                            $estado = $cliente['estado'] ?? ($_POST['estado'] ?? 'activo');
                            ?>
                            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="btn-group-form">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save-fill"></i> Guardar Cliente
                    </button>
                    <a href="<?= $basePath ?>?module=clientes" class="btn btn-outline">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
