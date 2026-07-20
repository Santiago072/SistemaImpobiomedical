<?php
/**
 * Vista: Catálogo de Productos — lista + modal editar en 1 vista
 * Variables: $productos, $busqueda, $paginaActual, $totalPaginas, $total, $mensajeExito, $mensajeError
 */
$pageTitle = 'Catálogo de Productos';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-box-seam-fill"></i> Catálogo de Productos</h1>
                <p class="mod-sub"><?= $total ?? 0 ?> producto(s) en catálogo</p>
            </div>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <button class="btn-mod-primary" onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </button>
            <?php endif; ?>
        </div>

        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensajeError)): ?>
        <?php $clase = (strpos($mensajeError, 'asociado') !== false) ? 'mod-alert-warn' : 'mod-alert-err'; ?>
        <div class="mod-alert <?= $clase ?>"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- Filtros por Categoría -->
        <?php if (!empty($categoriasCount)): ?>
        <div style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:8px;">
            <a href="<?= $basePath ?>?module=productos&action=lista" class="mod-btn-category <?= empty($categoriaSel) ? 'active' : '' ?>">
                Todos
            </a>
            <?php foreach ($categoriasCount as $cat): ?>
            <a href="<?= $basePath ?>?module=productos&action=lista&categoria=<?= urlencode($cat['categoria']) ?>" 
               class="mod-btn-category <?= ($categoriaSel === $cat['categoria']) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['categoria']) ?> <span class="cat-count">(<?= $cat['cantidad'] ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Búsqueda -->
        <div class="mod-search-bar">
            <form action="<?= $basePath ?>" method="GET" class="mod-search-form">
                <input type="hidden" name="module" value="productos">
                <input type="hidden" name="action" value="lista">
                <?php if (!empty($categoriaSel)): ?>
                <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaSel) ?>">
                <?php endif; ?>
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" name="busqueda" class="mod-search-input" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar producto...">
                <?php if ($busqueda): ?>
                <a href="<?= $basePath ?>?module=productos&action=lista<?= !empty($categoriaSel) ? '&categoria='.urlencode($categoriaSel) : '' ?>" class="mod-btn-clear"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Cuadrícula de productos -->
        <div class="prod-grid">
            <?php if (empty($productos)): ?>
            <div class="mod-empty-card">
                <i class="bi bi-box-seam"></i>
                <p>No se encontraron productos.</p>
            </div>
            <?php else: ?>
            <?php foreach ($productos as $p): ?>
            <div class="prod-card">
                <?php if (!empty(trim($p['foto']))): ?>
                <img src="<?= $basePath ?>uploads/<?= htmlspecialchars(trim($p['foto'])) ?>"
                     class="prod-img" alt="<?= htmlspecialchars($p['titulo']) ?>"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="prod-icon-fallback" style="display:none;"><i class="bi bi-box-seam"></i></div>
                <?php else: ?>
                <div class="prod-icon-fallback"><i class="bi bi-box-seam"></i></div>
                <?php endif; ?>

                <div class="prod-body">
                    <div class="prod-price">$<?= number_format($p['precio'], 0, ',', '.') ?></div>
                    <div class="prod-name"><?= htmlspecialchars($p['titulo']) ?></div>
                    <div class="prod-meta" style="margin-bottom:8px;">
                        <?php if(!empty($p['codigo_producto'])): ?><span class="prod-tag" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-upc-scan"></i> <?= htmlspecialchars($p['codigo_producto']) ?></span><?php endif; ?>
                        <?php if(!empty($p['categoria'])): ?><span class="prod-tag" style="background:#f3e8ff;color:#7e22ce;"><i class="bi bi-tag-fill"></i> <?= htmlspecialchars($p['categoria']) ?></span><?php endif; ?>
                    </div>
                    <div class="prod-meta">
                        <span class="prod-tag"><i class="bi bi-boxes"></i> Stock: <?= intval($p['cantidad']) ?></span>
                        <span class="prod-tag <?= $p['iva'] === 'si' ? 'tag-iva' : 'tag-noiva' ?>">
                            IVA: <?= $p['iva'] === 'si' ? 'Sí' : 'No' ?>
                        </span>
                        <?php if ($p['estado'] !== 'activo'): ?>
                            <span class="prod-tag" style="background:#fee2e2;color:#991b1b;"><i class="bi bi-x-circle-fill"></i> Inactivo</span>
                        <?php endif; ?>
                    </div>
                    <div class="prod-actions">
                        <button class="mod-btn-edit" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="mod-btn-del" onclick="confirmarEliminar(<?= intval($p['id']) ?>, '<?= htmlspecialchars(addslashes($p['titulo'])) ?>')" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php 
        $pagBaseUrl = $basePath . '?module=productos&action=lista' 
                    . (!empty($categoriaSel) ? '&categoria=' . urlencode($categoriaSel) : '')
                    . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
        include __DIR__ . '/../layout/paginacion.php'; 
        ?>
        <?php if (($total ?? 0) > 0): ?>
        <p class="mod-pag-info">Página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $total ?> productos)</p>
        <?php endif; ?>

    </main>
</div>

<!-- MODAL: Crear Producto -->
<div id="modal-crear" class="imo-modal-bg" onclick="cerrarModal('modal-crear', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-box-seam-fill"></i> Nuevo Producto</h3>
            <button onclick="cerrarModal('modal-crear')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" action="<?= $basePath ?>?module=productos&action=crear" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <div class="prod-edit-layout">
                <div class="prod-edit-left">
                    <div class="imo-form-row">
                        <div class="imo-form-group">
                            <label>Código del Producto</label>
                            <input type="text" name="codigo_producto" maxlength="60" placeholder="Ej: PROD-001">
                        </div>
                        <div class="imo-form-group">
                            <label>Categoría</label>
                            <select name="categoria">
                                <option value="">-- Seleccionar categoría --</option>
                                <option value="Insumo Medico Quirurgico">Insumo Médico Quirúrgico</option>
                                <option value="Insumo Medico Odontologico">Insumo Médico Odontológico</option>
                                <option value="Mobiliario Hospitalario">Mobiliario Hospitalario</option>
                                <option value="Equipo Medico">Equipo Médico</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Repuestos">Repuestos</option>
                                <option value="Equipo de Terapia">Equipo de Terapia</option>
                                <option value="Medicamentos">Medicamentos</option>
                            </select>
                        </div>
                    </div>
                    <div class="imo-form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" name="titulo" required maxlength="60">
                    </div>
                    <div class="imo-form-group">
                        <label>Descripción *</label>
                        <textarea name="descripcion" required maxlength="5000" rows="3" style="padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;resize:vertical;outline:none;"></textarea>
                    </div>
                    <div class="imo-form-group">
                        <label>¿Aplica IVA? *</label>
                        <select name="iva" required>
                            <option value="si">Sí — Aplicar IVA</option>
                            <option value="no">No — Sin IVA</option>
                        </select>
                        <input type="hidden" name="precio" value="0">
                    </div>
                </div>
                <div class="prod-edit-right">
                    <label style="font-size:12px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.4px;">Imagen del Producto</label>
                    <div id="c_prod-img-preview" class="prod-preview-box">
                        <i class="bi bi-card-image"></i>
                        <span>Sin imagen</span>
                    </div>
                    <input type="file" name="foto" id="c_foto" accept="image/*" style="margin-top:8px;font-size:13px;">
                    <small style="color:#9ca3af;font-size:12px;">Máx: 5MB · JPG, PNG, WebP</small>
                </div>
            </div>

            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-crear')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-plus-lg"></i> Crear Producto</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Editar Producto -->
<div id="modal-editar" class="imo-modal-bg" onclick="cerrarModal('modal-editar', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-pencil-square"></i> Editar Producto</h3>
            <button onclick="cerrarModal('modal-editar')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="form-editar" action="" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" id="e_id" name="id" value="">
            <input type="hidden" id="e_foto_actual" name="foto_actual" value="">

            <div class="prod-edit-layout">
                <div class="prod-edit-left">
                    <div class="imo-form-row">
                        <div class="imo-form-group">
                            <label>Código del Producto</label>
                            <input type="text" id="e_codigo_producto" name="codigo_producto" maxlength="60" placeholder="Ej: PROD-001">
                        </div>
                        <div class="imo-form-group">
                            <label>Categoría</label>
                            <select id="e_categoria" name="categoria">
                                <option value="">-- Seleccionar categoría --</option>
                                <option value="Insumo Medico Quirurgico">Insumo Médico Quirúrgico</option>
                                <option value="Insumo Medico Odontologico">Insumo Médico Odontológico</option>
                                <option value="Mobiliario Hospitalario">Mobiliario Hospitalario</option>
                                <option value="Equipo Medico">Equipo Médico</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Repuestos">Repuestos</option>
                                <option value="Equipo de Terapia">Equipo de Terapia</option>
                                <option value="Medicamentos">Medicamentos</option>
                            </select>
                        </div>
                    </div>
                    <div class="imo-form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" id="e_titulo" name="titulo" required maxlength="60">
                    </div>
                    <div class="imo-form-group">
                        <label>Descripción *</label>
                        <textarea id="e_descripcion" name="descripcion" required maxlength="5000" rows="3" style="padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;resize:vertical;outline:none;"></textarea>
                    </div>
                    <div class="imo-form-row">
                        <div class="imo-form-group">
                            <label>¿Aplica IVA? *</label>
                            <select id="e_iva" name="iva" required>
                                <option value="si">Sí — Aplicar IVA</option>
                                <option value="no">No — Sin IVA</option>
                            </select>
                            <input type="hidden" id="e_precio" name="precio" value="0">
                        </div>
                        <div class="imo-form-group">
                            <label>Estado *</label>
                            <select id="e_estado" name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="prod-edit-right">
                    <label style="font-size:12px;font-weight:600;color:#374151;text-transform:uppercase;letter-spacing:.4px;">Imagen del Producto</label>
                    <div id="prod-img-preview" class="prod-preview-box">
                        <i class="bi bi-card-image"></i>
                        <span>Sin imagen</span>
                    </div>
                    <input type="file" name="foto" id="e_foto" accept="image/*" style="margin-top:8px;font-size:13px;">
                    <small style="color:#9ca3af;font-size:12px;">Máx: 5MB · JPG, PNG, WebP</small>
                </div>
            </div>

            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Eliminar -->
<div id="modal-eliminar" class="imo-modal-bg" onclick="cerrarModal('modal-eliminar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3><i class="bi bi-exclamation-triangle-fill"></i> Eliminar Producto</h3>
            <button onclick="cerrarModal('modal-eliminar')" class="imo-modal-close">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p style="color:#4b5563;">¿Seguro que deseas eliminar <strong id="nombre-eliminar"></strong>?</p>
        </div>
        <div class="imo-modal-footer" style="display:flex; justify-content:flex-end; gap:12px;">
            <button class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <button id="btn-eliminar" class="imo-btn-danger" onclick="ejecutarEliminar()" style="display:inline-flex; align-items:center; justify-content:center; gap:6px; height:38px; padding:0 16px; border:none; border-radius:6px; cursor:pointer;"><i class="bi bi-trash-fill"></i> Eliminar</button>
        </div>
    </div>
</div>

<style>
.mod-alert-warn{background:#fef9c3;color:#854d0e;border:1px solid #fde68a;}
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;}
.prod-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;transition:transform .2s,box-shadow .2s;}
.prod-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.07);}
.prod-img{width:100%;height:160px;object-fit:contain;background:#fff;padding:10px;}
.prod-icon-fallback{width:100%;height:160px;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-size:48px;color:#cbd5e1;}
.prod-body{padding:16px;}
.prod-price{font-size:20px;font-weight:800;color:#10757e;margin-bottom:4px;}
.prod-name{font-size:14px;font-weight:600;color:#0f172a;margin-bottom:8px;line-height:1.4;}
.prod-meta{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;}
.prod-tag{font-size:11px;padding:3px 9px;border-radius:12px;background:#f1f5f9;color:#475569;font-weight:600;}
.tag-iva{background:#dcfce7;color:#166534;}.tag-noiva{background:#fee2e2;color:#991b1b;}
.prod-actions{display:flex;gap:6px;}
.prod-edit-layout{display:grid;grid-template-columns:1fr 200px;gap:20px;}
.prod-edit-left{display:flex;flex-direction:column;}
.prod-edit-right{display:flex;flex-direction:column;gap:8px;}
.prod-preview-box{width:100%;aspect-ratio:1;background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94a3b8;font-size:32px;overflow:hidden;}
.prod-preview-box img{width:100%;height:100%;object-fit:cover;}
.prod-preview-box span{font-size:12px;margin-top:6px;}
</style>

<script>
const BASE = '<?= $basePath ?>';

function abrirModalCrear() {
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(p) {
    document.getElementById('e_id').value          = p.id;
    document.getElementById('e_titulo').value      = p.titulo || '';
    document.getElementById('e_descripcion').value = p.descripcion || '';
    if (document.getElementById('e_precio')) document.getElementById('e_precio').value = p.precio || 0;
    document.getElementById('e_iva').value         = p.iva || 'no';
    document.getElementById('e_estado').value      = p.estado || 'activo';
    document.getElementById('e_foto_actual').value = p.foto || '';
    document.getElementById('e_categoria').value   = p.categoria || '';
    document.getElementById('e_codigo_producto').value = p.codigo_producto || '';

    const preview = document.getElementById('prod-img-preview');
    if (p.foto) {
        preview.innerHTML = `<img src="${BASE}uploads/${p.foto}" style="width:100%;height:100%;object-fit:cover;">`;
    } else {
        preview.innerHTML = `<i class="bi bi-card-image"></i><span>Sin imagen</span>`;
    }

    document.getElementById('form-editar').action = BASE + '?module=productos&action=editar&id=' + p.id;
    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

document.getElementById('c_foto')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('c_prod-img-preview').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
});

document.getElementById('e_foto')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('prod-img-preview').innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
});

let urlEliminar = '';
function confirmarEliminar(id, nombre) {
    document.getElementById('nombre-eliminar').textContent = nombre;
    urlEliminar = BASE + '?module=productos&action=eliminar&id=' + id;
    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function ejecutarEliminar() {
    if (urlEliminar) window.location.href = urlEliminar;
}

function cerrarModal(id, evento) {
    if (evento && evento.target !== document.getElementById(id)) return;
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['modal-crear','modal-editar','modal-eliminar'].forEach(id => {
            document.getElementById(id)?.classList.remove('open');
        });
        document.body.style.overflow = 'auto';
    }
});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
