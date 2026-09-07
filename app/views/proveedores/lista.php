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
                <p class="mod-sub"><?= $total ?? 0 ?> proveedor(es) registrado(s)</p>
            </div>
            <button type="button" class="btn-mod-primary" onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg"></i> Nuevo Proveedor
            </button>
        </div>

        <!-- ── Notificaciones Flash ── -->
        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php unset($_SESSION['flash_exito']); ?>
        <?php endif; ?>
        <?php if (!empty($mensajeError)): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- ── Barra de Búsqueda ── -->
        <div class="mod-search-bar">
            <form id="formBusqueda" method="GET" action="<?= $basePath ?>" class="mod-search-form">
                <input type="hidden" name="module" value="proveedores">
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" id="inputBusqueda" name="busqueda" placeholder="Buscar por NIT, Nombre del Proveedor o Banco..."
                       value="<?= htmlspecialchars($busqueda) ?>" class="mod-search-input" autocomplete="off">
                <?php if ($busqueda !== ''): ?>
                <a href="<?= $basePath ?>?module=proveedores" class="mod-btn-clear" title="Limpiar búsqueda"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
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
                <tbody>
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
                                <?php if ($p['estado'] === 'activo' && ($_SESSION['rol'] ?? '') === 'admin'): ?>
                                <button type="button" class="mod-btn-del" title="Desactivar Proveedor"
                                        onclick="confirmarEliminar(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nombre_proveedor'])) ?>')">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
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
                        <input type="text" name="nit" required placeholder="Ej: 900535843-3" maxlength="30" autocomplete="off">
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
                        <label>Nombre del Banco</label>
                        <input type="text" name="nombre_banco" placeholder="Ej: BANCOLOMBIA" maxlength="100">
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Cuenta</label>
                        <select name="tipo_cuenta">
                            <option value="">Seleccione...</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Número de Cuenta</label>
                    <input type="text" name="numero_cuenta" placeholder="Ej: 123456789" maxlength="100">
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
                <button type="submit" class="imo-btn-save"><i class="bi bi-check-lg"></i> Guardar Proveedor</button>
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
                        <input type="text" name="nit" id="edit_nit" required maxlength="30">
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
                        <label>Nombre del Banco</label>
                        <input type="text" name="nombre_banco" id="edit_nombre_banco" maxlength="100">
                    </div>
                    <div class="imo-form-group">
                        <label>Tipo de Cuenta</label>
                        <select name="tipo_cuenta" id="edit_tipo_cuenta">
                            <option value="">Seleccione...</option>
                            <option value="Ahorros">Ahorros</option>
                            <option value="Corriente">Corriente</option>
                        </select>
                    </div>
                </div>

                <div class="imo-form-group">
                    <label>Número de Cuenta</label>
                    <input type="text" name="numero_cuenta" id="edit_numero_cuenta" maxlength="100">
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
                <button type="submit" class="imo-btn-save"><i class="bi bi-check-lg"></i> Actualizar Proveedor</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL CONFIRMAR DESACTIVAR PROVEEDOR
══════════════════════════════════════════════════════════ -->
<div id="modal-eliminar" class="imo-modal-bg" onclick="cerrarModal('modal-eliminar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3><i class="bi bi-exclamation-triangle-fill"></i> Desactivar Proveedor</h3>
            <button type="button" class="imo-modal-close" onclick="cerrarModal('modal-eliminar')">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p class="imo-modal-desc">¿Estás seguro de desactivar al proveedor <strong id="nombre-eliminar"></strong>? Ya no aparecerá disponible para nuevas cotizaciones.</p>
        </div>
        <div class="imo-modal-footer">
            <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <form id="form-eliminar-prov" method="POST" action="<?= $basePath ?>?module=proveedores&action=eliminar" class="form-inline-action">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" id="eliminar_id">
                <button type="submit" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Desactivar</button>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalCrear() {
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(prov) {
    document.getElementById('edit_id').value                 = prov.id;
    document.getElementById('edit_nit').value                = prov.nit || '';
    document.getElementById('edit_nombre_proveedor').value   = prov.nombre_proveedor || '';
    document.getElementById('edit_tipo_contribuyente').value = prov.tipo_contribuyente || '';
    document.getElementById('edit_nombre_banco').value       = prov.nombre_banco || '';
    document.getElementById('edit_numero_cuenta').value      = prov.numero_cuenta || '';
    document.getElementById('edit_tipo_cuenta').value        = prov.tipo_cuenta || '';
    document.getElementById('edit_estado').value             = prov.estado || 'activo';
    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarEliminar(id, nombre) {
    document.getElementById('nombre-eliminar').textContent = nombre;
    document.getElementById('eliminar_id').value = id;
    document.getElementById('modal-eliminar').classList.add('open');
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
        ['modal-crear', 'modal-editar', 'modal-eliminar'].forEach(id => {
            document.getElementById(id)?.classList.remove('open');
        });
        document.body.style.overflow = 'auto';
    }
});

// ── Búsqueda automática en vivo sin presionar Enter (debounce 400ms) ──
const inpBusqueda = document.getElementById('inputBusqueda');
const formBusqueda = document.getElementById('formBusqueda');
let searchDebounceTimer = null;

if (inpBusqueda && formBusqueda) {
    // Colocar cursor al final del texto si ya tiene contenido
    if (inpBusqueda.value.length > 0) {
        inpBusqueda.focus();
        inpBusqueda.setSelectionRange(inpBusqueda.value.length, inpBusqueda.value.length);
    }

    inpBusqueda.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            formBusqueda.submit();
        }, 400);
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
