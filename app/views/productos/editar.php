<?php
/**
 * Vista: Editar producto
 * Variables: $producto, $mensajeError, $csrf_token
 */
$pageTitle = 'Editar Producto';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="contenido-principal">
    <?php $pageHeading = 'Editar Producto';
    include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <div class="encabezado-pagina"><h1>Editar Producto</h1></div>

    <?php if ($mensajeError): ?>
    <div class="error-box"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($mensajeError) ?></div>
    <?php endif; ?>

    <div class="formulario-contenedor">
        <form method="POST" enctype="multipart/form-data"
              action="<?= $basePath ?>?module=productos&action=editar&id=<?= intval($producto['id']) ?>"
              class="formulario">
            <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id"          value="<?= intval($producto['id']) ?>">
            <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($producto['foto']) ?>">

            <div class="form-split-layout">
                <div class="form-split-left">
                    <div class="grupo-campo">
                        <label for="titulo"><i class="bi bi-tag"></i> Nombre del Producto *</label>
                        <input type="text" id="titulo" name="titulo" required maxlength="60"
                               value="<?= htmlspecialchars($producto['titulo']) ?>">
                    </div>
                    <div class="grupo-campo">
                        <label for="descripcion"><i class="bi bi-card-text"></i> Descripción *</label>
                        <textarea id="descripcion" name="descripcion" required maxlength="500"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>
                    <div class="form-grid-2">
                        <div class="grupo-campo">
                            <label for="cantidad"><i class="bi bi-box"></i> Cantidad *</label>
                            <input type="number" id="cantidad" name="cantidad" required min="0" max="99"
                                   value="<?= intval($producto['cantidad']) ?>">
                        </div>
                        <div class="grupo-campo">
                            <label for="precio"><i class="bi bi-currency-dollar"></i> Precio Unitario *</label>
                            <input type="number" id="precio" name="precio" required min="0" max="9999999" step="0.01"
                                   value="<?= floatval($producto['precio']) ?>">
                        </div>
                    </div>
                    <div class="grupo-campo">
                        <label for="iva"><i class="bi bi-percent"></i> Valor con IVA *</label>
                        <select id="iva" name="iva" required>
                            <option value="">Seleccione una Opción</option>
                            <option value="si" <?= $producto['iva'] === 'si' ? 'selected' : '' ?>>Aplicar IVA</option>
                            <option value="no" <?= $producto['iva'] === 'no' ? 'selected' : '' ?>>No Aplicar IVA</option>
                        </select>
                    </div>
                </div>

                <div class="form-split-right">
                    <label><i class="bi bi-image"></i> Foto Actual</label>
                    <?php if (!empty($producto['foto'])): ?>
                    <div class="mb-10 text-center mt-10">
                        <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($producto['foto']) ?>" 
                             class="img-preview" style="width:100%; max-width:180px;">
                    </div>
                    <?php else: ?>
                    <div class="product-icon text-muted mt-10" style="height:120px; font-size:40px;">
                        <i class="bi bi-card-image"></i>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grupo-campo text-left mt-30">
                        <label>Cambiar Foto</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg, image/png, image/webp, image/gif">
                        <small class="text-muted">Max: 5MB</small>
                    </div>
                </div>
            </div>
            <div class="grupo-campo mt-30 text-center">
                <button type="submit" class="boton-primario"><i class="fas fa-save"></i> Guardar Producto</button>
                <a href="<?= $basePath ?>?module=productos&action=lista" class="boton-limpiar"><i class="bi bi-x"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
