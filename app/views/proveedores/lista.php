<?php
/**
 * Vista: Gestión de Proveedores (lista + modal crear + modal editar + modal desactivar)
 * Variables: $proveedores, $busqueda, $filtroEstado, $paginaActual, $totalPaginas, $total, $mensajeExito, $mensajeError, $csrf_token
 */
$pageTitle = 'Gestión de Proveedores';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">

        <!-- ── Cabecera de página ── -->
        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-truck"></i> Directorio de Proveedores</h1>
                <p class="mod-sub"><span id="total-proveedores-count"><?= $total ?? 0 ?></span> proveedor(es) registrado(s)</p>
            </div>
            <button type="button" class="btn-mod-primary" onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg"></i> Nuevo Proveedor
            </button>
        </div>

        <!-- ── Notificaciones Flash ── -->
        <?php if (!empty($mensajeError)):
            $alertaErrorMsg = $mensajeError;
            unset($_SESSION['flash_error']);
            include __DIR__ . '/../layout/alerta_error.php';
        endif; ?>

        <!-- ── Barra de Búsqueda ── -->
        <div class="mod-search-bar">
            <form id="formBusqueda" method="GET" action="<?= $basePath ?>" class="mod-search-form">
                <input type="hidden" name="module" value="proveedores">
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" id="inputBusqueda" name="busqueda" placeholder="Buscar por NIT, Nombre del Proveedor o Banco..."
                       value="<?= htmlspecialchars($busqueda) ?>" class="mod-search-input" autocomplete="off">
                <a href="<?= $basePath ?>?module=proveedores" class="mod-btn-clear" id="btn-clear-proveedores" style="<?= empty($busqueda) ? 'display:none;' : '' ?>" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
            </form>
        </div>

        <!-- ── Tabla de Proveedores ── -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>NIT / Identificación</th>
                        <th>Nombre del Proveedor / Razón Social</th>
                        <th>Tipo Contribuyente</th>
                        <th>Banco</th>
                        <th>N° Cuenta</th>
                        <th>Tipo Cuenta</th>
                        <th>Estado</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-proveedores">
                    <?php if (empty($proveedores)): ?>
                    <tr>
                        <td colspan="8" class="mod-empty">
                            <i class="bi bi-inbox"></i> No se encontraron proveedores registrados.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><strong class="nit-text"><?= htmlspecialchars($p['nit']) ?></strong></td>
                        <td><strong><?= htmlspecialchars($p['nombre_proveedor']) ?></strong></td>
                        <td><span class="sub-text"><?= htmlspecialchars($p['tipo_contribuyente'] ?: 'PERSONA JURÍDICA') ?></span></td>
                        <td><?= htmlspecialchars($p['nombre_banco'] ?: '—') ?></td>
                        <td><span class="mono-text"><?= htmlspecialchars($p['numero_cuenta'] ?: '—') ?></span></td>
                        <td><?= htmlspecialchars($p['tipo_cuenta'] ?: '—') ?></td>
                        <td>
                            <?php if ($p['estado'] === 'activo'): ?>
                            <span class="mod-badge badge-green"><i class="bi bi-check-circle-fill"></i> Activo</span>
                            <?php else: ?>
                            <span class="mod-badge badge-red"><i class="bi bi-x-circle-fill"></i> Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="mod-actions-center">
                                <button type="button" class="mod-btn-edit" title="Editar Proveedor"
                                        onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                                    <?php if ($p['estado'] === 'activo'): ?>
                                    <button type="button" class="mod-btn-del" title="Desactivar / Eliminar Proveedor"
                                            onclick="confirmarEliminar(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre_proveedor'])) ?>', <?= (int)($p['total_asociadas'] ?? 0) ?>)">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php else: ?>
                                    <button type="button" class="mod-btn-reactivar" title="Reactivar Proveedor"
                                            onclick="confirmarReactivar(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre_proveedor'])) ?>')">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <?php if (($p['total_asociadas'] ?? 0) == 0): ?>
                                    <button type="button" class="mod-btn-del" title="Eliminar Definitivamente"
                                            onclick="confirmarEliminar(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre_proveedor'])) ?>', 0)">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Paginación ── -->
        <?php 
        $pagBaseUrl = $basePath . '?module=proveedores' . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
        include __DIR__ . '/../layout/paginacion.php'; 
        ?>

    </main>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CREAR PROVEEDOR
══════════════════════════════════════════════════════════ -->
<div id="modal-crear" class="imo-modal-bg" onclick="cerrarModal('modal-crear', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-truck"></i> Nuevo Proveedor</h3>
            <button type="button" class="imo-modal-close" onclick="cerrarModal('modal-crear')">&times;</button>
        </div>
        <form method="POST" action="<?= $basePath ?>?module=proveedores&action=crear">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="imo-modal-body">
                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>NIT / Identificación <span class="required-star">*</span></label>
                        <input type="text" name="nit" id="crear_nit" required placeholder="Ej: 900535843-3" maxlength="30" autocomplete="off">
                        <small id="crear_nit_feedback" class="nit-feedback-msg"></small>
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Contribuyente</label>
                        <input type="text" name="tipo_contribuyente" placeholder="Ej: PERSONA JURÍDICA" maxlength="100" value="PERSONA JURÍDICA">
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Nombre del Proveedor / Razón Social <span class="required-star">*</span></label>
                    <input type="text" name="nombre_proveedor" required placeholder="Ej: LAS ELECTROMEDICINAS S.A.S." maxlength="200">
                </div>

                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>Nombre del Banco <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="nombre_banco" placeholder="Ej: BANCOLOMBIA" maxlength="100" required>
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Cuenta <span style="color:#ef4444;">*</span></label>
                        <select name="tipo_cuenta" required>
                            <option value="">Seleccione...</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Número de Cuenta <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="numero_cuenta" placeholder="Ej: 123456789" maxlength="100" required>
                </div>

                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>Celular / Teléfono de Contacto</label>
                        <input type="text" name="celular" placeholder="Ej: 3001234567" maxlength="30">
                    </div>
                    <div class="imo-form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" placeholder="Ej: contacto@proveedor.com" maxlength="100">
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Estado</label>
                    <select name="estado">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-crear')">Cancelar</button>
                <button type="submit" id="btn_guardar_crear_prov" class="imo-btn-save"><i class="bi bi-check-lg"></i> Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL EDITAR PROVEEDOR
══════════════════════════════════════════════════════════ -->
<div id="modal-editar" class="imo-modal-bg" onclick="cerrarModal('modal-editar', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-pencil-square"></i> Editar Proveedor</h3>
            <button type="button" class="imo-modal-close" onclick="cerrarModal('modal-editar')">&times;</button>
        </div>
        <form method="POST" action="<?= $basePath ?>?module=proveedores&action=editar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="id" id="edit_id">
            <div class="imo-modal-body">
                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>NIT / Identificación <span class="required-star">*</span></label>
                        <input type="text" name="nit" id="edit_nit" required maxlength="30" autocomplete="off">
                        <small id="edit_nit_feedback" class="nit-feedback-msg"></small>
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Contribuyente</label>
                        <input type="text" name="tipo_contribuyente" id="edit_tipo_contribuyente" maxlength="100">
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Nombre del Proveedor / Razón Social <span class="required-star">*</span></label>
                    <input type="text" name="nombre_proveedor" id="edit_nombre_proveedor" required maxlength="200">
                </div>

                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>Nombre del Banco <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="nombre_banco" id="edit_nombre_banco" maxlength="100" required>
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Cuenta <span style="color:#ef4444;">*</span></label>
                        <select name="tipo_cuenta" id="edit_tipo_cuenta" required>
                            <option value="">Seleccione...</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Número de Cuenta <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="numero_cuenta" id="edit_numero_cuenta" maxlength="100" required>
                </div>

                <div class="imo-form-row">
                    <div class="imo-form-group">
                        <label>Celular / Teléfono de Contacto</label>
                        <input type="text" name="celular" id="edit_celular" maxlength="30">
                    </div>
                    <div class="imo-form-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="correo" id="edit_correo" maxlength="100">
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Estado</label>
                    <select name="estado" id="edit_estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                <button type="submit" id="btn_guardar_editar_prov" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CONFIRMAR ELIMINAR
══════════════════════════════════════════════════════════ -->
<div id="modal-eliminar" class="imo-modal-bg" onclick="cerrarModal('modal-eliminar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3 id="modal-eliminar-titulo"><i class="bi bi-trash-fill"></i> Confirmar Eliminación</h3>
            <button type="button" class="imo-modal-close" onclick="cerrarModal('modal-eliminar')">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p id="modal-eliminar-mensaje">¿Está seguro de que desea eliminar al proveedor <strong id="nombre-eliminar"></strong>?</p>
            <p class="imo-muted-sm" id="modal-eliminar-detalle">Esta acción cambiará el estado a inactivo si tiene órdenes asociadas.</p>
        </div>
        <div class="imo-modal-footer">
            <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <form method="POST" action="<?= $basePath ?>?module=proveedores&action=eliminar" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="eliminar_id">
                <button type="submit" id="btn-confirmar-eliminar-prov" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Eliminar</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Reactivar Proveedor -->
<div id="modal-reactivar" class="imo-modal-bg" onclick="cerrarModal('modal-reactivar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header success">
            <h3><i class="bi bi-arrow-clockwise"></i> Reactivar Proveedor</h3>
            <button type="button" class="imo-modal-close" onclick="cerrarModal('modal-reactivar')">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p>¿Desea reactivar al proveedor <strong id="nombre-reactivar"></strong>?</p>
            <p class="imo-muted-sm">El proveedor volverá a estar disponible para cotizaciones y órdenes de compra.</p>
        </div>
        <div class="imo-modal-footer">
            <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-reactivar')">Cancelar</button>
            <form method="POST" action="<?= $basePath ?>?module=proveedores&action=reactivar" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="reactivar_id">
                <button type="submit" class="imo-btn-success"><i class="bi bi-check-circle-fill"></i> Reactivar</button>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_PROV = '<?= $basePath ?>';

let timerValidarProvCrear = null;
let timerValidarProvEditar = null;
let provIdEditarActual = 0;

function validarNitProveedorEnVivo(inputElem, feedbackElem, submitBtn, idExcluir = 0) {
    const nit = (inputElem.value || '').trim();
    if (!nit) {
        inputElem.classList.remove('input-is-valid', 'input-is-invalid');
        feedbackElem.className = 'nit-feedback-msg';
        feedbackElem.innerHTML = '';
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        return;
    }

    feedbackElem.className = 'nit-feedback-msg nit-feedback-loading';
    feedbackElem.innerHTML = '<i class="bi bi-hourglass-split"></i> Verificando NIT...';

    fetch(BASE_PROV + '?module=proveedores&action=verificar_nit&nit=' + encodeURIComponent(nit) + '&id=' + idExcluir)
        .then(r => r.json())
        .then(d => {
            if (d.disponible === false) {
                inputElem.classList.remove('input-is-valid');
                inputElem.classList.add('input-is-invalid');
                feedbackElem.className = 'nit-feedback-msg nit-feedback-error';
                feedbackElem.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> ' + (d.mensaje || 'NIT ya registrado');
                if (submitBtn) {
                    submitBtn.disabled = true;
                }
            } else {
                inputElem.classList.remove('input-is-invalid');
                inputElem.classList.add('input-is-valid');
                feedbackElem.className = 'nit-feedback-msg nit-feedback-success';
                feedbackElem.innerHTML = '<i class="bi bi-check-circle-fill"></i> NIT disponible';
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(() => {
            feedbackElem.className = 'nit-feedback-msg';
            feedbackElem.innerHTML = '';
        });
}

// Listeners en input crear proveedor
const inputNitProvCrear = document.getElementById('crear_nit');
const feedbackNitProvCrear = document.getElementById('crear_nit_feedback');
const btnGuardarProvCrear = document.getElementById('btn_guardar_crear_prov');
if (inputNitProvCrear) {
    ['input', 'paste', 'change'].forEach(evt => {
        inputNitProvCrear.addEventListener(evt, function() {
            clearTimeout(timerValidarProvCrear);
            timerValidarProvCrear = setTimeout(() => {
                validarNitProveedorEnVivo(inputNitProvCrear, feedbackNitProvCrear, btnGuardarProvCrear, 0);
            }, evt === 'input' ? 300 : 50);
        });
    });
}

// Listeners en input editar proveedor
const inputNitProvEditar = document.getElementById('edit_nit');
const feedbackNitProvEditar = document.getElementById('edit_nit_feedback');
const btnGuardarProvEditar = document.getElementById('btn_guardar_editar_prov');
if (inputNitProvEditar) {
    ['input', 'paste', 'change'].forEach(evt => {
        inputNitProvEditar.addEventListener(evt, function() {
            clearTimeout(timerValidarProvEditar);
            timerValidarProvEditar = setTimeout(() => {
                validarNitProveedorEnVivo(inputNitProvEditar, feedbackNitProvEditar, btnGuardarProvEditar, provIdEditarActual);
            }, evt === 'input' ? 300 : 50);
        });
    });
}

function abrirModalCrear() {
    if (inputNitProvCrear) {
        inputNitProvCrear.value = '';
        inputNitProvCrear.classList.remove('input-is-valid', 'input-is-invalid');
    }
    if (feedbackNitProvCrear) {
        feedbackNitProvCrear.className = 'nit-feedback-msg';
        feedbackNitProvCrear.innerHTML = '';
    }
    if (btnGuardarProvCrear) {
        btnGuardarProvCrear.disabled = false;
    }
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(prov) {
    provIdEditarActual = prov.id || 0;
    document.getElementById('edit_id').value                 = prov.id;
    document.getElementById('edit_nit').value                = prov.nit || '';
    document.getElementById('edit_nombre_proveedor').value   = prov.nombre_proveedor || '';
    document.getElementById('edit_tipo_contribuyente').value = prov.tipo_contribuyente || '';
    document.getElementById('edit_nombre_banco').value       = prov.nombre_banco || '';
    document.getElementById('edit_numero_cuenta').value      = prov.numero_cuenta || '';
    document.getElementById('edit_tipo_cuenta').value        = prov.tipo_cuenta || '';
    document.getElementById('edit_celular').value            = prov.celular || '';
    document.getElementById('edit_correo').value             = prov.correo || '';
    document.getElementById('edit_estado').value             = prov.estado || 'activo';
    
    if (inputNitProvEditar) {
        inputNitProvEditar.classList.remove('input-is-valid', 'input-is-invalid');
    }
    if (feedbackNitProvEditar) {
        feedbackNitProvEditar.className = 'nit-feedback-msg';
        feedbackNitProvEditar.innerHTML = '';
    }
    if (btnGuardarProvEditar) {
        btnGuardarProvEditar.disabled = false;
    }

    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarEliminar(id, nombre, totalAsociadas = 0) {
    const inpId = document.getElementById('eliminar_id');
    if (inpId) inpId.value = id;

    const tituloModal = document.getElementById('modal-eliminar-titulo');
    const msg         = document.getElementById('modal-eliminar-mensaje');
    const detalle     = document.getElementById('modal-eliminar-detalle');
    const btn         = document.getElementById('btn-confirmar-eliminar-prov');

    if (totalAsociadas > 0) {
        if (tituloModal) tituloModal.innerHTML = '<i class="bi bi-slash-circle-fill"></i> Desactivar Proveedor';
        if (msg) msg.innerHTML = `¿Desea desactivar al proveedor <strong>${escapeHtml(nombre)}</strong>?`;
        if (detalle) detalle.innerHTML = `Este proveedor tiene <strong>${totalAsociadas} cotización(es) u orden(es) asociada(s)</strong>. Se cambiará a estado <strong>Inactivo</strong> para proteger el historial.`;
        if (btn) {
            btn.className = 'imo-btn-danger';
            btn.innerHTML = '<i class="bi bi-slash-circle-fill"></i> Desactivar';
        }
    } else {
        if (tituloModal) tituloModal.innerHTML = '<i class="bi bi-trash-fill"></i> Confirmar Eliminación';
        if (msg) msg.innerHTML = `¿Está seguro de eliminar definitivamente al proveedor <strong>${escapeHtml(nombre)}</strong>?`;
        if (detalle) detalle.innerHTML = 'Este proveedor no tiene historial asociado. Se eliminará completamente del sistema.';
        if (btn) {
            btn.className = 'imo-btn-danger';
            btn.innerHTML = '<i class="bi bi-trash-fill"></i> Eliminar Definitivamente';
        }
    }

    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarReactivar(id, nombre) {
    const spanNom = document.getElementById('nombre-reactivar');
    if (spanNom) spanNom.textContent = nombre;
    const inpId = document.getElementById('reactivar_id');
    if (inpId) inpId.value = id;
    document.getElementById('modal-reactivar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id, evento) {
    if (evento && evento.target !== document.getElementById(id)) return;
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('open');
    }
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['modal-crear', 'modal-editar', 'modal-eliminar', 'modal-reactivar'].forEach(id => {
            document.getElementById(id)?.classList.remove('open');
        });
        document.body.style.overflow = 'auto';
    }
});

// ── Búsqueda en vivo idéntica a Catálogo de Productos (AJAX sin recargar) ──
let timerBusqProveedores  = null;
const inpBusqueda         = document.getElementById('inputBusqueda');
const formBusqueda        = document.getElementById('formBusqueda');
const btnClearProveedores = document.getElementById('btn-clear-proveedores');
const tbodyProveedores    = document.getElementById('tbody-proveedores');
const pagInfoProveedores  = document.querySelector('.mod-pag-info');
const htmlOriginalProvTbody = tbodyProveedores ? tbodyProveedores.innerHTML : '';

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function escapeJsString(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function restaurarVistaProveedores() {
    if (tbodyProveedores) tbodyProveedores.innerHTML = htmlOriginalProvTbody;
    const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
    if (wrapPag) wrapPag.style.display = '';
    if (pagInfoProveedores) pagInfoProveedores.style.display = '';
}

function renderizarProveedoresAjax(lista, isAdmin) {
    if (!tbodyProveedores) return;

    if (!lista || lista.length === 0) {
        tbodyProveedores.innerHTML = `
            <tr>
                <td colspan="8" class="mod-empty">
                    <i class="bi bi-search"></i>
                    <p>No se encontraron proveedores coincidentes.</p>
                </td>
            </tr>
        `;
        if (pagInfoProveedores) pagInfoProveedores.textContent = '0 proveedor(es) encontrado(s)';
        return;
    }

    let html = '';
    lista.forEach(p => {
        const nitEsc      = escapeHtml(p.nit || '');
        const nombreEsc   = escapeHtml(p.nombre_proveedor || '');
        const tipoContrib = escapeHtml(p.tipo_contribuyente || 'PERSONA JURÍDICA');
        const bancoEsc    = escapeHtml(p.nombre_banco || '—');
        const numCuenta   = escapeHtml(p.numero_cuenta || '—');
        const tipoCuenta  = escapeHtml(p.tipo_cuenta || '—');
        const estadoVal   = (p.estado || 'activo').toLowerCase();
        const badgeClass  = estadoVal === 'activo' ? 'badge-green' : 'badge-red';
        const badgeIcon   = estadoVal === 'activo' ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
        const estadoLabel = estadoVal === 'activo' ? 'Activo' : 'Inactivo';
        const pJson       = escapeHtml(JSON.stringify(p));
        const nombreSafe  = escapeJsString(p.nombre_proveedor || '');
        const pid         = parseInt(p.id);

        html += `
            <tr>
                <td><strong class="nit-text">${nitEsc}</strong></td>
                <td><strong>${nombreEsc}</strong></td>
                <td><span class="sub-text">${tipoContrib}</span></td>
                <td>${bancoEsc}</td>
                <td><span class="mono-text">${numCuenta}</span></td>
                <td>${tipoCuenta}</td>
                <td>
                    <span class="mod-badge ${badgeClass}"><i class="bi ${badgeIcon}"></i> ${estadoLabel}</span>
                </td>
                <td>
                    <div class="mod-actions-center">
                        <button type="button" class="mod-btn-edit" title="Editar Proveedor" onclick="abrirModalEditar(${pJson})">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        ${isAdmin ? (
                            estadoVal === 'activo'
                                ? `<button type="button" class="mod-btn-del" title="Desactivar / Eliminar Proveedor" onclick="confirmarEliminar(${pid}, '${nombreSafe}', ${parseInt(p.total_asociadas || 0)})">
                                    <i class="bi bi-trash-fill"></i>
                                   </button>`
                                : `<button type="button" class="mod-btn-reactivar" title="Reactivar Proveedor" onclick="confirmarReactivar(${pid}, '${nombreSafe}')">
                                    <i class="bi bi-arrow-clockwise"></i>
                                   </button>
                                   ${(parseInt(p.total_asociadas || 0) === 0) ? `
                                   <button type="button" class="mod-btn-del" title="Eliminar Definitivamente" onclick="confirmarEliminar(${pid}, '${nombreSafe}', 0)">
                                    <i class="bi bi-trash-fill"></i>
                                   </button>` : ''}
                                   `
                        ) : ''}
                    </div>
                </td>
            </tr>
        `;
    });

    tbodyProveedores.innerHTML = html;
    if (pagInfoProveedores) {
        pagInfoProveedores.textContent = `Mostrando ${lista.length} proveedor(es) encontrado(s)`;
    }
}

if (inpBusqueda) {
    inpBusqueda.addEventListener('input', function() {
        clearTimeout(timerBusqProveedores);
        const q = this.value.trim();

        if (btnClearProveedores) {
            btnClearProveedores.style.display = q.length > 0 ? 'flex' : 'none';
        }

        if (q === '') {
            restaurarVistaProveedores();
            return;
        }

        timerBusqProveedores = setTimeout(() => {
            fetch(`${BASE_PROV}?module=proveedores&action=ajax_buscar&modo=directorio&term=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'ok') {
                        const wrapPag = document.querySelector('.mod-pagination-wrap, .mod-paginacion, ul.pagination, div[class*="pagin"]');
                        if (wrapPag) wrapPag.style.display = 'none';
                        renderizarProveedoresAjax(data.proveedores, data.isAdmin);
                    }
                })
                .catch(err => console.error('Error en búsqueda AJAX de proveedores:', err));
        }, 200);
    });

    if (formBusqueda) {
        formBusqueda.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }
}

if (btnClearProveedores) {
    btnClearProveedores.addEventListener('click', function(e) {
        e.preventDefault();
        if (inpBusqueda) {
            inpBusqueda.value = '';
            inpBusqueda.focus();
        }
        btnClearProveedores.style.display = 'none';
        restaurarVistaProveedores();
    });
}

// ── Auto-formateo de NIT: Quitar puntos automáticamente y preservar guion ──
document.querySelectorAll('input[name="nit"]').forEach(function(inp) {
    inp.addEventListener('input', function() {
        const cursor = this.selectionStart;
        const valAnt = this.value;
        // Eliminar puntos y espacios
        const valLimpio = valAnt.replace(/[\.\s]/g, '');
        if (valAnt !== valLimpio) {
            this.value = valLimpio;
            // Ajustar posición del cursor tras eliminar punto
            const diff = valAnt.length - valLimpio.length;
            this.setSelectionRange(Math.max(0, cursor - diff), Math.max(0, cursor - diff));
        }
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
