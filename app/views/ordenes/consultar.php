<?php
/**
 * Vista: Consultar Órdenes de Compra — Sistema Impobiomedical
 * Variables: $ordenes, $csrf_token, $paginaActual, $totalPaginas, $tabActual,
 *            $conteoPendientes, $conteoCompletadas,
 *            $busquedaProveedor, $busquedaCotizacion, $busquedaFechaInicio, $busquedaFechaFin
 */
$pageTitle = 'Órdenes de Compra';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';

$rol = $_SESSION['rol'] ?? 'usuario';
$tabActual = $tabActual ?? 'pendientes';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-cart-check-fill"></i> Órdenes de Compra</h1>
                <p class="mod-sub">Consulte, clasifique y exporte las órdenes de compra generadas</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="mod-alert mod-alert-ok">
            <i class="bi bi-check-circle-fill"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="mod-alert mod-alert-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <!-- Pestañas (Tabs) de Estado -->
        <div class="orden-tabs-container">
            <a href="<?= $basePath ?>?module=ordenes&action=consultar&tab=pendientes" 
               class="tab-btn <?= $tabActual === 'pendientes' ? 'tab-active-pend' : 'tab-inactive' ?>">
                <i class="bi bi-clock-history"></i> Órdenes Pendientes
                <span class="tab-badge tab-badge-pend">
                    <?= (int)($conteoPendientes ?? 0) ?>
                </span>
            </a>
            <a href="<?= $basePath ?>?module=ordenes&action=consultar&tab=completadas" 
               class="tab-btn <?= $tabActual === 'completadas' ? 'tab-active-comp' : 'tab-inactive' ?>">
                <i class="bi bi-check-circle-fill"></i> Órdenes Completadas
                <span class="tab-badge tab-badge-comp">
                    <?= (int)($conteoCompletadas ?? 0) ?>
                </span>
            </a>
        </div>

        <!-- Filtros y Barra de Búsqueda -->
        <div class="mod-search-bar">
            <form method="POST" action="<?= $basePath ?>?module=ordenes&action=consultar&tab=<?= urlencode($tabActual) ?>"
                  class="mod-search-form orden-search-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <span class="mod-search-icon"><i class="bi bi-funnel"></i></span>
                <input type="text" name="proveedor" value="<?= htmlspecialchars($busquedaProveedor) ?>"
                       placeholder="Proveedor..." maxlength="60" class="mod-search-input">
                <input type="text" name="cotizacion_numero" value="<?= htmlspecialchars($busquedaCotizacion) ?>"
                       placeholder="N° Cot." maxlength="20" class="mod-search-input">
                <div class="filter-input-field">
                    <label class="filter-label-text">Desde:</label>
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($busquedaFechaInicio ?? '') ?>"
                           class="mod-search-input orden-search-input-date">
                </div>
                <div class="filter-input-field">
                    <label class="filter-label-text">Hasta:</label>
                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($busquedaFechaFin ?? '') ?>"
                           class="mod-search-input orden-search-input-date">
                </div>
                <button type="submit" class="imo-btn-save orden-search-btn">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <?php if ($busquedaProveedor || $busquedaCotizacion || !empty($busquedaFechaInicio) || !empty($busquedaFechaFin)): ?>
                <a href="<?= $basePath ?>?module=ordenes&action=consultar&tab=<?= urlencode($tabActual) ?>&limpiar=1" class="mod-btn-clear" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Barra de Acciones de Exportación por Selección -->
        <div class="export-selection-bar">
            <div class="export-count-text">
                <i class="bi bi-check-all"></i>
                <span id="seleccionados-conteo">0</span> órdenes seleccionadas
            </div>
            <div class="header-actions-wrap">
                <button type="button" onclick="exportarSeleccionadas('pdf')" class="btn-export-pdf">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Exportar PDF Seleccionadas
                </button>
                <button type="button" onclick="exportarSeleccionadas('excel')" class="btn-export-excel">
                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar Excel Seleccionadas
                </button>
            </div>
        </div>

        <!-- Formulario Oculto para Exportación -->
        <form id="form-exportar-ordenes" method="POST" action="" class="form-hidden-action">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div id="contenedor-ids-exportar"></div>
        </form>

        <!-- Tabla -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th class="text-center col-check">
                            <input type="checkbox" id="check-all" title="Seleccionar todas" onchange="toggleCheckAll(this)">
                        </th>
                        <th>P.O.</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Cotización</th>
                        <th>Condiciones Pago</th>
                        <th class="text-center">Estado</th>
                        <th>Generada por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes)): ?>
                        <?php foreach ($ordenes as $ord): 
                            $estOrd = $ord['estado'] ?? 'pendiente';
                        ?>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="check-orden" value="<?= (int)$ord['id'] ?>" onchange="actualizarConteoSeleccion()">
                            </td>
                            <td><strong><?= (int)$ord['numero_po'] ?></strong></td>
                            <td><?= htmlspecialchars($ord['fecha'] ?? '') ?></td>
                            <td>
                                <strong><?= htmlspecialchars($ord['proveedor']) ?></strong>
                                <?php $ep = $ord['estado_proveedor'] ?? 'nuevo'; ?>
                                <div style="margin-top: 3px;">
                                    <?php if ($ep === 'registrado'): ?>
                                        <span class="mod-badge badge-green" style="font-size: 10.5px; padding: 2px 7px;">
                                            <i class="bi bi-check-circle-fill"></i> Registrado
                                        </span>
                                    <?php else: ?>
                                        <span class="mod-badge badge-gold" style="font-size: 10.5px; padding: 2px 7px;">
                                            <i class="bi bi-plus-circle"></i> Nuevo
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($ord['cotizacion_numero'])): ?>
                                <span class="tag-code">
                                    <?= htmlspecialchars($ord['cotizacion_numero']) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($ord['condiciones_pago'] ?? '') ?></td>
                            <td class="text-center">
                                <?php if ($rol === 'admin' || $rol === 'compras'): ?>
                                    <select class="estado-orden-select <?= $estOrd === 'completada' ? 'badge-green' : 'badge-gold' ?>" 
                                            data-id="<?= (int)$ord['id'] ?>"
                                            onchange="cambiarEstadoOrden(this)">
                                        <option value="pendiente" <?= $estOrd === 'pendiente' ? 'selected' : '' ?>>🟡 Pendiente</option>
                                        <option value="completada" <?= $estOrd === 'completada' ? 'selected' : '' ?>>🟢 Completada</option>
                                    </select>
                                <?php else: ?>
                                    <span class="mod-badge <?= $estOrd === 'completada' ? 'badge-green' : 'badge-gold' ?>">
                                        <?= $estOrd === 'completada' ? '🟢 Completada' : '🟡 Pendiente' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($ord['nombre_usuario'] ?? '—') ?></td>
                            <td>
                                <div class="mod-actions">
                                    <button type="button" class="btn-orden-view"
                                        onclick="verOrdenPDF(<?= (int)$ord['id'] ?>, <?= (int)$ord['numero_po'] ?>)">
                                        <i class="bi bi-eye"></i> Ver P.O.
                                    </button>
                                    <?php if (in_array($rol, ['admin', 'compras'], true)): ?>
                                    <form method="POST" action="<?= $basePath ?>?module=ordenes&action=eliminar" class="form-inline-action" onsubmit="return confirm('¿Eliminar la P.O. <?= (int)$ord['numero_po'] ?>?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                        <input type="hidden" name="id" value="<?= (int)$ord['id'] ?>">
                                        <button type="submit" class="mod-btn-del" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="9" class="mod-empty">
                            <i class="bi bi-cart-x"></i>
                            <p>No hay órdenes de compra en la sección <?= htmlspecialchars($tabActual) ?>.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pagBaseUrl = $basePath . '?module=ordenes&action=consultar&tab=' . urlencode($tabActual);
        include __DIR__ . '/../layout/paginacion.php';
        ?>

    </main>
</div>

<!-- Modal visor PDF Orden -->
<div id="modal-orden-viewer" class="modal-pdf-viewer">
    <div class="modal-pdf-contenido">
        <div class="modal-pdf-header">
            <h3><i class="bi bi-file-earmark-pdf"></i> Orden de Compra P.O. <span id="orden-titulo"></span></h3>
            <div class="modal-pdf-acciones">
                <a id="btn-descargar-orden" href="#" class="btn-descargar-pdf" download>
                    <i class="bi bi-download"></i> Descargar
                </a>
                <button type="button" class="btn-cerrar-pdf" onclick="cerrarOrden()">
                    <i class="bi bi-x-lg"></i> Cerrar
                </button>
            </div>
        </div>
        <div class="pdf-container mt-10">
            <iframe id="orden-frame" class="modal-orden-iframe" src=""></iframe>
        </div>
    </div>
</div>

<script>
function toggleCheckAll(master) {
    const checks = document.querySelectorAll('.check-orden');
    checks.forEach(c => c.checked = master.checked);
    actualizarConteoSeleccion();
}

function actualizarConteoSeleccion() {
    const marcados = document.querySelectorAll('.check-orden:checked').length;
    document.getElementById('seleccionados-conteo').textContent = marcados;
    
    const todos = document.querySelectorAll('.check-orden').length;
    const master = document.getElementById('check-all');
    if (master) {
        master.checked = todos > 0 && marcados === todos;
    }
}

function exportarSeleccionadas(tipo) {
    const marcados = Array.from(document.querySelectorAll('.check-orden:checked')).map(c => c.value);
    if (marcados.length === 0) {
        alert('⚠️ Debe seleccionar al menos una orden de compra mediante las casillas para generar el reporte.');
        return;
    }

    const form = document.getElementById('form-exportar-ordenes');
    const container = document.getElementById('contenedor-ids-exportar');
    container.innerHTML = '';

    marcados.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        container.appendChild(input);
    });

    if (tipo === 'pdf') {
        form.action = '<?= $basePath ?>?module=ordenes&action=exportarPdf';
    } else {
        form.action = '<?= $basePath ?>?module=ordenes&action=exportarExcel';
    }

    form.submit();
}

function cambiarEstadoOrden(select) {
    const ordenId = select.getAttribute('data-id');
    const nuevoEstado = select.value;
    const csrfToken = '<?= htmlspecialchars($csrf_token) ?>';

    const estadoAnterior = select.getAttribute('data-prev') || (nuevoEstado === 'completada' ? 'pendiente' : 'completada');

    select.disabled = true;
    select.style.opacity = '0.5';

    const formData = new FormData();
    formData.append('id', ordenId);
    formData.append('estado', nuevoEstado);
    formData.append('csrf_token', csrfToken);

    fetch('<?= $basePath ?>?module=ordenes&action=cambiar_estado', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(r => {
        if (!r.ok) {
            return r.text().then(t => {
                let errJson;
                try { errJson = JSON.parse(t); } catch(e) {}
                throw new Error((errJson && errJson.message) ? errJson.message : 'HTTP ' + r.status);
            });
        }
        return r.json();
    })
    .then(data => {
        select.disabled = false;
        select.style.opacity = '1';
        if (data.success) {
            select.setAttribute('data-prev', nuevoEstado);
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'No se pudo actualizar el estado de la orden.'));
            select.value = estadoAnterior;
        }
    })
    .catch(err => {
        select.disabled = false;
        select.style.opacity = '1';
        alert('Error al actualizar el estado: ' + err.message);
        select.value = estadoAnterior;
    });
}

function verOrdenPDF(id, po) {
    const modal = document.getElementById('modal-orden-viewer');
    const frame = document.getElementById('orden-frame');
    document.getElementById('orden-titulo').textContent = po;
    const url = '<?= $basePath ?>?module=ordenes&action=generar_pdf&id=' + id;
    frame.src  = url;
    document.getElementById('btn-descargar-orden').href = url + '&descargar=1';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function cerrarOrden() {
    document.getElementById('modal-orden-viewer').style.display = 'none';
    document.getElementById('orden-frame').src = '';
    document.body.style.overflow = 'auto';
}
window.addEventListener('click', e => {
    if (e.target === document.getElementById('modal-orden-viewer')) cerrarOrden();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarOrden(); });
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>

