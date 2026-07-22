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
            <?php if(isset($_SESSION['cotizacion_revision_de'])): ?>
            <h1 class="page-title"><i class="bi bi-pencil-square"></i> Modificando Cotización: <?= htmlspecialchars($_SESSION['cotizacion_revision_de']) ?></h1>
            <p style="color:#f59e0b; font-size:13px; margin-bottom:10px;"><i class="bi bi-info-circle-fill"></i> Puedes editar, eliminar o agregar productos. El nuevo PDF tendrá un número derivado al finalizar.</p>
            <?php else: ?>
            <h1 class="page-title"><i class="bi bi-plus-circle-fill"></i> Nueva Cotización</h1>
            <?php endif; ?>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;">
                <p class="page-sub" style="margin:0;">Ítems agregados: <strong><?= $totalItems ?></strong></p>
                <?php if ($totalItems > 0): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary" style="padding: 6px 12px; font-size: 12px; text-decoration: none;">
                    <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Datos Cliente y PDF
                </a>
                <a href="<?= $basePath ?>?module=cotizaciones&action=limpiar_borrador" class="btn-mod-del" onclick="return confirm('¿Seguro que deseas descartar esta cotización y empezar de cero?');" style="padding: 6px 12px; font-size: 12px; text-decoration: none; background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; border-radius: 8px; font-weight: 500;">
                    <i class="bi bi-trash-fill"></i> Descartar
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($mensajeExito)): ?>
        <div class="mod-alert mod-alert-ok"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>

        <?php
        $errorUrl = htmlspecialchars(urldecode($_GET['error'] ?? ''));
        if (!empty($errorUrl)): ?>
        <div class="mod-alert mod-alert-err" style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; gap:10px; align-items:center;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Error al guardar el ítem: <?= $errorUrl ?></span>
        </div>
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

                        <!-- ── PASO 1: Calculadora de Ganancias (antes del formulario) ── -->
                        <div class="ganancias-section">
                            <button type="button" class="ganancias-toggle" onclick="toggleGanancias()">
                                <i class="bi bi-percent"></i> Porcentajes de Ganancias (Calculadora Dinámica)
                                <i class="bi bi-chevron-down" id="iconGanancias"></i>
                            </button>
                            <div id="panelGanancias" class="ganancias-panel" style="display:none;">
                                <div class="ganancias-aviso" style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12px; color:#92400e;">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Completa el precio del proveedor y configura los porcentajes. Al finalizar, el <strong>valor de Estampillas</strong> se asignará automáticamente como <strong>Precio Unitario</strong> del producto en la cotización.
                                </div>
                                <!-- Campos ocultos para guardar resultados -->
                                <input type="hidden" name="porcentaje_utilidad" id="hdnPctUtilidad" value="0">
                                <input type="hidden" name="flete" id="hdnFlete" value="0">
                                <input type="hidden" name="calibracion" id="hdnCalibracion" value="0">
                                <input type="hidden" name="estampillas" id="hdnEstampillas" value="0">
                                <input type="hidden" name="calc_ops" id="hdnCalcOps" value="{}">

                                <div class="imo-form-row">
                                    <div class="imo-form-group">
                                        <label>Precio Proveedor Base ($) *</label>
                                        <input type="number" name="precio_proveedor" id="inpPrecioProveedor"
                                               min="0" step="0.01" placeholder="0.00" oninput="calcularTotales()">
                                    </div>
                                    <div class="imo-form-group">
                                        <label>Proveedor</label>
                                        <input type="text" name="proveedor" id="inpProveedor" maxlength="100" placeholder="Ej: ALENO SAS">
                                    </div>
                                </div>
                                <div class="imo-form-row">
                                    <div class="imo-form-group">
                                        <label>Código Producto Proveedor</label>
                                        <input type="text" name="codigo_proveedor" id="inpCodigoProveedor" maxlength="60" placeholder="Ej: PROV-001">
                                    </div>
                                </div>

                                <div id="calc-container" style="margin-top: 15px;"></div>

                                <!-- Resultado calculado -->
                                <div class="ganancia-resultado">
                                    <!-- Se eliminó Valor Estampillas -->
                                    <div class="ganancia-res-row total-row" style="border-top:1px solid #d1fae5; padding-top:8px; margin-top:4px;">
                                        <span>💵 Valor Final con IVA para el Cliente:</span>
                                        <strong id="resValorFinal" style="color:#059669; font-size:16px;">$0</strong>
                                    </div>
                                    <p style="font-size:10px; color:#6b7280; margin-top:4px;">* El valor de Estampillas se asigna automáticamente como Precio Unitario del ítem.</p>
                                </div>
                            </div>
                        </div>

                        <!-- ── PASO 2: Formulario de agregar producto ── -->
                        <div class="imo-form-group">
                            <label>Categoría *</label>
                            <select name="categoria" id="inpCategoria">
                                <option value="">-- Seleccionar categoría --</option>
                                <option value="Insumo Medico Quirurgico">Insumo Médico Quirúrgico</option>
                                <option value="Insumo Medico Odontologico">Insumo Médico Odontológico</option>
                                <option value="Mobiliario Hospitalario">Mobiliario Hospitalario</option>
                                <option value="Equipo Medico">Equipo Médico</option>
                                <option value="Accesorios">Accesorios</option>
                                <option value="Repuestos">Repuestos</option>
                                <option value="Equipo de Terapia">Equipo de Terapia</option>
                                <option value="Medicamentos">Medicamentos</option>
                            </select>
                        </div>

                        <div class="imo-form-group">
                            <label>Código del Producto</label>
                            <input type="text" name="codigo_producto" id="inpCodigoProducto" maxlength="60"
                                   placeholder="Ej: MQ-001">
                        </div>

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
                                <label>Precio Unitario (sin IVA) *
                                    <span id="lblPrecioFuente" style="font-size:10px; color:#059669; font-weight:600;"></span>
                                </label>
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

/* Ganancias section */
.ganancias-section { margin-bottom: 16px; }
.ganancias-toggle {
    width: 100%; display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px; background: linear-gradient(135deg, #064e3b, #059669);
    color: #fff; border: none; border-radius: 10px; cursor: pointer;
    font-size: 13px; font-weight: 600; letter-spacing: 0.3px;
    transition: opacity .2s;
}
.ganancias-toggle:hover { opacity: .88; }
.ganancias-panel {
    background: #f0fdf4; border: 1px solid #6ee7b7;
    border-radius: 0 0 10px 10px; padding: 16px;
    margin-top: -4px;
}
.calc-etapa {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 12px; margin-bottom: 12px;
}
.calc-etapa h4 { margin: 0 0 10px 0; font-size: 13px; color: #111827; display: flex; justify-content: space-between; border-bottom: 1px solid #f3f4f6; padding-bottom: 6px; }
.calc-etapa h4 span { color: #059669; font-weight: bold; }
.calc-op-row {
    display: flex; gap: 8px; margin-bottom: 8px; align-items: center;
}
.calc-op-row select, .calc-op-row input {
    border: 1px solid #d1d5db; border-radius: 6px; padding: 6px; font-size: 12px;
}
.btn-calc-add {
    background: #e0f2fe; color: #0284c7; border: none; border-radius: 6px;
    padding: 4px 8px; font-size: 11px; cursor: pointer; font-weight: 600;
}
.btn-calc-del { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 14px; }
.ganancia-resultado {
    background: #fff; border: 1px solid #6ee7b7; border-radius: 8px;
    padding: 12px 16px; margin-top: 12px;
}
.ganancia-res-row {
    display: flex; justify-content: space-between;
    font-size: 12px; color: #374151; margin-bottom: 4px;
}
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
                        <div><div class="sugerencia-nombre">${p.titulo}</div>`;
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
    
    // Auto-fill new fields
    document.getElementById('inpCategoria').value      = p.categoria || '';
    document.getElementById('inpCodigoProducto').value = p.codigo_producto || '';
    document.getElementById('inpCodigoProveedor').value= p.codigo_proveedor || '';
    
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
    
    // Se eliminó la llamada a calcularTotales() para evitar un bucle infinito que ralentizaba la página.
}

function toggleGanancias() {
    const panel = document.getElementById('panelGanancias');
    const icon  = document.getElementById('iconGanancias');
    const visible = panel.style.display !== 'none';
    panel.style.display = visible ? 'none' : 'block';
    icon.className = visible ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
}

// ── Estado de la Calculadora Dinámica ──
let calcState = {
    utilidad:    [{ tipo: 'div_pct', valor: 0.70 }], // Ej: dividido por 70%
    flete:       [], // Ej: sumas fijas o porcentajes
    calibracion: [],
    estampillas: []
};

function addOp(etapa) {
    calcState[etapa].push({ tipo: 'suma', valor: 0 });
    renderCalculadoraInputs();
}

function removeOp(etapa, index) {
    calcState[etapa].splice(index, 1);
    renderCalculadoraInputs();
}

function updateOpTipo(etapa, index, valor) {
    calcState[etapa][index].tipo = valor;
    calcularTotales();
}

function updateOpValor(etapa, index, valor) {
    calcState[etapa][index].valor = parseFloat(valor) || 0;
    calcularTotales(); // Solo recalcula, NO redibuja los inputs
}

function aplicarOperaciones(valorBase, operaciones) {
    let acumulado = parseFloat(valorBase) || 0;
    operaciones.forEach(op => {
        let v = parseFloat(op.valor) || 0;
        if (op.tipo === 'suma') acumulado += v;
        if (op.tipo === 'mult_pct') acumulado += acumulado * (v / 100);
        if (op.tipo === 'div_pct' && v > 0) acumulado = acumulado / v; // Ej: v=0.70 => divide por 0.70
    });
    return acumulado;
}

function renderCalculadoraInputs() {
    const container = document.getElementById('calc-container');
    if (!container) return;

    const renderEtapa = (clave, titulo) => {
        let html = `<div class="calc-etapa">
            <h4>${titulo} <span id="acum_${clave}">Acumulado: $0</span></h4>`;
        
        calcState[clave].forEach((op, idx) => {
            html += `<div class="calc-op-row">
                <select onchange="updateOpTipo('${clave}', ${idx}, this.value)">
                    <option value="suma" ${op.tipo==='suma'?'selected':''}>+ Sumar valor ($)</option>
                    <option value="mult_pct" ${op.tipo==='mult_pct'?'selected':''}>+ Sumar porcentaje (%)</option>
                    <option value="div_pct" ${op.tipo==='div_pct'?'selected':''}>/ Dividir entre (Ej: 0.70)</option>
                </select>
                <input type="number" step="0.01" value="${op.valor}" oninput="updateOpValor('${clave}', ${idx}, this.value)" placeholder="Valor...">
                <button type="button" class="btn-calc-del" onclick="removeOp('${clave}', ${idx})"><i class="bi bi-x-circle-fill"></i></button>
            </div>`;
        });
        
        html += `<button type="button" class="btn-calc-add" onclick="addOp('${clave}')">+ Añadir operación</button>
        </div>`;
        return html;
    };

    container.innerHTML = 
        renderEtapa('utilidad', '1. Utilidad (Sobre precio proveedor)') +
        renderEtapa('flete', '2. Fletes (Sobre acumulado anterior)') +
        renderEtapa('calibracion', '3. Calibración') +
        renderEtapa('estampillas', '4. Estampillas');

    calcularTotales();
}

function calcularTotales() {
    const precioBase = parseFloat(document.getElementById('inpPrecioProveedor')?.value) || 0;
    
    // Cálculos en cascada
    const totalUtilidad = aplicarOperaciones(precioBase, calcState.utilidad);
    const totalFlete    = aplicarOperaciones(totalUtilidad, calcState.flete);
    const totalCalib    = aplicarOperaciones(totalFlete, calcState.calibracion);
    const totalEstamp   = aplicarOperaciones(totalCalib, calcState.estampillas);

    // Guardar totales finales en los inputs hidden
    document.getElementById('hdnPctUtilidad').value  = (totalUtilidad - precioBase).toFixed(2);
    document.getElementById('hdnFlete').value        = (totalFlete - totalUtilidad).toFixed(2);
    document.getElementById('hdnCalibracion').value  = (totalCalib - totalFlete).toFixed(2);
    document.getElementById('hdnEstampillas').value  = (totalEstamp - totalCalib).toFixed(2);

    const formatMoney = v => 'Acumulado: $' + Math.round(v).toLocaleString('es-CO');

    // Actualizar spans de acumulados
    const elUtil  = document.getElementById('acum_utilidad');    if(elUtil)  elUtil.textContent  = formatMoney(totalUtilidad);
    const elFlete = document.getElementById('acum_flete');       if(elFlete) elFlete.textContent = formatMoney(totalFlete);
    const elCalib = document.getElementById('acum_calibracion'); if(elCalib) elCalib.textContent = formatMoney(totalCalib);
    const elEstamp= document.getElementById('acum_estampillas'); if(elEstamp)elEstamp.textContent= formatMoney(totalEstamp);

    // ── Autocompletar Precio Unitario con el valor de Estampillas ─────────
    if (totalEstamp > 0) {
        const inpPrecio = document.getElementById('inpPrecio');
        if (inpPrecio) {
            inpPrecio.value = Math.round(totalEstamp);
            // Indicar visualmente que el precio viene de la calculadora
            const lbl = document.getElementById('lblPrecioFuente');
            if (lbl) lbl.textContent = '(← de Estampillas)';
            calcularPreview();
        }
    }

    // Ya no mostramos resEstampillas por separado.
    
    const ivaVal  = document.getElementById('inpIva')?.value || 'si';
    const pctIva  = parseFloat(document.getElementById('inpPctIva')?.value) || 0;
    const ivaFinal = ivaVal === 'si' ? totalEstamp * (pctIva / 100) : 0;
    const resFinal = document.getElementById('resValorFinal');
    if (resFinal) resFinal.textContent = '$' + Math.round(totalEstamp + ivaFinal).toLocaleString('es-CO');
}

function limpiarFormulario() {
    document.getElementById('hdnProductoId').value = '';
    document.getElementById('hdnFotoActual').value = '';
    document.getElementById('badgeAuto').style.display = 'none';
    document.getElementById('formItem').reset();
    document.getElementById('inpIva').value = 'si';
    document.getElementById('previewFoto').innerHTML = '';
    // Limpiar campos nuevos
    document.getElementById('inpCategoria').value = '';
    document.getElementById('inpCodigoProducto').value = '';
    
    document.getElementById('inpPrecioProveedor').value = '';
    document.getElementById('inpProveedor').value = '';
    document.getElementById('inpCodigoProveedor').value = '';

    // Resetear calculadora
    calcState = {
        utilidad:    [{ tipo: 'div_pct', valor: 0.70 }],
        flete:       [],
        calibracion: [],
        estampillas: []
    };
    renderCalculadoraInputs();
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

// Serializar calcState en hidden antes de enviar el formulario
document.getElementById('formItem').addEventListener('submit', function() {
    document.getElementById('hdnCalcOps').value = JSON.stringify(calcState);
});

toggleIva('si');
calcularPreview();
renderCalculadoraInputs();
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
