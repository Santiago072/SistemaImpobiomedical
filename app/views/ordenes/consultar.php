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

        <div class="mod-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="mod-title"><i class="bi bi-cart-check-fill"></i> Órdenes de Compra</h1>
                <p class="mod-sub">Consulte, clasifique y exporte las órdenes de compra generadas</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div style="background:#fee2e2; border:1.5px solid #ef4444; color:#991b1b; padding:12px 16px; border-radius:10px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-weight:600; font-size:13px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:18px;"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <!-- Pestañas (Tabs) de Estado -->
        <div class="orden-tabs-container" style="display:flex; gap:10px; margin-bottom:20px; border-bottom:2px solid #e2e8f0; padding-bottom:12px;">
            <a href="<?= $basePath ?>?module=ordenes&action=consultar&tab=pendientes" 
               class="tab-btn <?= $tabActual === 'pendientes' ? 'tab-active-pend' : 'tab-inactive' ?>"
               style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:10px; font-weight:700; font-size:13.5px; transition:all .2s;">
                <i class="bi bi-clock-history"></i> Órdenes Pendientes
                <span class="tab-badge" style="background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:12px; font-size:11.5px; font-weight:800; border:1px solid #fde68a;">
                    <?= (int)($conteoPendientes ?? 0) ?>
                </span>
            </a>
            <a href="<?= $basePath ?>?module=ordenes&action=consultar&tab=completadas" 
               class="tab-btn <?= $tabActual === 'completadas' ? 'tab-active-comp' : 'tab-inactive' ?>"
               style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:10px; font-weight:700; font-size:13.5px; transition:all .2s;">
                <i class="bi bi-check-circle-fill"></i> Órdenes Completadas
                <span class="tab-badge" style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:12px; font-size:11.5px; font-weight:800; border:1px solid #bbf7d0;">
                    <?= (int)($conteoCompletadas ?? 0) ?>
                </span>
            </a>
        </div>

        <!-- Filtros y Barra de Búsqueda -->
        <div class="mod-search-bar">
            <form method="POST" action="<?= $basePath ?>?module=ordenes&action=consultar&tab=<?= urlencode($tabActual) ?>"
                  class="mod-search-form" style="display:flex; gap:10px; align-items:center; flex:1; flex-wrap:wrap;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <span class="mod-search-icon"><i class="bi bi-funnel"></i></span>
                <input type="text" name="proveedor" value="<?= htmlspecialchars($busquedaProveedor) ?>"
                       placeholder="Proveedor..." maxlength="60" class="mod-search-input" style="flex:1;">
                <input type="text" name="cotizacion_numero" value="<?= htmlspecialchars($busquedaCotizacion) ?>"
                       placeholder="N° Cot." maxlength="20" class="mod-search-input" style="flex:0.8;">
                <div style="display:flex; align-items:center; gap:5px; flex:1;">
                    <label style="font-size:12px; color:var(--text-soft); font-weight:600;">Desde:</label>
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($busquedaFechaInicio ?? '') ?>"
                           class="mod-search-input" style="border:1.5px solid #e2e8f0; border-radius:9px; padding:8px; width:100%;">
                </div>
                <div style="display:flex; align-items:center; gap:5px; flex:1;">
                    <label style="font-size:12px; color:var(--text-soft); font-weight:600;">Hasta:</label>
                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($busquedaFechaFin ?? '') ?>"
                           class="mod-search-input" style="border:1.5px solid #e2e8f0; border-radius:9px; padding:8px; width:100%;">
                </div>
                <button type="submit" class="imo-btn-save" style="padding:10px 15px; border-radius:9px;">
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px; background:#f8fafc; padding:10px 16px; border-radius:10px; border:1px solid #e2e8f0;">
            <div style="font-size:13px; font-weight:600; color:#475569; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-check-all" style="font-size:18px; color:#10757e;"></i>
                <span id="seleccionados-conteo">0</span> órdenes seleccionadas
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button" onclick="exportarSeleccionadas('pdf')" class="imo-btn-save" style="padding:8px 16px; border-radius:8px; background:#ef4444; color:#fff; border:none; cursor:pointer; font-size:12.5px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Exportar PDF Seleccionadas
                </button>
                <button type="button" onclick="exportarSeleccionadas('excel')" class="imo-btn-save" style="padding:8px 16px; border-radius:8px; background:#10b981; color:#fff; border:none; cursor:pointer; font-size:12.5px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar Excel Seleccionadas
                </button>
            </div>
        </div>

        <!-- Formulario Oculto para Exportación -->
        <form id="form-exportar-ordenes" method="POST" action="" style="display:none;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div id="contenedor-ids-exportar"></div>
        </form>

        <!-- Tabla -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;">
                            <input type="checkbox" id="check-all" title="Seleccionar todas" style="transform:scale(1.25); cursor:pointer;" onchange="toggleCheckAll(this)">
                        </th>
                        <th>P.O.</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Cotización</th>
                        <th>Condiciones Pago</th>
                        <th style="text-align:center;">Estado</th>
                        <th>Generada por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes)): ?>
                        <?php foreach ($ordenes as $ord): 
                            $estOrd = $ord['estado'] ?? 'pendiente';
                            $badgeColor = $estOrd === 'completada' ? '#16a34a' : '#ca8a04';
                            $badgeBg    = $estOrd === 'completada' ? 'rgba(34,197,94,.15)' : 'rgba(234,179,8,.15)';
                        ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="check-orden" value="<?= (int)$ord['id'] ?>" style="transform:scale(1.25); cursor:pointer;" onchange="actualizarConteoSeleccion()">
                            </td>
                            <td><strong style="color:var(--amber);"><?= (int)$ord['numero_po'] ?></strong></td>
                            <td><?= htmlspecialchars($ord['fecha'] ?? '') ?></td>
                            <td><?= htmlspecialchars($ord['proveedor']) ?></td>
                            <td>
                                <?php if (!empty($ord['cotizacion_numero'])): ?>
                                <span style="font-size:12px; background:rgba(45,190,203,.12); padding:2px 8px; border-radius:12px;">
                                    <?= htmlspecialchars($ord['cotizacion_numero']) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($ord['condiciones_pago'] ?? '') ?></td>
                            <td style="text-align:center;">
                                <?php if ($rol === 'admin'): ?>
                                    <select class="estado-orden-select" 
                                            data-id="<?= (int)$ord['id'] ?>"
                                            style="padding:4px 8px; border-radius:6px; font-weight:700; font-size:11.5px; border:1.5px solid <?= $badgeColor ?>; background:<?= $badgeBg ?>; color:<?= $badgeColor ?>; cursor:pointer;"
                                            onchange="cambiarEstadoOrden(this)">
                                        <option value="pendiente" <?= $estOrd === 'pendiente' ? 'selected' : '' ?>>🟡 Pendiente</option>
                                        <option value="completada" <?= $estOrd === 'completada' ? 'selected' : '' ?>>🟢 Completada</option>
                                    </select>
                                <?php else: ?>
                                    <span style="display:inline-block; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px; border:1px solid <?= $badgeColor ?>; background:<?= $badgeBg ?>; color:<?= $badgeColor ?>;">
                                        <?= $estOrd === 'completada' ? '🟢 Completada' : '🟡 Pendiente' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($ord['nombre_usuario'] ?? '—') ?></td>
                            <td>
                                <div class="mod-actions">
                                    <button type="button" class="mod-btn-edit" style="width:auto; padding:0 12px;"
                                        onclick="verOrdenPDF(<?= (int)$ord['id'] ?>, <?= (int)$ord['numero_po'] ?>)">
                                        <i class="bi bi-eye"></i> Ver P.O.
                                    </button>
                                    <a href="<?= $basePath ?>?module=ordenes&action=generar_pdf&id=<?= (int)$ord['id'] ?>&descargar=1"
                                       class="mod-btn-edit" style="width:auto; padding:0 12px; background:rgba(34,197,94,.15); border-color:#22c55e; color:#22c55e;">
                                        <i class="bi bi-download"></i> PDF
                                    </a>
                                    <?php if ($rol === 'admin'): ?>
                                    <form method="POST" action="<?= $basePath ?>?module=ordenes&action=eliminar" style="display:inline;" onsubmit="return confirm('¿Eliminar la P.O. <?= (int)$ord['numero_po'] ?>?')">
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

<style>
.tab-active-pend {
    background: #fef3c7;
    color: #92400e;
    border: 1.5px solid #f59e0b;
}
.tab-active-comp {
    background: #dcfce7;
    color: #166534;
    border: 1.5px solid #22c55e;
}
.tab-inactive {
    background: #ffffff;
    color: #64748b;
    border: 1.5px solid #e2e8f0;
}
.tab-inactive:hover {
    background: #f8fafc;
    color: #1e293b;
}
</style>

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
            <iframe id="orden-frame" class="iframe-frame" src="" style="width:100%; height:75vh; border:none;"></iframe>
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

    fetch('<?= $basePath ?>?module=ordenes&action=cambiar_estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            id: ordenId,
            estado: nuevoEstado,
            csrf_token: csrfToken
        })
    })
    .then(r => r.json())
    .then(data => {
        select.disabled = false;
        if (data.success) {
            select.setAttribute('data-prev', nuevoEstado);
            if (nuevoEstado === 'completada') {
                select.style.borderColor = '#16a34a';
                select.style.backgroundColor = 'rgba(34,197,94,.15)';
                select.style.color = '#16a34a';
            } else {
                select.style.borderColor = '#ca8a04';
                select.style.backgroundColor = 'rgba(234,179,8,.15)';
                select.style.color = '#ca8a04';
            }
            // Recargar para mover la fila de pestaña y actualizar contadores
            window.location.reload();
        } else {
            alert(data.message || 'No se pudo actualizar el estado de la orden.');
            select.value = estadoAnterior;
        }
    })
    .catch(err => {
        select.disabled = false;
        alert('Error de conexión al actualizar el estado.');
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

