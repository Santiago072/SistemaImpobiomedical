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
                $exportCat = $basePath . '?module=productos&action=exportarPdf'
                           . (!empty($categoriaSel) ? '&categoria=' . urlencode($categoriaSel) : '')
                           . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
                ?>
                <button type="button" id="btn-pdf-header" class="btn-mod-primary btn-pdf-export"
                        onclick="exportarPdfHeader()"
                        title="Exportar PDF de productos seleccionados">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
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
            <?php foreach ($productos as $p): 
                $pid = (int)$p['id'];
            ?>
            <div class="prod-card" data-id="<?= $pid ?>" id="prod-card-<?= $pid ?>">
                <label class="prod-select-circle" title="Seleccionar para PDF">
                    <input type="checkbox" class="chk-select-prod" value="<?= $pid ?>" onchange="toggleSeleccionProducto(this)">
                </label>
                <span data-cat="<?= htmlspecialchars($p['categoria'] ?? '') ?>" style="display:none;"></span>
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

        <!-- Barra flotante para exportar selección -->
        <div id="bar-seleccion-productos" class="prod-selection-bar">
            <div class="sel-info">
                <span class="sel-badge"><span id="txt-cant-seleccionados">0</span> seleccionados</span>
                <span id="txt-cats-seleccionadas" style="font-size:11px; color:#94a3b8;"></span>
            </div>
            <form id="form-exportar-seleccionados" method="POST" action="<?= $basePath ?>?module=productos&action=exportarPdf" target="_blank" style="margin:0; display:inline;" onsubmit="return manejarExportarPdf(this)">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="ids" id="inp-ids-seleccionados" value="">
                <button type="submit" id="btn-exportar-sel-pdf" class="btn-sel-pdf">
                    <i class="bi bi-file-earmark-pdf"></i> <span id="txt-btn-exportar">Exportar PDF</span>
                </button>
            </form>
            <button type="button" class="btn-sel-clear" onclick="deseleccionarTodosProductos()">
                <i class="bi bi-x-circle"></i> Limpiar
            </button>
        </div>

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

// ── Gestión de selección de productos para exportar PDF ──────────────────────
const SS_KEY = 'prod_sel_ids';
const SS_KEY_CATS = 'prod_sel_cats';

// Restaurar selección desde sessionStorage al cargar la página
const productosSeleccionados = new Set(
    (sessionStorage.getItem(SS_KEY) || '').split(',').filter(Boolean).map(Number)
);
// Mapa id→categoria para mostrar categorías en barra
const productoCategoria = {};
document.querySelectorAll('.prod-card').forEach(card => {
    const pid = parseInt(card.dataset.id);
    const catSpan = card.querySelector('[data-cat]');
    if (catSpan) productoCategoria[pid] = catSpan.dataset.cat;
});

// Aplicar estado guardado a los checkboxes ya pintados en la página
document.querySelectorAll('.chk-select-prod').forEach(chk => {
    const pid = parseInt(chk.value);
    if (productosSeleccionados.has(pid)) {
        chk.checked = true;
        const card = chk.closest('.prod-card');
        if (card) card.classList.add('is-selected');
    }
});
actualizarBarraSeleccion();

function toggleSeleccionProducto(chk) {
    const pid = parseInt(chk.value);
    const card = document.getElementById('prod-card-' + pid) || chk.closest('.prod-card');
    // Capturar categoría del data-cat del card
    const catEl = card ? card.querySelector('[data-cat]') : null;
    if (catEl) productoCategoria[pid] = catEl.dataset.cat;

    if (chk.checked) {
        productosSeleccionados.add(pid);
        if (card) card.classList.add('is-selected');
    } else {
        productosSeleccionados.delete(pid);
        if (card) card.classList.remove('is-selected');
    }
    persistirSeleccion();
    actualizarBarraSeleccion();
}

function persistirSeleccion() {
    sessionStorage.setItem(SS_KEY, Array.from(productosSeleccionados).join(','));
}

function actualizarBarraSeleccion() {
    const bar = document.getElementById('bar-seleccion-productos');
    const txt = document.getElementById('txt-cant-seleccionados');
    const inp = document.getElementById('inp-ids-seleccionados');
    const txtCats = document.getElementById('txt-cats-seleccionadas');
    const count = productosSeleccionados.size;

    if (count > 0) {
        if (bar) bar.style.display = 'inline-flex';
        if (txt) txt.textContent = count;
        if (inp) inp.value = Array.from(productosSeleccionados).join(',');
        // Mostrar categorías de los seleccionados
        if (txtCats) {
            const cats = [...new Set(Array.from(productosSeleccionados).map(id => productoCategoria[id]).filter(Boolean))];
            txtCats.textContent = cats.length > 0 ? '· ' + cats.join(', ') : '';
        }
    } else {
        if (bar) bar.style.display = 'none';
        if (inp) inp.value = '';
        if (txtCats) txtCats.textContent = '';
    }
}

function deseleccionarTodosProductos() {
    productosSeleccionados.clear();
    persistirSeleccion();
    document.querySelectorAll('.chk-select-prod').forEach(chk => {
        chk.checked = false;
        const card = chk.closest('.prod-card');
        if (card) card.classList.remove('is-selected');
    });
    actualizarBarraSeleccion();
}

function manejarExportarPdf(form) {
    const count = productosSeleccionados.size;
    if (count === 0) {
        alert('Selecciona al menos un producto para exportar el PDF.');
        return false;
    }
    const btn = document.getElementById('btn-exportar-sel-pdf');
    const txtBtn = document.getElementById('txt-btn-exportar');
    if (btn) btn.disabled = true;
    if (txtBtn) txtBtn.textContent = 'Generando...';
    // Restaurar botón después de 10s (tiempo suficiente para que se abra la nueva pestaña)
    setTimeout(() => {
        if (btn) btn.disabled = false;
        if (txtBtn) txtBtn.textContent = 'Exportar PDF';
    }, 10000);
    return true;
}

function exportarPdfHeader() {
    if (productosSeleccionados.size > 0) {
        document.getElementById('form-exportar-seleccionados').submit();
    } else {
        if (confirm('¿Deseas exportar el catálogo completo con todos los productos organizados por categoría?')) {
            window.open('<?= $basePath ?>?module=productos&action=exportarPdf&modo=completo', '_blank');
        }
    }
}

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
        const catEsc = escapeHtml(p.categoria || '');

        const pid = parseInt(p.id);
        const estaSel = productosSeleccionados.has(pid);
        // Actualizar mapa de categorías también para productos AJAX
        if (p.categoria) productoCategoria[pid] = p.categoria;

        html += `
        <div class="prod-card ${estaSel ? 'is-selected' : ''}" data-id="${pid}" id="prod-card-${pid}">
            <label class="prod-select-circle" title="Seleccionar para PDF">
                <input type="checkbox" class="chk-select-prod" value="${pid}" ${estaSel ? 'checked' : ''} onchange="toggleSeleccionProducto(this)">
            </label>
            <span data-cat="${catEsc}" style="display:none;"></span>
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
            // Re-sincronizar estado de selección en la vista restaurada
            document.querySelectorAll('.chk-select-prod').forEach(chk => {
                const pid = parseInt(chk.value);
                if (productosSeleccionados.has(pid)) {
                    chk.checked = true;
                    const card = chk.closest('.prod-card');
                    if (card) card.classList.add('is-selected');
                }
            });
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
