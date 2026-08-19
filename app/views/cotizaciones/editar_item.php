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

        <div class="mod-form-panel p-24 mx-auto">
            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=editar_item&id=<?= intval($datos['id']) ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="item_id" value="<?= intval($datos['id']) ?>">
                <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($datos['foto']) ?>">
                <input type="hidden" name="porcentaje_utilidad" id="hdnPctUtilidad" value="<?= htmlspecialchars($datos['porcentaje_utilidad'] ?? 0) ?>">
                <input type="hidden" name="flete" id="hdnFlete" value="<?= htmlspecialchars($datos['flete'] ?? 0) ?>">
                <input type="hidden" name="calibracion" id="hdnCalibracion" value="<?= htmlspecialchars($datos['calibracion'] ?? 0) ?>">
                <input type="hidden" name="estampillas" id="hdnEstampillas" value="<?= htmlspecialchars($datos['estampillas'] ?? 0) ?>">
                <input type="hidden" name="calc_ops" id="hdnCalcOps" value="<?= htmlspecialchars($datos['calc_ops'] ?? '{}') ?>">

                <div class="cot-edit-grid">
                    <!-- Columna Izquierda: Información del Producto -->
                    <div class="cot-edit-left">
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
                                <input type="text" name="codigo_producto" value="<?= htmlspecialchars($datos['codigo_producto'] ?? '') ?>" maxlength="60" placeholder="Ej: MQ-001">
                            </div>
                        </div>

                        <div class="imo-form-group">
                            <label>Descripción *</label>
                            <textarea name="descripcion" required maxlength="5000"><?= htmlspecialchars($datos['descripcion']) ?></textarea>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="cantidad" id="inpCantidad" value="<?= intval($datos['cantidad']) ?>" min="1" required>
                            </div>
                            <div class="imo-form-group">
                                <label>Precio Unitario Final (sin IVA) *</label>
                                <input type="number" step="0.01" name="precio" id="inpPrecio" value="<?= htmlspecialchars($datos['precio']) ?>" required>
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Tiempo de Entrega</label>
                                <input type="text" name="tiempo_entrega" value="<?= htmlspecialchars($datos['tiempo_entrega'] ?? '') ?>" placeholder="Ej: 5 A 15 DÍAS HÁBILES" maxlength="120">
                            </div>
                            <div class="imo-form-group">
                                <label>¿Aplica IVA?</label>
                                <select name="iva" id="inpIva" onchange="toggleIva(this.value)">
                                    <option value="si" <?= $datos['iva'] === 'si' ? 'selected' : '' ?>>Sí</option>
                                    <option value="no" <?= $datos['iva'] === 'no' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="imo-form-group" id="grupoIvaPct">
                            <label>% IVA</label>
                            <input type="number" name="porcentaje_iva" id="inpPctIva" min="0" max="100" step="0.01" value="<?= floatval($datos['porcentaje_iva'] ?? 19) ?>">
                        </div>

                        <div class="imo-form-group">
                            <label>Imagen del Producto</label>
                            <?php if (!empty($datos['foto'])): ?>
                                <div class="mb-8">
                                    <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($datos['foto']) ?>" class="item-preview-edit">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="foto" accept="image/*">
                        </div>
                    </div>

                    <!-- Columna Derecha: Precios y Calculadora Dinámica de Ganancias -->
                    <div class="cot-edit-right">
                        <!-- Fila de Precios del Proveedor -->
                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Proveedor</label>
                                <input type="text" name="proveedor" id="inpProveedor" value="<?= htmlspecialchars($datos['proveedor'] ?? '') ?>" placeholder="Ej: ALENO SAS">
                            </div>
                            <div class="imo-form-group">
                                <label>Código Proveedor</label>
                                <input type="text" name="codigo_proveedor" id="inpCodigoProveedor" value="<?= htmlspecialchars($datos['codigo_proveedor'] ?? '') ?>" placeholder="Ej: PROV-001">
                            </div>
                        </div>

                        <div class="imo-form-group">
                            <label class="font-bold text-teal">
                                <i class="bi bi-calculator"></i> Calculadora de Ganancias Dinámica
                            </label>
                            
                            <div class="mb-12">
                                <label class="font-12">Precio Proveedor Base ($) *</label>
                                <input type="number" step="0.01" name="precio_proveedor" id="inpPrecioProveedor" 
                                       value="<?= htmlspecialchars($datos['precio_proveedor'] ?? 0) ?>" 
                                       oninput="calcularTotales()" placeholder="0.00">
                            </div>

                            <!-- Contenedor donde JS dibuja las etapas y operaciones -->
                            <div id="calc-container"></div>
                        </div>

                        <!-- Resultado calculado final para el cliente -->
                        <div class="ganancia-resultado">
                            <div class="ganancia-res-row total-row pt-8 mt-4 border-top-green">
                                <span>💵 Valor Final con IVA para el Cliente:</span>
                                <strong id="resValorFinal" class="res-final-value">$0</strong>
                            </div>
                            <p class="res-hint-text">* El valor de Estampillas se asigna automáticamente como Precio Unitario del ítem.</p>
                        </div>
                    </div>
                </div>

                <div class="imo-modal-footer">
                    <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="imo-btn-cancel text-nodecor"><i class="bi bi-x-lg"></i> Cancelar</a>
                    <button type="submit" class="btn-mod-primary"><i class="bi bi-save-fill"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
// Estado inicial de la calculadora cargado desde la BD
<?php
$calcOpsJson = $datos['calc_ops'] ?? '{}';
$calcOps = json_decode($calcOpsJson, true) ?: [];

// Si existe calc_ops con estructura completa, usarlo; si no, crear uno con los valores antiguos
if (!empty($calcOps) && isset($calcOps['utilidad'])) {
    // Ya tiene la estructura JSON de operaciones
    $stateToLoad = $calcOps;
} else {
    // Valores antiguos: convertir a estructura de operaciones (compatibilidad)
    $stateToLoad = [
        'utilidad'    => (!empty($datos['porcentaje_utilidad']) || $datos['porcentaje_utilidad'] == 0) 
                         ? [['tipo' => 'suma', 'valor' => (float)$datos['porcentaje_utilidad']]] 
                         : [['tipo' => 'div_pct', 'valor' => 0.70]],
        'flete'       => (!empty($datos['flete']) || $datos['flete'] == 0) 
                         ? [['tipo' => 'suma', 'valor' => (float)$datos['flete']]] 
                         : [],
        'calibracion' => (!empty($datos['calibracion']) || $datos['calibracion'] == 0) 
                         ? [['tipo' => 'suma', 'valor' => (float)$datos['calibracion']]] 
                         : [],
        'estampillas' => (!empty($datos['estampillas']) || $datos['estampillas'] == 0) 
                         ? [['tipo' => 'suma', 'valor' => (float)$datos['estampillas']]] 
                         : [],
    ];
}
?>
let calcState = <?= json_encode($stateToLoad) ?>;

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

    // ── Autocompletar Precio Unitario con el valor de Estampillas ─────────
    if (totalEstamp > 0) {
        const inpPrecio = document.getElementById('inpPrecio');
        if (inpPrecio) {
            inpPrecio.value = Math.round(totalEstamp);
        }
    }

    const precioUnitario = (totalEstamp > 0) ? totalEstamp : (parseFloat(document.getElementById('inpPrecio')?.value) || 0);

    const ivaVal = document.getElementById('inpIva')?.value || 'si';
    const pctIva = parseFloat(document.getElementById('inpPctIva')?.value) || 0;
    const ivaFinal = ivaVal === 'si' ? precioUnitario * (pctIva / 100) : 0;
    const resFinal = document.getElementById('resValorFinal');
    if (resFinal) resFinal.textContent = '$' + Math.round(precioUnitario + ivaFinal).toLocaleString('es-CO');
}

document.getElementById('inpPrecio')?.addEventListener('input', calcularTotales);
document.getElementById('inpPctIva')?.addEventListener('input', calcularTotales);

function toggleIva(val) {
    const group = document.getElementById('grupoIvaPct');
    if (group) {
        group.style.display = (val === 'si') ? 'block' : 'none';
    }
    if (val !== 'si') {
        const inp = document.getElementById('inpPctIva');
        if (inp) inp.value = 0;
    }
    calcularTotales();
}

// Inicializar
toggleIva(document.getElementById('inpIva').value);
renderCalculadoraInputs();

// Serializar calcState antes de enviar
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('hdnCalcOps').value = JSON.stringify(calcState);
});
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
