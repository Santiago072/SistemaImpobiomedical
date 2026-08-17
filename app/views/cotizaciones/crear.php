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
            <p class="text-warning-gold mb-10"><i class="bi bi-info-circle-fill"></i> Puedes editar, eliminar o agregar productos. El nuevo PDF tendrá un número derivado al finalizar.</p>
            <?php else: ?>
            <h1 class="page-title"><i class="bi bi-plus-circle-fill"></i> Nueva Cotización</h1>
            <?php endif; ?>
            <div class="header-actions-wrap align-center mt-8">
                <p class="page-sub m-0">Ítems agregados: <strong><?= $totalItems ?></strong></p>
                <?php if ($totalItems > 0): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary btn-sm-action">
                    <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Datos Cliente y PDF
                </a>
                <a href="<?= $basePath ?>?module=cotizaciones&action=limpiar_borrador" class="btn-discard-draft" onclick="return confirm('¿Seguro que deseas descartar esta cotización y empezar de cero?');">
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
        <div class="mod-alert mod-alert-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Error al guardar el ítem: <?= $errorUrl ?></span>
        </div>
        <?php endif; ?>

        <div class="cot-grid">

            <!-- ── Panel izquierdo: Buscar / Formulario ── -->
            <div class="panel-form">
                <div class="mod-table-wrap p-24 mb-20 overflow-visible">
                    <h2 class="mod-title mb-16"><i class="bi bi-search"></i> Buscar Producto del Catálogo</h2>
                    <div class="search-live">
                        <input type="text" id="busquedaProducto" placeholder="Buscar por nombre..." class="mod-search-input cot-live-input"
                               value="<?= htmlspecialchars($busqueda) ?>">
                        <div id="listaProductos" class="lista-sugerencias"></div>
                    </div>
                </div>

                <div class="mod-table-wrap p-24">
                    <h2 class="mod-title mb-16">
                        <i class="bi bi-pencil-square"></i>
                        <span id="formTitulo">Agregar Ítem</span>
                        <span class="mod-badge badge-green ml-8 form-hidden-action" id="badgeAuto">Del catálogo</span>
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
                            <div id="panelGanancias" class="ganancias-panel form-hidden-action">
                                <div class="ganancias-aviso">
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

                                <div id="calc-container" class="mt-16"></div>

                                <!-- Resultado calculado -->
                                <div class="ganancia-resultado">
                                    <div class="ganancia-res-row total-row pt-8 mt-4 border-top-green">
                                        <span>💵 Valor Final con IVA para el Cliente:</span>
                                        <strong id="resValorFinal" class="res-final-value">$0</strong>
                                    </div>
                                    <p class="res-hint-text">* El valor de Estampillas se asigna automáticamente como Precio Unitario del ítem.</p>
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
                                    <span id="lblPrecioFuente" class="price-source-lbl"></span>
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
                            <textarea name="descripcion" id="inpDesc" required maxlength="5000"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="imo-form-group">
                            <label>Imagen del Producto</label>
                            <input type="file" name="foto" id="inpFoto" accept="image/*">
                            <div id="previewFoto" class="mt-8"></div>
                        </div>

                        <!-- Preview IVA en tiempo real -->
                        <div class="preview-iva" id="previewIva">
                            <div class="prev-row"><span>Precio base:</span> <strong id="prevBase">$0</strong></div>
                            <div class="prev-row"><span>IVA:</span> <strong id="prevIva">$0</strong></div>
                            <div class="prev-row total-row"><span>Total unitario:</span> <strong id="prevTotal">$0</strong></div>
                        </div>

                        <div class="imo-modal-footer footer-plain">
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
                <div class="mod-table-wrap p-24">
                    <h2 class="mod-title mb-16"><i class="bi bi-list-check"></i> Ítems de la Cotización</h2>

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
                                             class="item-thumb-img">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 40, '…')) ?>
                                    </td>
                                    <td><?= $qty ?></td>
                                    <td>$<?= number_format($pu, 0, ',', '.') ?></td>
                                    <td><?= $it['iva'] === 'si' ? $pct . '%' : 'No' ?></td>
                                    <td><strong>$<?= number_format($sub, 0, ',', '.') ?></strong></td>
                                    <td class="font-11"><?= htmlspecialchars($it['tiempo_entrega'] ?? '') ?></td>
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
                                    <td colspan="4" class="text-right"><strong>TOTAL ESTIMADO:</strong></td>
                                    <td colspan="3"><strong class="total-highlight">$<?= number_format($totalCot, 0, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($totalItems > 0): ?>
                    <div class="mt-24 text-right">
                        <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary text-nodecor">
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
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF
        }
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
