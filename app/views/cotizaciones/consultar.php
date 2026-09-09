<?php
/**
 * Vista: Consultar cotizaciones
 * Variables: $cotizaciones, $csrf_token, $mensajeError,
 *            $busquedaFecha, $busquedaCliente, $busquedaNumero,
 *            $paginaActual, $totalPaginas, $urlBase
 */
$pageTitle = 'Consultar Cotización';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-file-earmark-text-fill"></i> Consultar Cotizaciones</h1>
                <p class="mod-sub">Filtre y visualice cotizaciones generadas</p>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div style="background:#fee2e2; border:1.5px solid #ef4444; color:#991b1b; padding:12px 16px; border-radius:10px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-weight:600; font-size:13px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:18px;"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <?php if ($mensajeError): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- Filtros de búsqueda estilo Panel -->
        <div class="mod-search-bar">
            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=consultar" class="mod-search-form orden-search-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <span class="mod-search-icon"><i class="bi bi-funnel"></i></span>
                <label class="cot-date-label">Desde</label>
                <input type="date" name="fecha_desde" value="<?= htmlspecialchars($busquedaFechaDesde ?? '') ?>" class="mod-search-input cot-filter-date" title="Fecha desde" onchange="this.form.submit()">
                <label class="cot-date-label">Hasta</label>
                <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($busquedaFechaHasta ?? '') ?>" class="mod-search-input cot-filter-date" title="Fecha hasta" onchange="this.form.submit()">
                <input type="text" name="nombre_cliente" value="<?= htmlspecialchars($busquedaCliente) ?>" placeholder="Buscar por cliente..." maxlength="60" class="mod-search-input cot-filter-client">
                <input type="text" name="numero_cotizacion" value="<?= htmlspecialchars($busquedaNumero) ?>" placeholder="Número cotización..." maxlength="20" class="mod-search-input cot-filter-num">
                <select name="estado_comercial" class="mod-search-input cot-filter-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" <?= ($busquedaEstado ?? '') === 'pendiente' ? 'selected' : '' ?>>🟡 Pendientes</option>
                    <option value="concluida" <?= ($busquedaEstado ?? '') === 'concluida' ? 'selected' : '' ?>>🟢 Concluidas</option>
                    <option value="descartada" <?= ($busquedaEstado ?? '') === 'descartada' ? 'selected' : '' ?>>🔴 Descartadas</option>
                </select>
                
                <button type="submit" class="imo-btn-save orden-search-btn"><i class="bi bi-search"></i> Buscar</button>
                <?php 
                $hayFiltros = (!empty($busquedaFechaDesde) || !empty($busquedaFechaHasta) || !empty($busquedaFecha) || !empty($busquedaCliente) || !empty($busquedaNumero) || !empty($busquedaEstado));
                if ($hayFiltros): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar&limpiar=1" class="mod-btn-clear" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>
        <?php if (($busquedaFechaDesde ?? '') || ($busquedaFechaHasta ?? '')): ?>
        <div style="font-size:12.5px; color:#475569; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-calendar-range" style="color:#10757e;"></i>
            <span>Mostrando cotizaciones
                <?= $busquedaFechaDesde ? 'desde <strong>' . htmlspecialchars($busquedaFechaDesde) . '</strong>' : '' ?>
                <?= $busquedaFechaHasta ? ' hasta <strong>' . htmlspecialchars($busquedaFechaHasta) . '</strong>' : '' ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Tabla de resultados -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>N° Cotización</th>
                        <th>Fecha</th>
                        <th>Cliente / Entidad</th>
                        <th>Ciudad</th>
                        <th class="text-center">Estado Comercial</th>
                        <th class="text-center">Entrega</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cotizaciones)): ?>
                        <?php foreach ($cotizaciones as $cot): 
                            $estCom = $cot['estado_comercial'] ?? 'pendiente';
                            $badgeClass = 'badge-gold';
                            if ($estCom === 'concluida') {
                                $badgeClass = 'badge-green';
                            } elseif ($estCom === 'descartada') {
                                $badgeClass = 'badge-red';
                            }
                            
                            $estEnt = $cot['estado_entrega'] ?? 'pendiente';
                            $entClass = 'badge-gold';
                            if ($estEnt === 'en_transito') {
                                $entClass = 'badge-blue';
                            } elseif ($estEnt === 'entregado') {
                                $entClass = 'badge-green';
                            }
                            
                            // Calcular días de entrega si ya fue entregado
                            $diasEntregaTxt = '';
                            if ($estEnt === 'entregado' && !empty($cot['fecha_entrega'])) {
                                $fBase = !empty($cot['fecha_cambio_estado']) ? $cot['fecha_cambio_estado'] : $cot['fecha_creacion'];
                                $tB = strtotime($fBase);
                                $tE = strtotime($cot['fecha_entrega']);
                                if ($tB && $tE && $tE >= $tB) {
                                    $diasDiff = (int)round(($tE - $tB) / 86400);
                                    $diasEntregaTxt = $diasDiff === 1 ? ' (1 día)' : " ($diasDiff días)";
                                }
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cot['numero_cotizacion'] ?: 'Sin número') ?></strong></td>
                            <td><?= htmlspecialchars($cot['fecha_creacion']) ?></td>
                            <td><?= htmlspecialchars($cot['cliente_nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cot['cliente_ciudad'] ?? '') ?></td>
                            <td class="text-center">
                                <select class="estado-comercial-select <?= $badgeClass ?>" 
                                        data-id="<?= (int)$cot['id'] ?>"
                                        onchange="cambiarEstadoComercial(this)">
                                    <option value="pendiente" <?= $estCom === 'pendiente' ? 'selected' : '' ?>>🟡 Pendiente</option>
                                    <option value="concluida" <?= $estCom === 'concluida' ? 'selected' : '' ?>>🟢 Concluida</option>
                                    <option value="descartada" <?= $estCom === 'descartada' ? 'selected' : '' ?>>🔴 Descartada</option>
                                </select>
                                <div class="cot-fecha-cambio-lbl">
                                    <?php if (!empty($cot['fecha_cambio_estado']) && $estCom !== 'pendiente'): ?>
                                        <i class="bi bi-clock-history"></i> <?= date('d/m/Y H:i', strtotime($cot['fecha_cambio_estado'])) ?>
                                    <?php else: ?>
                                        <span class="cot-entrega-pendiente">Sin cambio</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <select class="estado-entrega-select <?= $entClass ?>" 
                                        data-id="<?= (int)$cot['id'] ?>"
                                        onchange="cambiarEstadoEntrega(this)">
                                    <option value="pendiente" <?= $estEnt === 'pendiente' ? 'selected' : '' ?>>🟡 Pendiente</option>
                                    <option value="en_transito" <?= $estEnt === 'en_transito' ? 'selected' : '' ?>>🔵 En Tránsito</option>
                                    <option value="entregado" <?= $estEnt === 'entregado' ? 'selected' : '' ?>>🟢 Entregado</option>
                                </select>
                                <div class="cot-tiempo-entrega-lbl">
                                    <?php if ($estEnt === 'entregado' && !empty($cot['fecha_entrega'])): ?>
                                        <i class="bi bi-check2-all"></i> <?= date('d/m/Y', strtotime($cot['fecha_entrega'])) ?><?= $diasEntregaTxt ?>
                                    <?php elseif ($estEnt === 'en_transito'): ?>
                                        <span class="cot-entrega-camino"><i class="bi bi-truck"></i> En camino</span>
                                    <?php else: ?>
                                        <span class="cot-entrega-pendiente">Por despachar</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="mod-actions">
                                    <?php if (!empty($cot['numero_cotizacion'])): ?>
                                    <button type="button" class="btn-orden-view"
                                        onclick="verPDF(<?= (int)$cot['id'] ?>, '<?= htmlspecialchars(addslashes($cot['numero_cotizacion'])) ?>', '<?= htmlspecialchars(addslashes($cot['nombre_cliente'] ?? $cot['cliente_nombre'] ?? '')) ?>')">
                                        <i class="bi bi-eye"></i> Ver PDF
                                    </button>
                                    <a href="<?= $basePath ?>?module=cotizaciones&action=exportar_excel&id=<?= (int)$cot['id'] ?>&ver=<?= urlencode($cot['numero_cotizacion']) ?>" 
                                       class="btn-excel-cot" title="Descargar Cotización en Excel (Rápido, sin imágenes)">
                                        <i class="bi bi-file-earmark-excel-fill"></i> Excel
                                    </a>
                                    <button type="button" class="btn-respaldo-view"
                                        onclick="window.location.href='<?= $basePath ?>?module=cotizaciones&action=ver_respaldo&id=<?= (int)$cot['id'] ?>&numero=<?= urlencode($cot['numero_cotizacion']) ?>'" title="Hoja de Respaldo Proveedores">
                                        <i class="bi bi-file-earmark-spreadsheet"></i> Respaldo
                                    </button>
                                    <?php 
                                    $esRevision = (strpos($cot['numero_cotizacion'], '_') !== false);
                                    ?>
                                    <button type="button" class="btn-modificar-cot"
                                        onclick="abrirModalEdicion(<?= (int)$cot['id'] ?>, '<?= htmlspecialchars(addslashes($cot['numero_cotizacion'])) ?>', <?= $esRevision ? 'true' : 'false' ?>)" title="Ajustar o crear nueva versión">
                                        <i class="bi bi-pencil-square"></i> Modificar
                                    </button>
                                     <?php if ($estCom === 'pendiente'): ?>
                                     <button type="button" class="btn-accion-orden btn-orden-cot-enabled" data-cotizacion="<?= htmlspecialchars($cot['numero_cotizacion']) ?>"
                                         onclick="window.location.href='<?= $basePath ?>?module=ordenes&action=seleccionar_items&id=<?= (int)$cot['id'] ?>&cotizacion=<?= urlencode($cot['numero_cotizacion']) ?>'"
                                         title="Generar Orden de Compra">
                                         <i class="bi bi-cart-plus-fill"></i> Orden
                                     </button>
                                     <?php else: ?>
                                     <button type="button" class="btn-accion-orden btn-orden-cot-disabled" data-cotizacion="<?= htmlspecialchars($cot['numero_cotizacion']) ?>" disabled
                                         title="No disponible: la cotización está <?= htmlspecialchars($estCom) ?>">
                                         <i class="bi bi-cart-x"></i> Orden
                                     </button>
                                     <?php endif; ?>
                                     <?php if (in_array($_SESSION['rol'] ?? '', ['admin', 'compras'], true)): ?>
                                     <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=eliminar" class="form-inline-action" onsubmit="return confirm('¿Eliminar la cotización <?= htmlspecialchars($cot['numero_cotizacion']) ?>?')">
                                         <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                         <input type="hidden" name="id" value="<?= (int)$cot['id'] ?>">
                                         <button type="submit" class="mod-btn-del" title="Eliminar"><i class="bi bi-trash3-fill"></i></button>
                                     </form>
                                     <?php endif; ?>
                                    <?php else: ?>
                                    <span class="mod-badge badge-red">No generado</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php elseif (isset($_GET['buscando'])): ?>
                    <tr>
                        <td colspan="8" class="mod-empty">
                            <i class="bi bi-search"></i>
                            <p>No se encontraron cotizaciones.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="mod-empty">
                            <i class="bi bi-funnel"></i>
                            <p>Use los filtros de arriba para buscar cotizaciones.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php 
        $pagBaseUrl = $basePath . '?module=cotizaciones&action=consultar';
        if (!empty($_GET['buscando'])) $pagBaseUrl .= '&buscando=1';
        if (!empty($_GET['busqueda_cliente'])) $pagBaseUrl .= '&busqueda_cliente=' . urlencode($_GET['busqueda_cliente']);
        if (!empty($_GET['fecha_inicio'])) $pagBaseUrl .= '&fecha_inicio=' . urlencode($_GET['fecha_inicio']);
        if (!empty($_GET['fecha_fin'])) $pagBaseUrl .= '&fecha_fin=' . urlencode($_GET['fecha_fin']);
        include __DIR__ . '/../layout/paginacion.php'; 
        ?>

    </main>
</div>

<!-- Modal visor PDF -->
<div id="modal-pdf-viewer" class="modal-pdf-viewer">
    <div class="modal-pdf-contenido">
        <div class="modal-pdf-header">
            <h3><i class="bi bi-file-earmark-pdf"></i> Cotización: <span id="pdf-titulo"></span></h3>
            <div class="modal-pdf-acciones">
                <a id="btn-descargar" href="#" class="btn-descargar-pdf" download>
                    <i class="bi bi-download"></i> Descargar
                </a>
                <button type="button" class="btn-cerrar-pdf" onclick="cerrarPDF()">
                    <i class="bi bi-x-lg"></i> Cerrar
                </button>
            </div>
        </div>
        <div class="pdf-container mt-10">
            <iframe id="pdf-frame" class="iframe-frame pdf-viewer-frame" src=""></iframe>
            <div id="pdf-error" class="pdf-error d-none">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <h4>No se pudo cargar el PDF</h4>
                <p>El archivo no está disponible o ha sido movido.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Opciones: Ajustar vs Modificar -->
<div id="modal-opciones-modificar" class="modal-opciones-edicion modal-hidden">
    <div class="modal-opciones-contenido">
        <div class="modal-opciones-header">
            <div class="modal-opciones-icon-wrap">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h3 class="modal-opciones-titulo">¿Qué deseas hacer con la cotización?</h3>
                <p class="modal-opciones-sub">Cotización: <strong id="modal-edit-numero"></strong></p>
            </div>
            <button type="button" class="modal-opciones-cerrar" onclick="cerrarModalEdicion()" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="modal-opciones-body">
            <div class="modal-opcion-card" id="card-ajustar" onclick="irAjustar()">
                <div class="modal-opcion-icono modal-opcion-ajustar">
                    <i class="bi bi-wrench-adjustable"></i>
                </div>
                <div class="modal-opcion-info">
                    <h4>Ajustar Cotización</h4>
                    <p>Corrige errores directamente en esta cotización <strong>manteniendo el mismo número</strong>. Ideal para corregir precios, cantidades o datos del cliente.</p>
                </div>
                <div class="modal-opcion-arrow">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
            <div class="modal-opcion-divider"><span>ó</span></div>
            <div class="modal-opcion-card" id="card-modificar" onclick="irModificar()">
                <div class="modal-opcion-icono modal-opcion-modificar">
                    <i class="bi bi-files"></i>
                </div>
                <div class="modal-opcion-info">
                    <h4>Nueva Versión / Revisión</h4>
                    <p>Crea una cotización derivada (ej: <strong>_01</strong>, <strong>_02</strong>) conservando la original intacta en el historial comercial.</p>
                </div>
                <div class="modal-opcion-arrow">
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>
        <div class="modal-opciones-footer">
            <button type="button" class="btn-opciones-cancelar" onclick="cerrarModalEdicion()">
                <i class="bi bi-x-circle"></i> Cancelar
            </button>
        </div>
    </div>
</div>

<script>
function verPDF(id, numero, cliente) {
    const modal   = document.getElementById('modal-pdf-viewer');
    const frame   = document.getElementById('pdf-frame');
    const titulo  = document.getElementById('pdf-titulo');
    const btnDesc = document.getElementById('btn-descargar');
    const err     = document.getElementById('pdf-error');

    err.style.display   = 'none';
    frame.style.display = 'block';
    titulo.textContent  = numero + ' - ' + cliente;
    frame.src           = '<?= $basePath ?>?module=cotizaciones&action=generar_pdf&id=' + id + '&ver=' + encodeURIComponent(numero);
    btnDesc.href        = '<?= $basePath ?>?module=cotizaciones&action=generar_pdf&id=' + id + '&ver=' + encodeURIComponent(numero) + '&descargar=1';
    btnDesc.setAttribute('download', 'cotizacion_' + numero + '.pdf');
    modal.style.display          = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarPDF() {
    document.getElementById('modal-pdf-viewer').style.display = 'none';
    document.getElementById('pdf-frame').src                  = '';
    document.body.style.overflow                               = 'auto';
}

function cambiarEstadoComercial(select) {
    const id = select.getAttribute('data-id');
    const nuevoEstado = select.value;
    const csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';
    const fila = select.closest('tr');
    const btnOrden = fila ? fila.querySelector('.btn-accion-orden') : null;

    // Colores dinámicos
    const estilos = {
        'pendiente': { color: '#ca8a04', bg: 'rgba(234,179,8,.15)' },
        'concluida': { color: '#16a34a', bg: 'rgba(34,197,94,.15)' },
        'descartada': { color: '#dc2626', bg: 'rgba(239,68,68,.15)' }
    };

    select.disabled = true;
    select.style.opacity = '0.5';

    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado_comercial', nuevoEstado);
    formData.append('csrf_token', csrfToken);

    fetch('<?= $basePath ?>?module=cotizaciones&action=cambiar_estado', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        select.disabled = false;
        select.style.opacity = '1';
        if (d.status === 'success') {
            // Actualizar clases de badge
            select.classList.remove('badge-gold', 'badge-green', 'badge-red');
            if (nuevoEstado === 'concluida') {
                select.classList.add('badge-green');
            } else if (nuevoEstado === 'descartada') {
                select.classList.add('badge-red');
            } else {
                select.classList.add('badge-gold');
            }

            const conf = estilos[nuevoEstado] || estilos['pendiente'];
            select.style.setProperty('border-color', conf.color, 'important');
            select.style.setProperty('color', conf.color, 'important');
            select.style.setProperty('background', conf.bg, 'important');

            // Actualizar fecha debajo del select de estado comercial
            const lblFecha = fila ? fila.querySelector('.cot-fecha-cambio-lbl') : null;
            if (lblFecha) {
                if (d.fecha_cambio) {
                    lblFecha.innerHTML = `<i class="bi bi-clock-history"></i> ${d.fecha_cambio}`;
                } else {
                    lblFecha.innerHTML = `<span style="color:#94a3b8;">Sin cambio</span>`;
                }
            }

            // Actualizar reactivamente el botón de Orden en la fila
            if (btnOrden) {
                const numCot = btnOrden.getAttribute('data-cotizacion') || '';
                if (nuevoEstado === 'pendiente') {
                    btnOrden.disabled = false;
                    btnOrden.style.background = 'rgba(34,197,94,.15)';
                    btnOrden.style.color = '#22c55e';
                    btnOrden.style.borderColor = '#22c55e';
                    btnOrden.style.cursor = 'pointer';
                    btnOrden.style.opacity = '1';
                    btnOrden.title = 'Generar Orden de Compra';
                    btnOrden.innerHTML = '<i class="bi bi-cart-plus-fill"></i> Orden';
                    btnOrden.onclick = function() {
                        window.location.href = '<?= $basePath ?>?module=ordenes&action=seleccionar_items&cotizacion=' + encodeURIComponent(numCot);
                    };
                } else {
                    btnOrden.disabled = true;
                    btnOrden.style.background = '#f1f5f9';
                    btnOrden.style.color = '#94a3b8';
                    btnOrden.style.borderColor = '#cbd5e1';
                    btnOrden.style.cursor = 'not-allowed';
                    btnOrden.style.opacity = '0.7';
                    btnOrden.title = 'No disponible: la cotización está ' + nuevoEstado;
                    btnOrden.innerHTML = '<i class="bi bi-cart-x"></i> Orden';
                    btnOrden.onclick = null;
                }
            }
        } else {
            alert('Error: ' + (d.message || 'No se pudo actualizar'));
            window.location.reload();
        }
    })
    .catch(err => {
        select.disabled = false;
        select.style.opacity = '1';
        alert('Error de conexión al actualizar estado.');
    });
}

function cambiarEstadoEntrega(select) {
    const id = select.getAttribute('data-id');
    const nuevoEstado = select.value;
    const csrfToken = '<?= htmlspecialchars($csrf_token ?? '') ?>';
    const fila = select.closest('tr');
    const lblEntrega = fila ? fila.querySelector('.cot-tiempo-entrega-lbl') : null;

    const estilosEntrega = {
        'pendiente':   { color: '#ca8a04', bg: 'rgba(234,179,8,.15)' },
        'en_transito': { color: '#2563eb', bg: 'rgba(37,99,235,.15)' },
        'entregado':   { color: '#16a34a', bg: 'rgba(34,197,94,.15)' }
    };

    select.disabled = true;
    select.style.opacity = '0.5';

    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado_entrega', nuevoEstado);
    formData.append('csrf_token', csrfToken);

    fetch('<?= $basePath ?>?module=cotizaciones&action=cambiar_entrega', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        select.disabled = false;
        select.style.opacity = '1';
        if (d.status === 'success') {
            // Actualizar clases de badge
            select.classList.remove('badge-gold', 'badge-blue', 'badge-green');
            if (nuevoEstado === 'en_transito') {
                select.classList.add('badge-blue');
            } else if (nuevoEstado === 'entregado') {
                select.classList.add('badge-green');
            } else {
                select.classList.add('badge-gold');
            }

            const conf = estilosEntrega[nuevoEstado] || estilosEntrega['pendiente'];
            select.style.setProperty('border-color', conf.color, 'important');
            select.style.setProperty('color', conf.color, 'important');
            select.style.setProperty('background', conf.bg, 'important');

            if (lblEntrega) {
                if (nuevoEstado === 'entregado') {
                    const diasTxt = d.dias_entrega !== null ? (d.dias_entrega === 1 ? ' (1 día)' : ` (${d.dias_entrega} días)`) : '';
                    lblEntrega.innerHTML = `<i class="bi bi-check2-all"></i> ${d.fecha_entrega || ''}${diasTxt}`;
                    lblEntrega.style.color = '#15803d';
                } else if (nuevoEstado === 'en_transito') {
                    lblEntrega.innerHTML = `<span style="color:#2563eb;"><i class="bi bi-truck"></i> En camino</span>`;
                } else {
                    lblEntrega.innerHTML = `<span style="color:#94a3b8;">Por despachar</span>`;
                }
            }
        } else {
            alert('Error: ' + (d.message || 'No se pudo actualizar'));
            window.location.reload();
        }
    })
    .catch(err => {
        select.disabled = false;
        select.style.opacity = '1';
        alert('Error de conexión al actualizar entrega.');
    });
}

// ── Modal Opciones: Ajustar vs Modificar ─────────────────────────────────────
let _editId = null;
let _editNumero = null;
let _esRevision = false;
const _basePath = '<?= $basePath ?>';

function abrirModalEdicion(id, numero, esRevision = false) {
    _editId     = id;
    _editNumero = numero;
    _esRevision = Boolean(esRevision);
    
    document.getElementById('modal-edit-numero').textContent = numero;
    const modal = document.getElementById('modal-opciones-modificar');
    const cardModificar = document.getElementById('card-modificar');

    if (cardModificar) {
        if (_esRevision) {
            cardModificar.classList.add('card-disabled');
            cardModificar.setAttribute('title', 'Esta cotización ya es una revisión. Para una nueva versión modifique la original o use Ajustar.');
        } else {
            cardModificar.classList.remove('card-disabled');
            cardModificar.removeAttribute('title');
        }
    }

    modal.classList.remove('modal-hidden');
    modal.classList.add('modal-active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEdicion() {
    const modal = document.getElementById('modal-opciones-modificar');
    if (modal) {
        modal.classList.remove('modal-active');
        modal.classList.add('modal-hidden');
    }
    document.body.style.overflow = 'auto';
    _editId = null;
    _editNumero = null;
    _esRevision = false;
}

function irAjustar() {
    if (_editId === null) return;
    window.location.href = _basePath + '?module=cotizaciones&action=ajustar&id=' + _editId + '&numero=' + encodeURIComponent(_editNumero);
}

function irModificar() {
    if (_editId === null || _esRevision) return;
    window.location.href = _basePath + '?module=cotizaciones&action=modificar&id=' + _editId + '&numero=' + encodeURIComponent(_editNumero);
}

window.onclick = e => {
    if (e.target === document.getElementById('modal-pdf-viewer')) cerrarPDF();
    if (e.target === document.getElementById('modal-opciones-modificar')) cerrarModalEdicion();
};
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        cerrarPDF();
        cerrarModalEdicion();
    }
});
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
