<?php
/**
 * Vista: Gestión de Clientes (lista + modal crear + modal editar en 1 sola vista)
 * Variables: $clientes, $busqueda, $paginaActual, $totalPaginas, $total, $mensajeExito, $mensajeError, $csrf_token
 */
$pageTitle = 'Gestión de Clientes';
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
                <h1 class="mod-title"><i class="bi bi-building"></i> Gestión de Clientes</h1>
                <p class="mod-sub"><?= $total ?? 0 ?> cliente(s) registrado(s)</p>
            </div>
            <button class="btn-mod-primary" onclick="abrirModalCrear()">
                <i class="bi bi-plus-lg"></i> Nuevo Cliente
            </button>
        </div>

        <!-- ── Notificaciones ── -->
        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensajeError)): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- ── Búsqueda ── -->
        <div class="mod-search-bar">
            <form method="GET" action="<?= $basePath ?>index.php" class="mod-search-form">
                <input type="hidden" name="module" value="clientes">
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" name="busqueda" placeholder="Buscar por nombre, NIT o municipio..."
                       value="<?= htmlspecialchars($busqueda) ?>" class="mod-search-input">
                <?php if ($busqueda !== ''): ?>
                <a href="<?= $basePath ?>?module=clientes" class="mod-btn-clear"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ── Tabla ── -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>Nombre / Entidad</th>
                        <th>NIT / CC</th>
                        <th>Ubicación</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="8" class="mod-empty">
                            <i class="bi bi-inbox"></i>
                            <p>No se encontraron clientes.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($c['nit']) ?></td>
                        <td><?= htmlspecialchars($c['municipio'] . ', ' . $c['departamento']) ?></td>
                        <td><?= htmlspecialchars($c['nombre_contacto']) ?></td>
                        <td><?= htmlspecialchars($c['telefono']) ?></td>
                        <td><?= htmlspecialchars($c['correo'] ?? '—') ?></td>
                        <td>
                            <span class="mod-badge <?= strtolower($c['estado'] ?? 'activo') === 'activo' ? 'badge-green' : 'badge-red' ?>">
                                <?= htmlspecialchars($c['estado'] ?? 'Activo') ?>
                            </span>
                        </td>
                        <td>
                            <div class="mod-actions">
                                <button class="mod-btn-edit" title="Editar" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($c)) ?>)">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                                <button class="mod-btn-del" title="Eliminar" onclick="confirmarEliminar(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['nombre'])) ?>')">
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
        $pagBaseUrl = $basePath . '?module=clientes' . (!empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : '');
        include __DIR__ . '/../layout/paginacion.php'; 
        ?>

    </main>
</div>

<!-- ════════════════════════════════
     MODAL: CREAR CLIENTE
════════════════════════════════ -->
<div id="modal-crear" class="imo-modal-bg" onclick="cerrarModal('modal-crear', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-person-plus-fill"></i> Nuevo Cliente</h3>
            <button onclick="cerrarModal('modal-crear')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" action="<?= $basePath ?>?module=clientes&action=crear" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Nombre / Entidad *</label>
                    <input type="text" name="nombre" required maxlength="100" placeholder="Razón social o nombre">
                </div>
                <div class="imo-form-group">
                    <label>NIT / CC</label>
                    <input type="text" name="nit" maxlength="25" placeholder="900.123.456-7">
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Departamento</label>
                    <input type="text" name="departamento" maxlength="60" placeholder="Cundinamarca">
                </div>
                <div class="imo-form-group">
                    <label>Municipio</label>
                    <input type="text" name="municipio" maxlength="60" placeholder="Bogotá">
                </div>
            </div>
            <div class="imo-form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" maxlength="100" placeholder="Cra 10 #20-30">
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Nombre del Contacto</label>
                    <input type="text" name="nombre_contacto" maxlength="60" placeholder="Carlos Pérez">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" maxlength="20" placeholder="3001234567">
                </div>
            </div>
            <div class="imo-form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="correo" maxlength="100" placeholder="contacto@empresa.com">
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-crear')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════
     MODAL: EDITAR CLIENTE
════════════════════════════════ -->
<div id="modal-editar" class="imo-modal-bg" onclick="cerrarModal('modal-editar', event)">
    <div class="imo-modal">
        <div class="imo-modal-header">
            <h3><i class="bi bi-pencil-square"></i> Editar Cliente</h3>
            <button onclick="cerrarModal('modal-editar')" class="imo-modal-close">&times;</button>
        </div>
        <form method="POST" id="form-editar-cliente" action="" class="imo-modal-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="_method" value="editar">
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Nombre / Entidad *</label>
                    <input type="text" id="e_nombre" name="nombre" required maxlength="100">
                </div>
                <div class="imo-form-group">
                    <label>NIT / CC</label>
                    <input type="text" id="e_nit" name="nit" maxlength="25">
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Departamento</label>
                    <input type="text" id="e_departamento" name="departamento" maxlength="60">
                </div>
                <div class="imo-form-group">
                    <label>Municipio</label>
                    <input type="text" id="e_municipio" name="municipio" maxlength="60">
                </div>
            </div>
            <div class="imo-form-group">
                <label>Dirección</label>
                <input type="text" id="e_direccion" name="direccion" maxlength="100">
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Nombre del Contacto</label>
                    <input type="text" id="e_nombre_contacto" name="nombre_contacto" maxlength="60">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono</label>
                    <input type="text" id="e_telefono" name="telefono" maxlength="20">
                </div>
            </div>
            <div class="imo-form-row">
                <div class="imo-form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" id="e_correo" name="correo" maxlength="100">
                </div>
                <div class="imo-form-group">
                    <label>Estado</label>
                    <select id="e_estado" name="estado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Confirmar Eliminar -->
<div id="modal-eliminar" class="imo-modal-bg" onclick="cerrarModal('modal-eliminar', event)">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3><i class="bi bi-exclamation-triangle-fill"></i> Confirmar eliminación</h3>
            <button onclick="cerrarModal('modal-eliminar')" class="imo-modal-close">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p style="color:#4b5563;">¿Estás seguro de eliminar a <strong id="nombre-eliminar"></strong>? Esta acción no se puede deshacer.</p>
        </div>
        <div class="imo-modal-footer">
            <button class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <a id="link-eliminar" href="#" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Eliminar</a>
        </div>
    </div>
</div>



<script>
const BASE = '<?= $basePath ?>';

function abrirModalCrear() {
    document.getElementById('modal-crear').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function abrirModalEditar(data) {
    document.getElementById('e_nombre').value          = data.nombre || '';
    document.getElementById('e_nit').value             = data.nit || '';
    document.getElementById('e_departamento').value    = data.departamento || '';
    document.getElementById('e_municipio').value       = data.municipio || '';
    document.getElementById('e_direccion').value       = data.direccion || '';
    document.getElementById('e_nombre_contacto').value = data.nombre_contacto || '';
    document.getElementById('e_telefono').value        = data.telefono || '';
    document.getElementById('e_correo').value          = data.correo || '';
    document.getElementById('e_estado').value          = data.estado || 'activo';
    document.getElementById('form-editar-cliente').action = BASE + '?module=clientes&action=editar&id=' + data.id;
    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarEliminar(id, nombre) {
    document.getElementById('nombre-eliminar').textContent = nombre;
    document.getElementById('link-eliminar').href = BASE + '?module=clientes&action=eliminar&id=' + id;
    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id, evento) {
    if (evento && evento.target !== document.getElementById(id)) return;
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') {
    ['modal-crear','modal-editar','modal-eliminar'].forEach(id => {
        document.getElementById(id)?.classList.remove('open');
    });
    document.body.style.overflow = 'auto';
}});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
