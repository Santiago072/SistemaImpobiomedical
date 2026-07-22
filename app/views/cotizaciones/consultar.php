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

        <?php if ($mensajeError): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <!-- Filtros de búsqueda estilo Panel -->
        <div class="mod-search-bar">
            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=consultar" class="mod-search-form" style="display:flex; gap:10px; align-items:center; flex:1;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <span class="mod-search-icon"><i class="bi bi-funnel"></i></span>
                <input type="date" name="fecha" value="<?= htmlspecialchars($busquedaFecha) ?>" class="mod-search-input" style="flex:0.5; border: 1.5px solid #e2e8f0; border-radius: 9px; padding: 10px;" onchange="this.form.submit()">
                <input type="text" name="nombre_cliente" value="<?= htmlspecialchars($busquedaCliente) ?>" placeholder="Buscar por cliente..." maxlength="60" class="mod-search-input" style="flex:1;">
                <input type="text" name="numero_cotizacion" value="<?= htmlspecialchars($busquedaNumero) ?>" placeholder="Número cotización..." maxlength="20" class="mod-search-input" style="flex:1;">
                
                <button type="submit" class="imo-btn-save" style="padding: 10px 15px; border-radius: 9px;"><i class="bi bi-search"></i> Buscar</button>
                <?php if (!empty($cotizaciones) || $busquedaFecha || $busquedaCliente || $busquedaNumero): ?>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cotizaciones)): ?>
                        <?php foreach ($cotizaciones as $cot): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cot['numero_cotizacion'] ?: 'Sin número') ?></strong></td>
                            <td><?= htmlspecialchars($cot['fecha_creacion']) ?></td>
                            <td><?= htmlspecialchars($cot['cliente_nombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cot['cliente_ciudad'] ?? '') ?></td>
                            <td>
                                <div class="mod-actions">
                                    <?php if (!empty($cot['numero_cotizacion'])): ?>
                                    <button type="button" class="mod-btn-edit" style="width:auto; padding:0 12px; font-weight:600;"
                                        onclick="verPDF('<?= htmlspecialchars($cot['numero_cotizacion']) ?>', '<?= htmlspecialchars($cot['nombre_cliente']) ?>')">
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
                                    <button type="button"
                                        style="width:auto; padding:0 12px; font-weight:600; background:rgba(34,197,94,.15); color:#22c55e; border:1.5px solid #22c55e; border-radius:8px; cursor:pointer; display:inline-flex; align-items:center; gap:5px; height:34px; font-size:12px; transition:all .2s;"
                                        onclick="window.location.href='<?= $basePath ?>?module=ordenes&action=seleccionar_items&cotizacion=<?= urlencode($cot['numero_cotizacion']) ?>'"
                                        title="Generar Orden de Compra">
                                        <i class="bi bi-cart-plus-fill"></i> Orden
                                    </button>
                                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                                    <a href="<?= $basePath ?>?module=cotizaciones&action=eliminar&id=<?= (int)$cot['id'] ?>"
                                       class="mod-btn-del"
                                       onclick="return confirm('¿Eliminar la cotización <?= htmlspecialchars($cot['numero_cotizacion']) ?>?')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </a>
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
                        <td colspan="6" class="mod-empty">
                            <i class="bi bi-search"></i>
                            <p>No se encontraron cotizaciones.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="mod-empty">
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

window.onclick = e => { if (e.target === document.getElementById('modal-pdf-viewer')) cerrarPDF(); };
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarPDF(); });
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
