<?php
/**
 * Vista: Consultar Órdenes de Compra
 * Variables: $ordenes, $csrf_token, $paginaActual, $totalPaginas,
 *            $busquedaProveedor, $busquedaPO, $busquedaCotizacion, $busquedaFecha
 */
$pageTitle = 'Órdenes de Compra';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';

$rol = $_SESSION['rol'] ?? 'usuario';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="mod-title"><i class="bi bi-cart-check-fill"></i> Órdenes de Compra</h1>
                <p class="mod-sub">Consulte y gestione las órdenes de compra generadas</p>
            </div>
        </div>

        <!-- Filtros -->
        <div class="mod-search-bar">
            <form method="POST" action="<?= $basePath ?>?module=ordenes&action=consultar"
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
                <a href="<?= $basePath ?>?module=ordenes&action=exportarPdf" download="Reporte_Ordenes_Compra.pdf" class="imo-btn-save" style="padding:10px 15px; border-radius:9px; background:#ef4444; text-decoration:none;">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <?php if ($busquedaProveedor || $busquedaCotizacion || !empty($busquedaFechaInicio) || !empty($busquedaFechaFin)): ?>
                <a href="<?= $basePath ?>?module=ordenes&action=consultar&limpiar=1" class="mod-btn-clear" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla -->
        <div class="mod-table-wrap">
            <table class="mod-table">
                <thead>
                    <tr>
                        <th>P.O.</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Cotización</th>
                        <th>Condiciones Pago</th>
                        <th>Generada por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes)): ?>
                        <?php foreach ($ordenes as $ord): ?>
                        <tr>
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
                        <td colspan="7" class="mod-empty">
                            <i class="bi bi-cart-x"></i>
                            <p>No hay órdenes de compra registradas.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pagBaseUrl = $basePath . '?module=ordenes&action=consultar';
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
            <iframe id="orden-frame" class="iframe-frame" src="" style="width:100%; height:75vh; border:none;"></iframe>
        </div>
    </div>
</div>

<script>
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
