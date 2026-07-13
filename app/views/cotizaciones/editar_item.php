<?php
/**
 * Vista: Editar ítem de cotización
 * Variables: $datos, $mensajeError, $csrf_token
 */
$pageTitle = 'Editar Ítem';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-pencil-square"></i> Editar Ítem de Cotización</h1>
                <p class="mod-sub">Modifica los detalles o las ganancias del producto</p>
            </div>
        </div>

        <?php if ($mensajeError): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="mod-form-panel" style="max-width:900px; margin:0 auto; padding:24px;">
            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=editar_item&id=<?= intval($datos['id']) ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="item_id" value="<?= intval($datos['id']) ?>">
                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($datos['foto']) ?>">
                <input type="hidden" name="porcentaje_utilidad" id="hdnPctUtilidad" value="<?= htmlspecialchars($datos['porcentaje_utilidad'] ?? 0) ?>">
                <input type="hidden" name="flete" id="hdnFlete" value="<?= htmlspecialchars($datos['flete'] ?? 0) ?>">
                <input type="hidden" name="calibracion" id="hdnCalibracion" value="<?= htmlspecialchars($datos['calibracion'] ?? 0) ?>">
                <input type="hidden" name="estampillas" id="hdnEstampillas" value="<?= htmlspecialchars($datos['estampillas'] ?? 0) ?>">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <!-- Columna Izquierda -->
                    <div>
                        <div class="imo-form-group">
                            <label>Nombre del Producto *</label>
                            <input type="text" name="titulo" value="<?= htmlspecialchars($datos['titulo']) ?>" required maxlength="100">
                        </div>
                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Categoría</label>
                                <select name="categoria">
                                    <option value="">-- Seleccionar categoría --</option>
                                    <?php
                                    $cats = ['Insumo Medico Quirurgico', 'Insumo Medico Odontologico', 'Mobiliario Hospitalario', 'Equipo Medico', 'Accesorios', 'Repuestos', 'Equipo de Terapia', 'Medicamentos'];
                                    foreach($cats as $c) {
                                        $sel = ($datos['categoria'] ?? '') === $c ? 'selected' : '';
                                        echo "<option value=\"$c\" $sel>$c</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="imo-form-group">
                                <label>Código del Producto</label>
                                <input type="text" name="codigo_producto" value="<?= htmlspecialchars($datos['codigo_producto'] ?? '') ?>" maxlength="60">
                            </div>
                        </div>
                        <div class="imo-form-group">
                            <label>Descripción *</label>
                            <textarea name="descripcion" required maxlength="5000" rows="3" style="padding:11px 14px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:14px;resize:vertical;outline:none;width:100%;"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
                        </div>
                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="cantidad" value="<?= intval($datos['cantidad']) ?>" required min="1">
                            </div>
                            <div class="imo-form-group">
                                <label>Tiempo de Entrega</label>
                                <input type="text" name="tiempo_entrega" value="<?= htmlspecialchars($datos['tiempo_entrega'] ?? '') ?>" maxlength="120">
                            </div>
                        </div>
                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Aplicar IVA *</label>
                                <select name="iva" id="inpIva" required onchange="toggleIva(this.value)">
                                    <option value="si" <?= $datos['iva'] === 'si' ? 'selected' : '' ?>>Sí</option>
                                    <option value="no" <?= $datos['iva'] === 'no' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="imo-form-group" id="groupPctIva">
                                <label>% IVA *</label>
                                <input type="number" name="porcentaje_iva" id="inpPctIva" value="<?= floatval($datos['porcentaje_iva'] ?? 19) ?>" min="0" max="100" step="0.01" oninput="calcularTotales()">
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div>
                        <div class="imo-form-group">
                            <label>Foto del Producto</label>
                            <?php if (!empty($datos['foto'])): ?>
                            <div style="margin-bottom:10px;">
                                <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($datos['foto']) ?>" style="height:60px; border-radius:8px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" name="foto" accept="image/*">
                        </div>

                        <!-- Calculadora de Ganancias -->
                        <div style="background:#ecfdf5; border:1px solid #10b981; border-radius:12px; margin-top:20px; overflow:hidden;">
                            <div style="background:#10b981; color:#fff; padding:12px 20px; font-weight:600; font-size:14px;">
                                Porcentajes Ganancias (Calculadora Dinámica)
                            </div>
                            <div style="padding:20px;">
                                <div class="imo-form-row">
                                    <div class="imo-form-group">
                                        <label>Precio Proveedor Base ($)</label>
                                        <input type="number" name="precio_proveedor" id="inpPrecioProveedor" min="0" step="0.01" value="<?= floatval($datos['precio_proveedor'] ?? 0) ?>" oninput="calcularTotales()">
                                    </div>
                                    <div class="imo-form-group">
                                        <label>Proveedor</label>
                                        <input type="text" name="proveedor" value="<?= htmlspecialchars($datos['proveedor'] ?? '') ?>" maxlength="100">
                                    </div>
                                </div>
                                <div class="imo-form-row">
                                    <div class="imo-form-group">
                                        <label>Código Proveedor</label>
                                        <input type="text" name="codigo_proveedor" value="<?= htmlspecialchars($datos['codigo_proveedor'] ?? '') ?>" maxlength="60">
                                    </div>
                                </div>

                                <div id="calc-container" style="margin-top: 15px;"></div>

                                <div class="ganancia-resultado" style="background:#fff; border:1.5px solid #d1fae5; border-radius:8px; padding:12px; margin-top:15px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <span style="font-size:14px; font-weight:600; color:#065f46;">💰 Valor Final con IVA para el Cliente:</span>
                                        <strong id="resValorFinal" style="color:#059669; font-size:18px;">$0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="imo-modal-footer" style="padding-top:20px;">
                    <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="imo-btn-cancel" style="text-decoration:none;"><i class="bi bi-x-lg"></i> Cancelar</a>
                    <button type="submit" class="btn-mod-primary"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.calc-etapa { background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin-bottom:12px; }
.calc-etapa h4 { font-size:12px; font-weight:700; color:#374151; margin-bottom:10px; display:flex; justify-content:space-between; }
.calc-etapa h4 span { color:#059669; }
.calc-op-row { display:flex; gap:10px; margin-bottom:8px; }
.calc-op-row select, .calc-op-row input { flex:1; border:1px solid #d1d5db; border-radius:6px; padding:6px 10px; font-size:12px; outline:none; }
.btn-calc-del { background:#fee2e2; color:#ef4444; border:none; border-radius:6px; padding:0 10px; cursor:pointer; }
.btn-calc-add { background:#e0f2fe; color:#0284c7; border:none; border-radius:6px; padding:4px 10px; font-size:11px; font-weight:600; cursor:pointer; }
</style>

<script>
// Estado inicial de la calculadora cargado desde la BD
let calcState = {
    utilidad:    [{ tipo: 'suma', valor: <?= floatval($datos['porcentaje_utilidad'] ?? 0) ?> }],
    flete:       [{ tipo: 'suma', valor: <?= floatval($datos['flete'] ?? 0) ?> }],
    calibracion: [{ tipo: 'suma', valor: <?= floatval($datos['calibracion'] ?? 0) ?> }],
    estampillas: [{ tipo: 'suma', valor: <?= floatval($datos['estampillas'] ?? 0) ?> }]
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
    calcState[etapa][index].valor = valor;
    calcularTotales();
}

function aplicarOperaciones(valorBase, operaciones) {
    let acumulado = parseFloat(valorBase) || 0;
    operaciones.forEach(op => {
        let v = parseFloat(op.valor) || 0;
        if (op.tipo === 'suma') acumulado += v;
        if (op.tipo === 'mult_pct') acumulado += acumulado * (v / 100);
        if (op.tipo === 'div_pct' && v > 0) acumulado = acumulado / v;
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
    
    const totalUtilidad = aplicarOperaciones(precioBase, calcState.utilidad);
    const totalFlete = aplicarOperaciones(totalUtilidad, calcState.flete);
    const totalCalib = aplicarOperaciones(totalFlete, calcState.calibracion);
    const totalEstamp = aplicarOperaciones(totalCalib, calcState.estampillas);

    document.getElementById('hdnPctUtilidad').value = (totalUtilidad - precioBase).toFixed(2);
    document.getElementById('hdnFlete').value = (totalFlete - totalUtilidad).toFixed(2);
    document.getElementById('hdnCalibracion').value = (totalCalib - totalFlete).toFixed(2);
    document.getElementById('hdnEstampillas').value = (totalEstamp - totalCalib).toFixed(2);

    const formatMoney = v => 'Acumulado: $' + Math.round(v).toLocaleString('es-CO');

    const elUtil = document.getElementById('acum_utilidad'); if(elUtil) elUtil.textContent = formatMoney(totalUtilidad);
    const elFlete = document.getElementById('acum_flete'); if(elFlete) elFlete.textContent = formatMoney(totalFlete);
    const elCalib = document.getElementById('acum_calibracion'); if(elCalib) elCalib.textContent = formatMoney(totalCalib);
    const elEstamp = document.getElementById('acum_estampillas'); if(elEstamp) elEstamp.textContent = formatMoney(totalEstamp);

    const ivaVal = document.getElementById('inpIva')?.value || 'si';
    const pctIva = parseFloat(document.getElementById('inpPctIva')?.value) || 0;
    const ivaFinal = ivaVal === 'si' ? totalEstamp * (pctIva / 100) : 0;
    const resFinal = document.getElementById('resValorFinal');
    if (resFinal) resFinal.textContent = '$' + Math.round(totalEstamp + ivaFinal).toLocaleString('es-CO');
}

function toggleIva(val) {
    const group = document.getElementById('groupPctIva');
    if (val === 'si') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
        document.getElementById('inpPctIva').value = 0;
    }
    calcularTotales();
}

// Inicializar
toggleIva(document.getElementById('inpIva').value);
renderCalculadoraInputs();
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
