<?php
/**
 * Vista: Editar ítem de cotización
 * Variables: $datos, $mensajeError, $csrf_token
 */
$pageTitle = 'Editar Ítem';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Editar Ítem de Cotización';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Editar Ítem de Cotización</h1></div>

    <?php if ($mensajeError): ?>
    <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>

    <div class="formulario-contenedor">
        <form method="POST"
              action="<?= $basePath ?>?module=cotizaciones&action=editar_item&id=<?= intval($datos['id']) ?>"
              enctype="multipart/form-data" class="formulario">
            <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="item_id"     value="<?= intval($datos['id']) ?>">
            <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($datos['foto']) ?>">

            <div class="grupo-campo">
                <label>Nombre del Producto *</label>
                <input type="text" name="titulo"
                       value="<?= htmlspecialchars($datos['titulo']) ?>" required maxlength="100">
            </div>
            <div class="grupo-campo">
                <label>Foto Actual</label>
                <?php if (!empty($datos['foto'])): ?>
                <div class="mb-10">
                    <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($datos['foto']) ?>" 
                         width="100" class="img-preview">
                </div>
                <?php endif; ?>
                <input type="file" id="foto" name="foto" accept="image/jpeg, image/png, image/webp, image/gif">
                <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Máx: 5MB</small>
            </div>
            <div class="grupo-campo">
                <label>Descripción *</label>
                <textarea name="descripcion" required maxlength="1000"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
            </div>
            <div class="grupo-campo">
                <label>Cantidad *</label>
                <input type="number" name="cantidad"
                       value="<?= intval($datos['cantidad']) ?>" required min="1">
            </div>
            <div class="grupo-campo">
                <label>Valor con IVA *</label>
                <select name="IVA" required>
                    <option value="si" <?= $datos['iva'] === 'si' ? 'selected' : '' ?>>Aplicar IVA</option>
                    <option value="no" <?= $datos['iva'] === 'no' ? 'selected' : '' ?>>No Aplicar IVA</option>
                </select>
            </div>
            <div class="grupo-campo">
                <label>Precio Unitario *</label>
                <input type="number" name="precio"
                       value="<?= floatval($datos['precio']) ?>" required min="0" step="0.01">
            </div>
            <div class="grupo-campo">
                <button type="submit" class="boton-primario">Actualizar Ítem</button>
                <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="boton-secundario">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
