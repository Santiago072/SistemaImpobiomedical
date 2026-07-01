<?php
/**
 * Vista: Editar usuario
 * Variables: $usuario, $mensajeError, $csrf_token
 */
$pageTitle = 'Editar Usuario';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Editar Usuario';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Editar Usuario</h1></div>

    <?php if ($mensajeError): ?>
    <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>

    <div class="formulario-contenedor">
        <form method="POST" action="<?= $basePath ?>?module=usuarios&action=editar&id=<?= intval($usuario['id']) ?>" class="formulario">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" value="<?= intval($usuario['id']) ?>">

            <div class="form-grid-2">
                <div class="grupo-campo">
                    <label for="documento"><i class="bi bi-person-badge"></i> Documento *</label>
                    <input type="text" id="documento" name="documento" required maxlength="15"
                           value="<?= htmlspecialchars($usuario['documento']) ?>">
                </div>
                <div class="grupo-campo">
                    <label for="nombre"><i class="bi bi-person"></i> Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="60"
                           value="<?= htmlspecialchars($usuario['nombre']) ?>">
                </div>
                <div class="grupo-campo">
                    <label for="correo"><i class="bi bi-envelope"></i> Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required maxlength="60"
                           value="<?= htmlspecialchars($usuario['correo']) ?>">
                </div>
                <div class="grupo-campo">
                    <label for="telefono"><i class="bi bi-telephone"></i> Teléfono *</label>
                    <input type="text" id="telefono" name="telefono" required maxlength="15"
                           value="<?= htmlspecialchars($usuario['telefono']) ?>">
                </div>
                <div class="grupo-campo">
                    <label for="rol"><i class="bi bi-shield"></i> Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un Rol</option>
                        <option value="admin"   <?= $usuario['rol'] === 'admin'   ? 'selected' : '' ?>>Administrador</option>
                        <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                    </select>
                </div>
                <div class="grupo-campo">
                    <label for="estado"><i class="bi bi-toggle-on"></i> Estado *</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione un Estado</option>
                        <option value="activo"   <?= $usuario['estado'] === 'activo'   ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <div class="grupo-campo">
                    <label for="nueva_password"><i class="bi bi-key"></i> Nueva Contraseña</label>
                    <input type="password" id="nueva_password" name="nueva_password" minlength="6" maxlength="30">
                    <small class="text-muted">Dejar en blanco para no cambiar</small>
                </div>
            </div>

            <div class="grupo-campo mt-30 text-center">
                <button type="submit" class="boton-primario"><i class="fas fa-save"></i> Actualizar Usuario</button>
                <a href="<?= $basePath ?>?module=usuarios&action=lista" class="boton-limpiar"><i class="bi bi-x"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
