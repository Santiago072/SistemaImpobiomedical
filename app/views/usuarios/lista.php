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

        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensajeError)): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- Búsqueda -->
        <div class="mod-search-bar">
            <form action="<?= $basePath ?>" method="GET" class="mod-search-form">
                <input type="hidden" name="module" value="usuarios">
                <input type="hidden" name="action" value="lista">
                <span class="mod-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" name="busqueda" class="mod-search-input" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar usuario por nombre, correo...">
                <?php if ($busqueda): ?>
                <a href="<?= $basePath ?>?module=usuarios&action=lista" class="mod-btn-clear"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
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
                <div style="font-size:12px; color:#64748b; margin-bottom:6px; font-weight:600;">
                    <?= htmlspecialchars($u['cargo'] ?? 'Sin cargo') ?>
                </div>
                <div class="usr-role">
                    <i class="bi bi-shield-check"></i>
                    <?= htmlspecialchars(ucfirst($u['rol'])) ?>
                </div>
                <div class="usr-info">
                    <div><i class="bi bi-upc-scan"></i> Cód: <?= htmlspecialchars($u['codigo'] ?? 'N/A') ?></div>
                    <div><i class="bi bi-envelope"></i> <?= htmlspecialchars($u['correo']) ?></div>
                    <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($u['telefono']) ?></div>
                </div>
                <div class="usr-actions">
                    <button type="button" class="mod-btn-edit" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode($u)) ?>, event)">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button type="button" class="mod-btn-del" onclick="confirmarEliminar(<?= intval($u['id']) ?>, '<?= htmlspecialchars(addslashes($u['nombre'])) ?>', event)">
                        <i class="bi bi-trash-fill"></i>
                    </button>
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
                    <label>Correo Electrónico *</label>
                    <input type="email" name="correo" required maxlength="60" placeholder="juan@empresa.com">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono *</label>
                    <input type="text" name="telefono" required maxlength="15" placeholder="3001234567">
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
                        <option value="usuario">Usuario</option>
                    </select>
                </div>
            </div>
            <div class="imo-form-group">
                <label>Contraseña (opcional)</label>
                <input type="password" name="password" minlength="6" maxlength="30" placeholder="Dejar vacío = usa el documento">
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
                    <label>Correo *</label>
                    <input type="email" id="e_correo" name="correo" required maxlength="60">
                </div>
                <div class="imo-form-group">
                    <label>Teléfono *</label>
                    <input type="text" id="e_telefono" name="telefono" required maxlength="15">
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
                        <option value="usuario">Usuario</option>
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
                    <label>Nueva Contraseña (opcional)</label>
                    <input type="password" name="password" minlength="6" maxlength="30" placeholder="••••••••">
                </div>
            </div>
            <div class="imo-modal-footer">
                <button type="button" class="imo-btn-cancel" onclick="cerrarModal('modal-editar')">Cancelar</button>
                <button type="submit" class="imo-btn-save"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Eliminar -->
<div id="modal-eliminar" class="imo-modal-bg">
    <div class="imo-modal imo-modal-sm">
        <div class="imo-modal-header danger">
            <h3><i class="bi bi-exclamation-triangle-fill"></i> Eliminar Usuario</h3>
            <button onclick="cerrarModal('modal-eliminar')" class="imo-modal-close">&times;</button>
        </div>
        <div class="imo-modal-body">
            <p style="color:#4b5563;">¿Seguro que deseas eliminar a <strong id="nombre-eliminar"></strong>? Esta acción es irreversible.</p>
        </div>
        <div class="imo-modal-footer">
            <button class="imo-btn-cancel" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <a id="link-eliminar" href="#" class="imo-btn-danger"><i class="bi bi-trash-fill"></i> Sí, eliminar</a>
        </div>
    </div>
</div>

<style>
.usr-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;}
.usr-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:24px;text-align:center;position:relative;transition:transform .2s,box-shadow .2s;}
.usr-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.07);}
.usr-status{position:absolute;top:16px;right:16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;}
.status-on{background:#dcfce7;color:#166534;}.status-on i{font-size:7px;color:#16a34a;}
.status-off{background:#fee2e2;color:#991b1b;}.status-off i{font-size:7px;color:#dc2626;}
.usr-avatar{font-size:52px;color:#10757e;margin:10px 0 12px;}
.usr-name{font-size:17px;font-weight:700;color:#0f172a;margin-bottom:6px;}
.usr-role{display:inline-flex;align-items:center;gap:6px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:600;padding:4px 12px;border-radius:20px;margin-bottom:16px;}
.usr-info{font-size:13px;color:#64748b;display:flex;flex-direction:column;gap:6px;margin-bottom:18px;text-align:left;}
.usr-info div{display:flex;align-items:center;gap:8px;} 
.usr-info i{color:#10757e;width:16px;}
.usr-actions{display:flex;gap:8px;justify-content:center;}
.mod-empty-card{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;padding:60px;text-align:center;color:#94a3b8;grid-column:1/-1;}
.mod-empty-card i{font-size:40px;display:block;margin-bottom:12px;}
.mod-pag-info{text-align:center;color:#64748b;font-size:13px;margin-top:12px;}
</style>

<script>
const BASE = '<?= $basePath ?>';

function abrirModalCrear(e) {
    if(e) { e.preventDefault(); e.stopPropagation(); }
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
    document.getElementById('form-editar-usuario').action = BASE + '?module=usuarios&action=editar&id=' + u.id;
    document.getElementById('modal-editar').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function confirmarEliminar(id, nombre, e) {
    if(e) { e.preventDefault(); e.stopPropagation(); }
    document.getElementById('nombre-eliminar').textContent = nombre;
    document.getElementById('link-eliminar').href = BASE + '?module=usuarios&action=eliminar&id=' + id;
    document.getElementById('modal-eliminar').classList.add('open');
    document.body.style.overflow = 'hidden';
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
