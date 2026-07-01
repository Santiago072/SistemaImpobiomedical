<?php
/**
 * Vista: Crear usuario
 * Variables: $mensajeError, $csrf_token
 */
$pageTitle = 'Nuevo Usuario';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Crear Nuevo Usuario';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Crear Nuevo Usuario</h1></div>

    <?php if ($mensajeError): ?>
    <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>

    <div class="formulario-contenedor">
        <form method="POST" action="<?= $basePath ?>?module=usuarios&action=crear" class="formulario">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-grid-2">
                <div class="grupo-campo">
                    <label for="documento"><i class="bi bi-person-badge"></i> Documento *</label>
                    <input type="text" id="documento" name="documento" required maxlength="15">
                </div>
                <div class="grupo-campo">
                    <label for="nombre"><i class="bi bi-person"></i> Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="60">
                </div>
                <div class="grupo-campo">
                    <label for="correo"><i class="bi bi-envelope"></i> Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" required maxlength="60">
                </div>
                <div class="grupo-campo">
                    <label for="telefono"><i class="bi bi-telephone"></i> Teléfono *</label>
                    <input type="text" id="telefono" name="telefono" required maxlength="15">
                </div>
                <div class="grupo-campo">
                    <label for="rol"><i class="bi bi-shield"></i> Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un Rol</option>
                        <option value="admin">Administrador</option>
                        <option value="usuario">Usuario</option>
                    </select>
                </div>
                <div class="grupo-campo">
                    <label for="password"><i class="bi bi-key"></i> Contraseña (opcional)</label>
                    <input type="password" id="password" name="password" minlength="6" maxlength="30">
                    <small class="text-muted">Si no se proporciona, se usará el documento como contraseña temporal</small>
                </div>
            </div>

            <div class="grupo-campo mt-30 text-center">
                <button type="submit" class="boton-primario"><i class="bi bi-person-plus"></i> Crear Usuario</button>
                <a href="<?= $basePath ?>?module=usuarios&action=lista" class="boton-limpiar"><i class="bi bi-x"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
