<?php
/**
 * Vista: Crear Cotización — agregar ítems (productos del catálogo o manuales).
 * Variables: $productos, $producto, $busqueda, $cotizacion_id, $items, $totalItems, $csrf_token, $mensajeExito
 */
$pageTitle = 'Crear Cotización';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-plus-circle-fill"></i> Nueva Cotización</h1>
            <p class="page-sub">Ítems agregados: <strong><?= $totalItems ?></strong>
                <?php if ($totalItems > 0): ?>
                | <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                    <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Datos Cliente y PDF
                </a>
                <?php endif; ?>
            </p>
        </div>

        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>

        <div class="cot-grid">

            <!-- ── Panel izquierdo: Buscar / Formulario ── -->
            <div class="panel-form">
                <div class="mod-table-wrap" style="padding:24px; margin-bottom:20px; overflow:visible;">
                    <h2 class="mod-title" style="font-size:18px; margin-bottom:16px;"><i class="bi bi-search"></i> Buscar Producto del Catálogo</h2>
                    <div class="search-live">
                        <input type="text" id="busquedaProducto" placeholder="Buscar por nombre..." class="mod-search-input" style="width:100%; border:1.5px solid #e2e8f0; border-radius:9px; padding:11px 14px; background:#f8fafc;"
                               value="<?= htmlspecialchars($busqueda) ?>">
                        <div id="listaProductos" class="lista-sugerencias"></div>
                    </div>
                </div>

                <div class="mod-table-wrap" style="padding:24px;">
                    <h2 class="mod-title" style="font-size:18px; margin-bottom:16px;">
                        <i class="bi bi-pencil-square"></i>
                        <span id="formTitulo">Agregar Ítem</span>
                        <span class="mod-badge badge-green" id="badgeAuto" style="display:none; margin-left:8px;">Del catálogo</span>
                    </h2>

                    <form method="POST" enctype="multipart/form-data" action="<?= $basePath ?>?module=cotizaciones&action=crear" id="formItem">
                        <input type="hidden" name="action" value="guardar_item">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="producto_id" id="hdnProductoId" value="">
                        <input type="hidden" name="foto_actual" id="hdnFotoActual" value="">

                        <div class="imo-form-group">
                            <label>Título / Nombre del Producto *</label>
                            <input type="text" name="titulo" id="inpTitulo" required maxlength="255"
                                   value="<?= htmlspecialchars($producto['titulo'] ?? '') ?>">
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="cantidad" id="inpCantidad" min="1" value="<?= $producto['cantidad'] ?? 1 ?>" required>
                            </div>
                            <div class="imo-form-group">
                                <label>Precio Unitario (antes de IVA) *</label>
                                <input type="number" name="precio" id="inpPrecio" min="0" step="0.01"
                                       value="<?= $producto['precio'] ?? '' ?>" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>¿Aplica IVA?</label>
                                <select name="iva" id="inpIva" onchange="toggleIva(this.value)">
                                    <option value="si" <?= ($producto['iva'] ?? 'si') === 'si' ? 'selected' : '' ?>>Sí</option>
                                    <option value="no" <?= ($producto['iva'] ?? 'si') === 'no' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="imo-form-group" id="grupoIvaPct">
                                <label>% IVA</label>
                                <input type="number" name="porcentaje_iva" id="inpPctIva"
                                       min="0" max="100" step="0.01" value="<?= $producto['porcentaje_iva'] ?? 19 ?>">
                            </div>
                        </div>

                        <div class="imo-form-group">
                            <label>Tiempo de Entrega</label>
                            <input type="text" name="tiempo_entrega" id="inpTiempoEntrega"
                                   placeholder="Ej: 5 A 15 DÍAS HÁBILES" maxlength="120">
                        </div>

                        <div class="imo-form-group">
                            <label>Descripción *</label>
                            <textarea name="descripcion" id="inpDesc" style="padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:9px; width:100%; height:100px; resize:vertical; outline:none;"
                                      required maxlength="5000"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="imo-form-group">
                            <label>Imagen del Producto</label>
                            <input type="file" name="foto" id="inpFoto" accept="image/*">
                            <div id="previewFoto" style="margin-top:8px;"></div>
                        </div>

                        <!-- Preview IVA en tiempo real -->
                        <div class="preview-iva" id="previewIva">
                            <div class="prev-row"><span>Precio base:</span> <strong id="prevBase">$0</strong></div>
                            <div class="prev-row"><span>IVA:</span> <strong id="prevIva">$0</strong></div>
                            <div class="prev-row total-row"><span>Total unitario:</span> <strong id="prevTotal">$0</strong></div>
                        </div>

                        <div class="imo-modal-footer" style="border-top:none; padding-top:0;">
                            <button type="button" class="imo-btn-cancel" onclick="limpiarFormulario()">
                                <i class="bi bi-arrow-counterclockwise"></i> Limpiar
                            </button>
                            <button type="submit" class="btn-mod-primary" id="btnGuardar">
                                <i class="bi bi-plus-lg"></i> Agregar a Cotización
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Panel derecho: Lista de ítems agregados ── -->
            <div class="panel-lista">
                <div class="mod-table-wrap" style="padding:24px;">
                    <h2 class="mod-title" style="font-size:18px; margin-bottom:16px;"><i class="bi bi-list-check"></i> Ítems de la Cotización</h2>

                    <?php if (empty($items)): ?>
                    <div class="mod-empty">
                        <i class="bi bi-inbox"></i>
                        <p>Agrega ítems usando el formulario de arriba</p>
                    </div>
                    <?php else: ?>

                    <div class="tabla-responsive">
                        <table class="mod-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>IVA</th>
                                    <th>Subtotal</th>
                                    <th>Entrega</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $totalCot = 0; foreach ($items as $it):
                                $pu  = (float)$it['precio'];
                                $qty = (int)$it['cantidad'];
                                $pct = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaAmt = ($it['iva'] === 'si') ? $pu * $qty * ($pct / 100) : 0;
                                $sub = $pu * $qty + $ivaAmt;
                                $totalCot += $sub;
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($it['foto'])): ?>
                                        <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($it['foto']) ?>"
                                             style="height:32px;width:32px;object-fit:cover;border-radius:6px;margin-right:6px;vertical-align:middle;">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 40, '…')) ?>
                                    </td>
                                    <td><?= $qty ?></td>
                                    <td>$<?= number_format($pu, 0, ',', '.') ?></td>
                                    <td><?= $it['iva'] === 'si' ? $pct . '%' : 'No' ?></td>
                                    <td><strong>$<?= number_format($sub, 0, ',', '.') ?></strong></td>
                                    <td style="font-size:11px;"><?= htmlspecialchars($it['tiempo_entrega'] ?? '') ?></td>
                                    <td>
                                        <div class="mod-actions">
                                            <a href="<?= $basePath ?>?module=cotizaciones&action=editar_item&id=<?= $it['id'] ?>"
                                               class="mod-btn-edit" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                            <button onclick="eliminarItem(<?= $it['id'] ?>)"
                                                    class="mod-btn-del" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" style="text-align:right;"><strong>TOTAL ESTIMADO:</strong></td>
                                    <td colspan="3"><strong class="total-highlight" style="color:#f59e0b; font-size:16px;">$<?= number_format($totalCot, 0, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($totalItems > 0): ?>
                    <div style="margin-top:24px; text-align:right;">
                        <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary" style="text-decoration:none;">
                            <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Completar Datos del Cliente
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /.cot-grid -->
    </main>
</div>

<style>
.cot-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
@media(max-width:900px){ .cot-grid { grid-template-columns: 1fr; } }
.lista-sugerencias {
    position: absolute; z-index: 200; width: 100%;
    background: #ffffff; border: 1px solid var(--copper);
    border-radius: 0 0 10px 10px; max-height: 280px; overflow-y: auto;
    display: none;
}
.search-live { position: relative; z-index: 999; }
.sugerencia-item {
    padding: 10px 14px; cursor: pointer;
    display: flex; align-items: center; gap: 10px;
    border-bottom: 1px solid #e5e7eb;
    transition: background .15s;
}
.sugerencia-item:hover { background: rgba(26,138,138,.2); }
.sugerencia-item img { width:36px; height:36px; object-fit:cover; border-radius:6px; flex-shrink:0; }
.sugerencia-nombre { font-size:13px; font-weight:500; }
.sugerencia-precio { font-size:11px; color: var(--gilt); }
.badge-auto {
    font-size:10px; background: var(--copper);
    color:#10757e; padding:2px 8px; border-radius:20px;
    margin-left:8px; vertical-align:middle;
}
.preview-iva {
    background: rgba(26,138,138,.1);
    border: 1px solid #e5e7eb;
    border-radius: 10px; padding: 12px 16px;
    margin-bottom: 14px;
}
.prev-row { display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; color:#4b5563; }
.total-row { border-top:1px solid #e5e7eb; padding-top:8px; margin-top:4px; color:#10757e; font-size:13px; }
.total-highlight { color: var(--amber); font-size:15px; }
.empty-state { text-align:center; padding:48px 20px; color:#9ca3af; }
.empty-state i { font-size:48px; display:block; margin-bottom:12px; }
</style>

<script>
const BASE = '<?= $basePath ?>';
const CSRF = '<?= htmlspecialchars($csrf_token) ?>';

// ── Live search ───────────────────────────────────────────────────────────────
let timerBusq;
document.getElementById('busquedaProducto').addEventListener('input', function() {
    clearTimeout(timerBusq);
    timerBusq = setTimeout(() => buscarProductos(this.value.trim()), 280);
});

function buscarProductos(q) {
    if (q.length < 2) { document.getElementById('listaProductos').style.display='none'; return; }
    fetch(BASE + '?module=cotizaciones&action=ajax_buscar_productos&busqueda=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(json => {
            if (json.status !== 'success') return;
            const lista = document.getElementById('listaProductos');
            lista.innerHTML = '';
            if (!json.data.length) {
                lista.innerHTML = '<div class="sugerencia-item" style="color:#9ca3af">Sin resultados</div>';
            } else {
                json.data.forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'sugerencia-item';
                    div.innerHTML = `
                        ${p.foto ? `<img src="${BASE}uploads/${p.foto}">` : '<div style="width:36px;height:36px;border-radius:6px;background:#e5e7eb;flex-shrink:0;"></div>'}
                        <div><div class="sugerencia-nombre">${p.titulo}</div>
                             <div class="sugerencia-precio">$${Number(p.precio).toLocaleString('es-CO')} | IVA: ${p.iva === 'si' ? p.porcentaje_iva + '%' : 'No'}</div></div>`;
                    div.addEventListener('click', () => autocompletar(p));
                    lista.appendChild(div);
                });
            }
            lista.style.display = 'block';
        });
}

function autocompletar(p) {
    document.getElementById('hdnProductoId').value    = p.id;
    document.getElementById('inpTitulo').value        = p.titulo;
    document.getElementById('inpDesc').value          = p.descripcion;
    document.getElementById('inpPrecio').value        = p.precio;
    document.getElementById('inpCantidad').value      = 1;
    document.getElementById('inpIva').value           = (p.iva || 'si').toLowerCase();
    document.getElementById('inpPctIva').value        = parseFloat(p.porcentaje_iva || 19);
    document.getElementById('hdnFotoActual').value    = p.foto || '';
    
    if (p.foto) {
        document.getElementById('previewFoto').innerHTML = '<img src="' + BASE + 'uploads/' + p.foto + '" style="max-height:100px; border-radius:8px; border:1px solid #e2e8f0; margin-top:8px;">';
    } else {
        document.getElementById('previewFoto').innerHTML = '';
    }
    
    document.getElementById('badgeAuto').style.display = 'inline';
    document.getElementById('listaProductos').style.display = 'none';
    document.getElementById('busquedaProducto').value = '';
    toggleIva((p.iva || 'si').toLowerCase());
    calcularPreview();
}

// ── IVA preview ───────────────────────────────────────────────────────────────
document.getElementById('inpPrecio').addEventListener('input', calcularPreview);
document.getElementById('inpCantidad').addEventListener('input', calcularPreview);
document.getElementById('inpPctIva').addEventListener('input', calcularPreview);

function toggleIva(val) {
    document.getElementById('grupoIvaPct').style.display = (val === 'si') ? '' : 'none';
    calcularPreview();
}

function calcularPreview() {
    const pu  = parseFloat(document.getElementById('inpPrecio').value) || 0;
    const qty = parseInt(document.getElementById('inpCantidad').value) || 1;
    const pct = parseFloat(document.getElementById('inpPctIva').value) || 0;
    const ivaVal = document.getElementById('inpIva').value;
    const iva = ivaVal === 'si' ? pu * (pct / 100) : 0;
    const total = (pu + iva) * qty;
    document.getElementById('prevBase').textContent = '$' + (pu * qty).toLocaleString('es-CO', {minimumFractionDigits:0});
    document.getElementById('prevIva').textContent  = '$' + (iva * qty).toLocaleString('es-CO', {minimumFractionDigits:0});
    document.getElementById('prevTotal').textContent = '$' + total.toLocaleString('es-CO', {minimumFractionDigits:0});
}

function limpiarFormulario() {
    document.getElementById('hdnProductoId').value = '';
    document.getElementById('badgeAuto').style.display = 'none';
    document.getElementById('formItem').reset();
    document.getElementById('inpIva').value = 'si';
    document.getElementById('previewFoto').innerHTML = '';
    toggleIva('si');
}

// ── Eliminar ítem ─────────────────────────────────────────────────────────────
function eliminarItem(id) {
    if (!confirm('¿Eliminar este ítem?')) return;
    fetch(BASE + '?module=cotizaciones&action=eliminar_item&id=' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(j => { if (j.status === 'success') location.reload(); });
}

// Cerrar lista al hacer clic fuera
document.addEventListener('click', e => {
    if (!e.target.closest('.search-live'))
        document.getElementById('listaProductos').style.display = 'none';
});

toggleIva('si');
calcularPreview();
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
