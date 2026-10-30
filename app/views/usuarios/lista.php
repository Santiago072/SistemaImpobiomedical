<?php
/**
 * Vista: Gestión de Usuarios (lista + modal crear + modal editar en 1 vista)
 * Variables: $usuarios, $busqueda, $paginaActual, $totalPaginas, $total, $mensajeExito, $mensajeError, $csrf_token
 */
$pageTitle = 'Gestión de Usuarios';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h1>
                <p class="mod-sub"><?= $total ?? 0 ?> usuario(s) registrado(s)</p>
            </div>
            <button type="button" class="btn-mod-primary" onclick="abrirModalCrear(event)">
                <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
            </button>
        </div>

        <?php if (!empty($mensajeError)):
            $alertaErrorMsg = $mensajeError;
            include __DIR__ . '/../layout/alerta_error.php';
        endif; ?>

        <!-- Búsqueda -->
        <div class="mod-search-bar">
            <form action="<?= $basePath ?>" method="GET" class="mod-search-form" id="form-busqueda-usuarios">
                <input type="hidden" name="module" value="usuarios">
                <input type="hidden" name="action" value="lista">
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" name="busqueda" id="input-busqueda-usuarios" class="mod-search-input" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar usuario por nombre, cargo, rol, correo...">
                <a href="<?= $basePath ?>?module=usuarios&action=lista" class="mod-btn-clear" id="btn-clear-usuarios" style="<?= empty($busqueda) ? 'display:none;' : '' ?>" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
            </form>
        </div>

        <!-- Cuadrícula de tarjetas -->
        <div class="usr-grid">
            <?php if (empty($usuarios)): ?>
            <div class="mod-empty-card">
                <i class="bi bi-search"></i>
                <p>No se encontraron usuarios.</p>
            </div>
            <?php else: ?>
            <?php foreach ($usuarios as $u): ?>
            <div class="usr-card">
                <div class="usr-status <?= strtolower($u['estado']) === 'activo' ? 'status-on' : 'status-off' ?>">
                    <i class="bi bi-circle-fill"></i>
                    <?= htmlspecialchars($u['estado']) ?>
                </div>
                <div class="usr-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="usr-name"><?= htmlspecialchars($u['nombre']) ?></div>
                <div class="usr-cargo">
                    <?= htmlspecialchars($u['cargo'] ?? 'Sin cargo') ?>
                </div>
                <div class="usr-role">
                    <i class="bi bi-shield-check"></i>
                    <?= htmlspecialchars(ucfirst($u['rol'])) ?>
                </div>
                <div class="usr-info">
                    <div><i class="bi bi-upc-scan"></i> Cód: <?= htmlspecialchars($u['codigo'] ?? 'N/A') ?></div>
                    <div><i class="bi bi-envelope"></i> <?= htmlspecialchars($u['correo'] ?? '') ?></div>
                    <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($u['telefono'] ?? '') ?></div>
                </div>
                <div class="usr-actions">
                    <button type="button" class="mod-btn-edit" title="Editar Usuario" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($u)) ?>, event)">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="mod-btn-key" title="Restablecer Contraseña al Documento" onclick="confirmarResetDirecto(<?= intval($u['id']) ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>', '<?= htmlspecialchars(addslashes($u['documento'])) ?>', event)">
                        <i class="bi bi-key-fill"></i>
                    </button>
                    <?php if (strtolower($u['estado'] ?? 'activo') === 'activo'): ?>
                    <button type="button" class="mod-btn-del" title="Desactivar / Eliminar" onclick="confirmarEliminar(<?= intval($u['id']) ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>', <?= (int)($u['total_cotizaciones'] ?? 0) ?>, event)">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    <?php else: ?>
                    <button type="button" class="mod-btn-reactivar" title="Reactivar Usuario" onclick="confirmarReactivar(<?= intval($u['id']) ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>', event)">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <?php if ((int)($u['total_cotizaciones'] ?? 0) === 0): ?>
                    <button type="button" class="mod-btn-del" title="Eliminar Definitivamente" onclick="confirmarEliminar(<?= intval($u['id']) ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>', 0, event)">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php 
        $pagBaseUrl = $basePath . '?module=usuarios&action=lista' . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
        include __DIR__ . '/../layout/paginacion.php'; 
        ?>
        <?php if ($total > 0): ?>
        <p class="mod-pag-info">Mostrando página <?= $paginaActual ?> de <?= $totalPaginas ?> (<?= $total ?> usuarios)</p>
        <?php endif; ?>

    </main>
</div>

<!-- MODAL: Crear -->
<div id="modal-crear" class="imo-modal-bg">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-person-plus-fill"></i> Nuevo Usuario</h3>
            <button onclick="cerrarModal('modal-crear')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" action="<?= $basePath ?>?module=usuarios&action=crear" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Código Asesor *</label>
                    <input type="text" name="codigo" required maxlength="10" placeholder="Ej: EB">
                </div>
                <div class="imo-form-group">
                    <label>Documento *</label>
                    <input type="text" name="documento" required maxlength="15" placeholder="CC / NIT">
                </div>
            </div>
            <div class="imo-form-group">
                <label>Nombre Completo (Asesor) *</label>
                <input type="text" name="nombre" required maxlength="60" placeholder="Juan Pérez">
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Correo Electrónico <span class="label-optional-hint">(opcional)</span></label>
                    <input type="email" name="correo" maxlength="60" placeholder="juan@empresa.com">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono <span class="label-optional-hint">(opcional)</span></label>
                    <input type="text" name="telefono" maxlength="15" placeholder="3001234567">
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Cargo *</label>
                    <input type="text" name="cargo" required maxlength="50" placeholder="Ej: ING COMERCIAL">
                </div>
                <div class="imo-form-group">
                    <label>Rol *</label>
                    <select name="rol" required>
                        <option value="">Seleccione un Rol</option>
                        <option value="admin">Administrador</option>
                        <option value="compras">Encargado de Compras</option>
                        <option value="usuario">Usuario / Asesor</option>
                    </select>
                </div>
            </div>
            <div class="imo-form-group">
                <label>Contraseña <span class="label-optional-hint">(opcional - por defecto usa el documento)</span></label>
                <div class="password-input-wrap">
                    <input type="password" id="c_password" name="password" minlength="6" maxlength="30" placeholder="••••••••">
                    <button type="button" onclick="togglePassVisibility('c_password', 'c_eyeIcon')" class="password-toggle-btn" aria-label="Mostrar/Ocultar contraseña">
                        <i class="bi bi-eye-slash" id="c_eyeIcon"></i>
                    </button>
                </div>
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-crear')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-person-check-fill"></i> Crear Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Editar -->
<div id="modal-editar" class="imo-modal-bg">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-pencil-square"></i> Editar Usuario</h3>
            <button onclick="cerrarModal('modal-editar')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" id="form-editar-usuario" action="" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Código Asesor *</label>
                    <input type="text" id="e_codigo" name="codigo" required maxlength="10">
                </div>
                <div class="imo-form-group">
                    <label>Documento *</label>
                    <input type="text" id="e_documento" name="documento" required maxlength="15">
                </div>
            </div>
            <div class="imo-form-group">
                <label>Nombre Completo (Asesor) *</label>
                <input type="text" id="e_nombre" name="nombre" required maxlength="60">
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Correo <span class="label-optional-hint">(opcional)</span></label>
                    <input type="email" id="e_correo" name="correo" maxlength="60">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono <span class="label-optional-hint">(opcional)</span></label>
                    <input type="text" id="e_telefono" name="telefono" maxlength="15">
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Cargo *</label>
                    <input type="text" id="e_cargo" name="cargo" required maxlength="50">
                </div>
                <div class="imo-form-group">
                    <label>Rol *</label>
                    <select id="e_rol" name="rol" required>
                        <option value="admin">Administrador</option>
                        <option value="compras">Encargado de Compras</option>
                        <option value="usuario">Usuario / Asesor</option>
                    </select>
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Estado</label>
                    <select id="e_estado" name="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="imo-form-group">
                    <label>Nueva Contraseña <span class="label-optional-hint">(opcional)</span></label>
                    <div class="password-input-wrap">
                        <input type="password" id="e_password" name="password" minlength="6" maxlength="30" placeholder="••••••••">
                        <button type="button" onclick="togglePassVisibility('e_password', 'e_eyeIcon')" class="password-toggle-btn" aria-label="Mostrar/Ocultar contraseña">
                            <i class="bi bi-eye-slash" id="e_eyeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="imo-modal-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <button type="button" onclick="restablecerClaveDocumento(event)" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 8px 14px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;" title="Restablecer la contraseña del usuario a su documento">
                    <i class="bi bi-key-fill"></i> Restablecer al Documento
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                    <button type="submit" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Formulario Oculto para Restablecer Contraseña -->
<form id="form-reset-pass-user" method="POST" action="<?= $basePath ?>?module=usuarios&action=reset_password" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
    <input type="hidden" name="id" id="reset_pass_user_id" value="">
</form>

<!-- MODAL: Eliminar / Desactivar -->
<div id="modal-eliminar" class="imo-modal-bg" onclick="cerrarModal('modal-eliminar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3 id="modal-eliminar-titulo"><i class="bi bi-trash-fill"></i> Confirmar Eliminación</h3>
            <button type="button" onclick="cerrarModal('modal-eliminar')" class="imo-modal-close">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p id="modal-eliminar-mensaje" class="imo-modal-desc">¿Está seguro de que desea eliminar al usuario <strong id="nombre-eliminar"></strong>?</p>
            <p class="imo-muted-sm" id="modal-eliminar-detalle">Esta acción cambiará el estado a inactivo para proteger el historial de cotizaciones.</p>
        </div>
        <div class="imo-modal-footer">
            <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <form id="form-eliminar-usuario" method="POST" action="" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" id="btn-confirmar-eliminar" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Eliminar</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Reactivar Usuario -->
<div id="modal-reactivar" class="imo-modal-bg">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header" style="background:#ecfdf5; color:#065f46; border-bottom:1px solid #a7f3d0;">
            <h3><i class="bi bi-arrow-clockwise"></i> Reactivar Usuario</h3>
            <button onclick="cerrarModal('modal-reactivar')" class="imo-modal-close">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p class="imo-modal-desc">¿Deseas reactivar el acceso al sistema para el usuario <strong id="nombre-reactivar"></strong>?</p>
            <p style="font-size:0.85rem; color:#64748b; margin-top:8px;">Su estado volverá a estar <strong>Activo</strong> y podrá iniciar sesión normalmente.</p>
        </div>
        <div class="imo-modal-footer">
            <button class="imo-btn-cancel" onclick="cerrarModal('modal-reactivar')">Cancelar</button>
            <form id="form-reactivar-usuario" method="POST" action="<?= $basePath ?>?module=usuarios&action=reactivar" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" id="reactivar_usuario_id" value="">
                <button type="submit" class="imo-btn-save" style="background:#0d9488;"><i class="bi bi-check-circle-fill"></i> Sí, reactivar</button>
            </form>
        </div>
    </div>
</div>

<script>
const BASE = '<?= $basePath ?>';
const CSRF = '<?= htmlspecialchars($csrf_token ?? '') ?>';

function togglePassVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}

function abrirModalCrear(e) {
    if(e) { e.preventDefault(); e.stopPropagation(); }
    const pIn = document.getElementById('c_password');
    if (pIn) { pIn.value = ''; pIn.type = 'password'; }
    const pIcon = document.getElementById('c_eyeIcon');
    if (pIcon) { pIcon.className = 'bi bi-eye-slash'; }
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(u, e) {
    if(e) { e.preventDefault(); e.stopPropagation(); }
    document.getElementById('e_codigo').value    = u.codigo || '';
    document.getElementById('e_documento').value = u.documento || '';
    document.getElementById('e_nombre').value    = u.nombre || '';
    document.getElementById('e_correo').value    = u.correo || '';
    document.getElementById('e_telefono').value  = u.telefono || '';
    document.getElementById('e_cargo').value     = u.cargo || '';
    document.getElementById('e_rol').value       = u.rol || 'usuario';
    document.getElementById('e_estado').value    = u.estado || 'activo';
    const pIn = document.getElementById('e_password');
    if (pIn) { pIn.value = ''; pIn.type = 'password'; }
    const pIcon = document.getElementById('e_eyeIcon');
    if (pIcon) { pIcon.className = 'bi bi-eye-slash'; }
    document.getElementById('form-editar-usuario').action = BASE + '?module=usuarios&action=editar&id=' + u.id;
    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

const CURRENT_USER_ID = <?= (int)($_SESSION['usuario_id'] ?? 0) ?>;

function confirmarEliminar(id, nombre, totalCotizaciones = 0, e = null) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    if (id === CURRENT_USER_ID) {
        alert('No puedes eliminar ni desactivar tu propia cuenta mientras tienes la sesión activa.');
        return;
    }
    document.getElementById('form-eliminar-usuario').action = BASE + '?module=usuarios&action=eliminar&id=' + id;
    const tituloModal = document.getElementById('modal-eliminar-titulo');
    const msg         = document.getElementById('modal-eliminar-mensaje');
    const detalle     = document.getElementById('modal-eliminar-detalle');
    const btn         = document.getElementById('btn-confirmar-eliminar');

    if (totalCotizaciones > 0) {
        if (tituloModal) tituloModal.innerHTML = '<i class="bi bi-slash-circle-fill"></i> Desactivar Usuario';
        if (msg) msg.innerHTML = `¿Desea desactivar al usuario <strong>${escapeHtml(nombre)}</strong>?`;
        if (detalle) detalle.innerHTML = `Este usuario tiene <strong>${totalCotizaciones} cotización(es) asociada(s)</strong>. Se cambiará a estado <strong>Inactivo</strong> para proteger el historial.`;
        if (btn) {
            btn.className = 'imo-btn-danger';
            btn.innerHTML = '<i class="bi bi-slash-circle-fill"></i> Desactivar';
        }
    } else {
        if (tituloModal) tituloModal.innerHTML = '<i class="bi bi-trash-fill"></i> Confirmar Eliminación';
        if (msg) msg.innerHTML = `¿Está seguro de eliminar definitivamente al usuario <strong>${escapeHtml(nombre)}</strong>?`;
        if (detalle) detalle.innerHTML = 'Este usuario no tiene cotizaciones asociadas. Se eliminará por completo del sistema.';
        if (btn) {
            btn.className = 'imo-btn-danger';
            btn.innerHTML = '<i class="bi bi-trash-fill"></i> Eliminar Definitivamente';
        }
    }
    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarReactivar(id, nombre, e = null) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    document.getElementById('reactivar_usuario_id').value = id;
    document.getElementById('nombre-reactivar').textContent = nombre;
    document.getElementById('modal-reactivar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarResetDirecto(id, nombre, doc, e) {
    if (e) { e.preventDefault(); e.stopPropagation(); }
    if (confirm(`¿Restablecer la contraseña de "${nombre}" a su número de documento (${doc})?`)) {
        document.getElementById('reset_pass_user_id').value = id;
        document.getElementById('form-reset-pass-user').submit();
    }
}

function restablecerClaveDocumento(e) {
    if (e) { e.preventDefault(); }
    const doc = document.getElementById('e_documento').value;
    const nom = document.getElementById('e_nombre').value;
    if (confirm(`¿Restablecer la contraseña de "${nom}" a su número de documento (${doc})?`)) {
        const actionUrl = document.getElementById('form-editar-usuario').action;
        const idMatch = actionUrl.match(/id=(\d+)/);
        if (idMatch && idMatch[1]) {
            document.getElementById('reset_pass_user_id').value = idMatch[1];
            document.getElementById('form-reset-pass-user').submit();
        }
    }
}

function cerrarModal(id, evento) {
    if (evento && evento.target !== document.getElementById(id)) return;
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        ['modal-crear','modal-editar','modal-eliminar','modal-reactivar'].forEach(id => {
            document.getElementById(id)?.classList.remove('open');
        });
        document.body.style.overflow = 'auto';
    }
});

// ── Búsqueda en vivo idéntica a Catálogo de Productos (AJAX sin recargar) ──
let timerBusqUsuarios  = null;
const inpBusqUsuarios  = document.getElementById('input-busqueda-usuarios');
const formBusqUsuarios = document.getElementById('form-busqueda-usuarios');
const btnClearUsuarios = document.getElementById('btn-clear-usuarios');
const usrGrid          = document.querySelector('.usr-grid');
const pagInfoUsuarios  = document.querySelector('.mod-pag-info');
const htmlOriginalUsrGrid = usrGrid ? usrGrid.innerHTML : '';

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function escapeJsString(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function capitalizar(text) {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
}

function restaurarVistaUsuarios() {
    if (usrGrid) usrGrid.innerHTML = htmlOriginalUsrGrid;
    const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
    if (wrapPag) wrapPag.style.display = '';
    if (pagInfoUsuarios) pagInfoUsuarios.style.display = '';
}

function renderizarUsuariosAjax(usuarios) {
    if (!usrGrid) return;

    if (!usuarios || usuarios.length === 0) {
        usrGrid.innerHTML = `
            <div class="mod-empty-card" style="grid-column: 1 / -1;">
                <i class="bi bi-search"></i>
                <p>No se encontraron usuarios coincidentes.</p>
            </div>
        `;
        if (pagInfoUsuarios) pagInfoUsuarios.textContent = '0 usuario(s) encontrado(s)';
        return;
    }

    let html = '';
    usuarios.forEach(u => {
        const estActivo = (u.estado || 'activo').toLowerCase() === 'activo';
        const uJson = escapeHtml(JSON.stringify(u));
        const nombreSafe = escapeJsString(u.nombre || '');
        const uid = parseInt(u.id);

        html += `
        <div class="usr-card">
            <div class="usr-status ${estActivo ? 'status-on' : 'status-off'}">
                <i class="bi bi-circle-fill"></i>
                ${escapeHtml(u.estado || 'Activo')}
            </div>
            <div class="usr-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="usr-name">${escapeHtml(u.nombre || '')}</div>
            <div class="usr-cargo">
                ${escapeHtml(u.cargo || 'Sin cargo')}
            </div>
            <div class="usr-role">
                <i class="bi bi-shield-check"></i>
                ${escapeHtml(capitalizar(u.rol || ''))}
            </div>
            <div class="usr-info">
                <div><i class="bi bi-upc-scan"></i> Cód: ${escapeHtml(u.codigo || 'N/A')}</div>
                <div><i class="bi bi-envelope"></i> ${escapeHtml(u.correo || '')}</div>
                <div><i class="bi bi-telephone"></i> ${escapeHtml(u.telefono || '')}</div>
            </div>
            <div class="usr-actions">
                <button type="button" class="mod-btn-edit" title="Editar Usuario" onclick="abrirModalEditar(${uJson}, event)">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button type="button" class="mod-btn-key" title="Restablecer Contraseña al Documento" onclick="confirmarResetDirecto(${uid}, '${nombreSafe}', '${escapeJsString(u.documento || '')}', event)">
                    <i class="bi bi-key-fill"></i>
                </button>
                ${estActivo ? `
                <button type="button" class="mod-btn-del" title="Desactivar / Eliminar" onclick="confirmarEliminar(${uid}, '${nombreSafe}', ${parseInt(u.total_cotizaciones || 0)}, event)">
                    <i class="bi bi-trash-fill"></i>
                </button>
                ` : `
                <button type="button" class="mod-btn-reactivar" title="Reactivar Usuario" onclick="confirmarReactivar(${uid}, '${nombreSafe}', event)">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                ${parseInt(u.total_cotizaciones || 0) === 0 ? `
                <button type="button" class="mod-btn-del" title="Eliminar Definitivamente" onclick="confirmarEliminar(${uid}, '${nombreSafe}', 0, event)">
                    <i class="bi bi-trash-fill"></i>
                </button>
                ` : ''}
                `}
            </div>
        </div>
        `;
    });

    usrGrid.innerHTML = html;
    if (pagInfoUsuarios) {
        pagInfoUsuarios.textContent = `Mostrando ${usuarios.length} usuario(s) encontrado(s)`;
    }
}

if (inpBusqUsuarios) {
    inpBusqUsuarios.addEventListener('input', function() {
        clearTimeout(timerBusqUsuarios);
        const q = this.value.trim();

        if (btnClearUsuarios) {
            btnClearUsuarios.style.display = q.length > 0 ? 'flex' : 'none';
        }

        if (q === '') {
            restaurarVistaUsuarios();
            return;
        }

        timerBusqUsuarios = setTimeout(() => {
            fetch(`${BASE}?module=usuarios&action=ajax_buscar&term=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
                        if (wrapPag) wrapPag.style.display = 'none';
                        renderizarUsuariosAjax(data.usuarios);
                    }
                })
                .catch(err => console.error('Error en búsqueda AJAX de usuarios:', err));
        }, 200);
    });

    if (formBusqUsuarios) {
        formBusqUsuarios.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }
}

if (btnClearUsuarios) {
    btnClearUsuarios.addEventListener('click', function(e) {
        e.preventDefault();
        if (inpBusqUsuarios) {
            inpBusqUsuarios.value = '';
            inpBusqUsuarios.focus();
        }
        btnClearUsuarios.style.display = 'none';
        restaurarVistaUsuarios();
    });
}
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
