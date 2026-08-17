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
            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=consultar" class="mod-search-form" style="display:flex; gap:10px; align-items:center; flex:1; flex-wrap:wrap;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <span class="mod-search-icon"><i class="bi bi-funnel"></i></span>
                <input type="date" name="fecha" value="<?= htmlspecialchars($busquedaFecha) ?>" class="mod-search-input" style="flex:0.5; min-width:140px; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 10px;" onchange="this.form.submit()">
                <input type="text" name="nombre_cliente" value="<?= htmlspecialchars($busquedaCliente) ?>" placeholder="Buscar por cliente..." maxlength="60" class="mod-search-input" style="flex:1; min-width:180px;">
                <input type="text" name="numero_cotizacion" value="<?= htmlspecialchars($busquedaNumero) ?>" placeholder="Número cotización..." maxlength="20" class="mod-search-input" style="flex:1; min-width:150px;">
                <select name="estado_comercial" class="mod-search-input" style="flex:0.8; min-width:150px; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 10px;" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" <?= ($busquedaEstado ?? '') === 'pendiente' ? 'selected' : '' ?>>🟡 Pendientes</option>
                    <option value="concluida" <?= ($busquedaEstado ?? '') === 'concluida' ? 'selected' : '' ?>>🟢 Concluidas</option>
                    <option value="descartada" <?= ($busquedaEstado ?? '') === 'descartada' ? 'selected' : '' ?>>🔴 Descartadas</option>
                </select>
                
                <button type="submit" class="imo-btn-save" style="padding: 10px 15px; border-radius: 9px;"><i class="bi bi-search"></i> Buscar</button>
                <?php if (!empty($cotizaciones) || $busquedaFecha || $busquedaCliente || $busquedaNumero || !empty($busquedaEstado)): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=consultar&limpiar=1" class="mod-btn-clear" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de resultados -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>N° Cotización</th>
                        <th>Fecha</th>
                        <th>Cliente / Entidad</th>
                        <th>Ciudad</th>
                        <th style="text-align:center;">Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cotizaciones)): ?>
                        <?php foreach ($cotizaciones as $cot): 
                            $estCom = $cot['estado_comercial'] ?? 'pendiente';
                            $badgeClass = 'badge-yellow';
                            $badgeLabel = 'Pendiente';
                            $badgeColor = '#ca8a04';
                            $badgeBg    = 'rgba(234,179,8,.15)';
                            if ($estCom === 'concluida') {
                                $badgeClass = 'badge-green';
                                $badgeLabel = 'Concluida';
                                $badgeColor = '#16a34a';
                                $badgeBg    = 'rgba(34,197,94,.15)';
                            } elseif ($estCom === 'descartada') {
                                $badgeClass = 'badge-red';
                                $badgeLabel = 'Descartada';
                                $badgeColor = '#dc2626';
                                $badgeBg    = 'rgba(239,68,68,.15)';
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cot['numero_cotizacion'] ?: 'Sin número') ?></strong></td>
                            <td><?= htmlspecialchars($cot['fecha_creacion']) ?></td>
                            <td><?= htmlspecialchars($cot['cliente_nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cot['cliente_ciudad'] ?? '') ?></td>
                            <td style="text-align:center;">
                                <?php if (($_SESSION['rol'] ?? 'usuario') === 'admin'): ?>
                                    <select class="estado-comercial-select" 
                                            data-id="<?= (int)$cot['id'] ?>"
                                            style="padding:4px 8px; border-radius:6px; font-weight:700; font-size:11.5px; border:1.5px solid <?= $badgeColor ?>; background:<?= $badgeBg ?>; color:<?= $badgeColor ?>; cursor:pointer;"
                                            onchange="cambiarEstadoComercial(this)">
                                        <option value="pendiente" <?= $estCom === 'pendiente' ? 'selected' : '' ?>>🟡 Pendiente</option>
                                        <option value="concluida" <?= $estCom === 'concluida' ? 'selected' : '' ?>>🟢 Concluida</option>
                                        <option value="descartada" <?= $estCom === 'descartada' ? 'selected' : '' ?>>🔴 Descartada</option>
                                    </select>
                                <?php else: ?>
                                    <span style="display:inline-block; padding:4px 10px; border-radius:6px; font-weight:700; font-size:11px; border:1px solid <?= $badgeColor ?>; background:<?= $badgeBg ?>; color:<?= $badgeColor ?>;">
                                        <?= htmlspecialchars($badgeLabel) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="mod-actions">
                                    <?php if (!empty($cot['numero_cotizacion'])): ?>
                                    <button type="button" class="mod-btn-edit" style="width:auto; padding:0 12px; font-weight:600;"
                                        onclick="verPDF('<?= htmlspecialchars($cot['numero_cotizacion']) ?>', '<?= htmlspecialchars($cot['nombre_cliente'] ?? $cot['cliente_nombre'] ?? '') ?>')">
                                        <i class="bi bi-eye"></i> Ver PDF
                                    </button>
                                    <button type="button" class="mod-btn-del" style="width:auto; padding:0 12px; font-weight:600; background:#3b82f6; color:white; border-color:#3b82f6;"
                                        onclick="window.location.href='<?= $basePath ?>?module=cotizaciones&action=ver_respaldo&numero=<?= urlencode($cot['numero_cotizacion']) ?>'" title="Hoja de Respaldo Proveedores">
                                        <i class="bi bi-file-earmark-spreadsheet"></i> Respaldo
                                    </button>
                                    <button type="button" class="mod-btn-edit" style="width:auto; padding:0 12px; font-weight:600; background:rgba(234,179,8,.15); color:#ca8a04; border:1.5px solid #ca8a04; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; height:34px; font-size:12px; transition:all .2s;"
                                        onclick="window.location.href='<?= $basePath ?>?module=cotizaciones&action=modificar&numero=<?= urlencode($cot['numero_cotizacion']) ?>'" title="Crear nueva versión / Modificar Cotización">
                                        <i class="bi bi-pencil-square"></i> Modificar
                                    </button>
                                     <?php if ($estCom === 'pendiente'): ?>
                                     <button type="button"
                                         style="width:auto; padding:0 12px; font-weight:600; background:rgba(34,197,94,.15); color:#22c55e; border:1.5px solid #22c55e; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; height:34px; font-size:12px; transition:all .2s;"
                                         onclick="window.location.href='<?= $basePath ?>?module=ordenes&action=seleccionar_items&cotizacion=<?= urlencode($cot['numero_cotizacion']) ?>'"
                                         title="Generar Orden de Compra">
                                         <i class="bi bi-cart-plus-fill"></i> Orden
                                     </button>
                                     <?php else: ?>
                                     <button type="button" disabled
                                         style="width:auto; padding:0 12px; font-weight:600; background:#f1f5f9; color:#94a3b8; border:1.5px solid #cbd5e1; border-radius:8px; cursor:not-allowed; display:inline-flex; align-items:center; gap:5px; height:34px; font-size:12px; opacity:0.7;"
                                         title="No disponible: la cotización está <?= htmlspecialchars($estCom) ?>">
                                         <i class="bi bi-cart-x"></i> Orden
                                     </button>
                                     <?php endif; ?>
                                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=eliminar" style="display:inline;" onsubmit="return confirm('¿Eliminar la cotización <?= htmlspecialchars($cot['numero_cotizacion']) ?>?')">
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
                        <td colspan="7" class="mod-empty">
                            <i class="bi bi-search"></i>
                            <p>No se encontraron cotizaciones.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" class="mod-empty">
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
            <iframe id="pdf-frame" class="iframe-frame" src="" style="width:100%; height:75vh; border:none;"></iframe>
            <div id="pdf-error" class="pdf-error d-none">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <h4>No se pudo cargar el PDF</h4>
                <p>El archivo no está disponible o ha sido movido.</p>
            </div>
        </div>
    </div>
</div>

<script>
function verPDF(numero, cliente) {
    const modal   = document.getElementById('modal-pdf-viewer');
    const frame   = document.getElementById('pdf-frame');
    const titulo  = document.getElementById('pdf-titulo');
    const btnDesc = document.getElementById('btn-descargar');
    const err     = document.getElementById('pdf-error');

    err.style.display   = 'none';
    frame.style.display = 'block';
    titulo.textContent  = numero + ' - ' + cliente;
    frame.src           = '<?= $basePath ?>?module=cotizaciones&action=generar_pdf&ver=' + encodeURIComponent(numero);
    btnDesc.href        = '<?= $basePath ?>?module=cotizaciones&action=generar_pdf&ver=' + encodeURIComponent(numero) + '&descargar=1';
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
            const conf = estilos[nuevoEstado] || estilos['pendiente'];
            select.style.borderColor = conf.color;
            select.style.color = conf.color;
            select.style.background = conf.bg;
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

window.onclick = e => { if (e.target === document.getElementById('modal-pdf-viewer')) cerrarPDF(); };
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarPDF(); });
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
