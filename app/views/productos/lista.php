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
            <div class="header-actions-wrap">
                <?php 
                $exportUrl = $basePath . '?module=productos'
                           . (!empty($categoriaSel) ? '&categoria=' . urlencode($categoriaSel) : '')
                           . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
                ?>
                <a href="<?= $exportUrl ?>&action=exportarPdf" download="Catalogo_Productos.pdf" class="btn-mod-primary btn-pdf-export">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <button class="btn-mod-primary" onclick="abrirModalCrear()">
                    <i class="bi bi-plus-lg"></i> Nuevo Producto
                </button>
            </div>
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
        <div class="categories-filter-bar">
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
                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('form-hidden-action');">
                <div class="prod-icon-fallback form-hidden-action"><i class="bi bi-box-seam"></i></div>
                <?php else: ?>
                <div class="prod-icon-fallback"><i class="bi bi-box-seam"></i></div>
                <?php endif; ?>

                <div class="prod-body">
                    <div class="prod-name"><?= htmlspecialchars($p['titulo']) ?></div>
                    <div class="prod-meta prod-meta-spacing">
                        <?php if(!empty($p['codigo_producto'])): ?><span class="prod-tag tag-code"><i class="bi bi-upc-scan"></i> <?= htmlspecialchars($p['codigo_producto']) ?></span><?php endif; ?>
                    </div>
                    <div class="prod-meta">
                        <span class="prod-tag <?= $p['iva'] === 'si' ? 'tag-iva' : 'tag-noiva' ?>">
                            IVA: <?= $p['iva'] === 'si' ? 'Sí' : 'No' ?>
                        </span>
                        <?php if ($p['estado'] !== 'activo'): ?>
                            <span class="prod-tag tag-inactive"><i class="bi bi-x-circle-fill"></i> Inactivo</span>
                        <?php endif; ?>
                    </div>
                    <div class="prod-actions">
                        <button class="mod-btn-edit" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p)) ?>)" title="Editar">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                        <button class="mod-btn-del" onclick="confirmarEliminar(<?= intval($p['id']) ?>, '<?= htmlspecialchars(addslashes($p['titulo'])) ?>')" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        <?php endif; ?>
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
                                <option value="Servicio Calibracion">Servicio Calibración</option>
                            </select>
                        </div>
                    </div>
                    <div class="imo-form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" name="titulo" required maxlength="60">
                    </div>
                    <div class="imo-form-group">
                        <label>Descripción *</label>
                        <textarea name="descripcion" required maxlength="5000" rows="3"></textarea>
                    </div>
                    <div class="imo-form-group">
                        <label>¿Aplica IVA? *</label>
                        <select name="iva" required>
                            <option value="si">Sí — Aplicar IVA</option>
                            <option value="no">No — Sin IVA</option>
                        </select>
                    </div>
                </div>
                <div class="prod-edit-right">
                    <label class="prod-label-upload">Imagen del Producto</label>
                    <div id="c_prod-img-preview" class="prod-preview-box">
                        <i class="bi bi-card-image"></i>
                        <span>Sin imagen</span>
                    </div>
                    <input type="file" name="foto" id="c_foto" accept="image/*" class="prod-file-input">
                    <small class="prod-file-hint">Máx: 5MB · JPG, PNG, WebP</small>
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
                                <option value="Servicio Calibracion">Servicio Calibración</option>
                            </select>
                        </div>
                    </div>
                    <div class="imo-form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" id="e_titulo" name="titulo" required maxlength="60">
                    </div>
                    <div class="imo-form-group">
                        <label>Descripción *</label>
                        <textarea id="e_descripcion" name="descripcion" required maxlength="5000" rows="3"></textarea>
                    </div>
                    <div class="imo-form-row">
                        <div class="imo-form-group">
                            <label>¿Aplica IVA? *</label>
                            <select id="e_iva" name="iva" required>
                                <option value="si">Sí — Aplicar IVA</option>
                                <option value="no">No — Sin IVA</option>
                            </select>
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
                    <label class="prod-label-upload">Imagen del Producto</label>
                    <div id="prod-img-preview" class="prod-preview-box">
                        <i class="bi bi-card-image"></i>
                        <span>Sin imagen</span>
                    </div>
                    <input type="file" name="foto" id="e_foto" accept="image/*" class="prod-file-input">
                    <small class="prod-file-hint">Máx: 5MB · JPG, PNG, WebP</small>
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
            <p class="imo-modal-desc">¿Seguro que deseas eliminar <strong id="nombre-eliminar"></strong>?</p>
        </div>
        <div class="imo-modal-footer">
            <button class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <form id="form-eliminar-producto" method="POST" action="" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
const BASE  = '<?= $basePath ?>';
const CSRF  = '<?= htmlspecialchars($csrf_token ?? '') ?>';

function abrirModalCrear() {
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(p) {
    document.getElementById('e_id').value          = p.id;
    document.getElementById('e_titulo').value      = p.titulo || '';
    document.getElementById('e_descripcion').value = p.descripcion || '';
    document.getElementById('e_iva').value         = p.iva || 'no';
    document.getElementById('e_estado').value      = p.estado || 'activo';
    document.getElementById('e_foto_actual').value = p.foto || '';
    document.getElementById('e_categoria').value   = p.categoria || '';
    document.getElementById('e_codigo_producto').value = p.codigo_producto || '';

    const preview = document.getElementById('prod-img-preview');
    if (p.foto) {
        preview.innerHTML = `<img src="${BASE}uploads/${p.foto}" class="img-full-cover">`;
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
        document.getElementById('c_prod-img-preview').innerHTML = `<img src="${e.target.result}" class="img-full-cover">`;
    };
    reader.readAsDataURL(file);
});

document.getElementById('e_foto')?.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('prod-img-preview').innerHTML = `<img src="${e.target.result}" class="img-full-cover">`;
    };
    reader.readAsDataURL(file);
});

function confirmarEliminar(id, nombre) {
    document.getElementById('nombre-eliminar').textContent = nombre;
    document.getElementById('form-eliminar-producto').action = BASE + '?module=productos&action=eliminar&id=' + id;
    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id, evento) {
    if (evento && evento.target !== document.getElementById(id)) return;
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = 'auto';
}

let timerFiltro;
const inputBusq   = document.querySelector('.mod-search-input');
const prodGrid    = document.querySelector('.prod-grid');
const paginacion  = document.querySelector('.mod-pag-info') ? document.querySelector('.mod-pag-info').parentElement.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]') : null;
const pagInfo     = document.querySelector('.mod-pag-info');
const catActual   = '<?= addslashes($categoriaSel ?? '') ?>';
const htmlOriginalGrid = prodGrid ? prodGrid.innerHTML : '';

function renderizarProductosAjax(productos, isAdmin) {
    if (!prodGrid) return;
    if (!productos || productos.length === 0) {
        prodGrid.innerHTML = `
            <div class="mod-empty-card" style="grid-column: 1 / -1;">
                <i class="bi bi-search"></i>
                <p>No se encontraron productos coincidentes.</p>
            </div>
        `;
        if (pagInfo) pagInfo.textContent = '0 producto(s) encontrado(s)';
        return;
    }

    let html = '';
    productos.forEach(p => {
        const fotoTrim = (p.foto || '').trim();
        const tieneFoto = fotoTrim.length > 0;
        const fotoUrl = tieneFoto ? `${BASE}uploads/${fotoTrim}` : '';
        const tituloEsc = escapeHtml(p.titulo || '');
        const pJson = escapeHtml(JSON.stringify(p));

        html += `
        <div class="prod-card">
            ${tieneFoto ? `
                <img src="${fotoUrl}" class="prod-img" alt="${tituloEsc}" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('form-hidden-action');">
                <div class="prod-icon-fallback form-hidden-action"><i class="bi bi-box-seam"></i></div>
            ` : `
                <div class="prod-icon-fallback"><i class="bi bi-box-seam"></i></div>
            `}
            <div class="prod-body">
                <div class="prod-name">${tituloEsc}</div>
                <div class="prod-meta prod-meta-spacing">
                    ${p.codigo_producto ? `<span class="prod-tag tag-code"><i class="bi bi-upc-scan"></i> ${escapeHtml(p.codigo_producto)}</span>` : ''}
                </div>
                <div class="prod-meta">
                    <span class="prod-tag ${p.iva === 'si' ? 'tag-iva' : 'tag-noiva'}">
                        IVA: ${p.iva === 'si' ? 'Sí' : 'No'}
                    </span>
                    ${p.estado !== 'activo' ? `<span class="prod-tag tag-inactive"><i class="bi bi-x-circle-fill"></i> Inactivo</span>` : ''}
                </div>
                <div class="prod-actions">
                    <button class="mod-btn-edit" onclick="abrirModalEditar(${pJson})" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    ${isAdmin ? `
                    <button class="mod-btn-del" onclick="confirmarEliminar(${parseInt(p.id)}, '${escapeJsString(p.titulo)}')" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    ` : ''}
                </div>
            </div>
        </div>
        `;
    });

    prodGrid.innerHTML = html;
    if (pagInfo) {
        pagInfo.textContent = `Mostrando ${productos.length} producto(s) encontrado(s)`;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function escapeJsString(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

if (inputBusq) {
    inputBusq.addEventListener('input', function() {
        clearTimeout(timerFiltro);
        const q = this.value.trim();

        if (q === '') {
            // Restaurar vista original
            if (prodGrid) prodGrid.innerHTML = htmlOriginalGrid;
            const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
            if (wrapPag) wrapPag.style.display = '';
            if (pagInfo) pagInfo.style.display = '';
            return;
        }

        timerFiltro = setTimeout(() => {
            fetch(`${BASE}?module=productos&action=ajax_buscar&term=${encodeURIComponent(q)}&categoria=${encodeURIComponent(catActual)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
                        if (wrapPag) wrapPag.style.display = 'none';
                        renderizarProductosAjax(data.productos, data.isAdmin);
                    }
                })
                .catch(err => console.error('Error en búsqueda AJAX:', err));
        }, 200);
    });
}
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
