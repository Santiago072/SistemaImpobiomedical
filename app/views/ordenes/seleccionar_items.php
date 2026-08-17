<?php
/**
 * Vista: Seleccionar ítems para Orden de Compra
 * Variables: $cotizacion, $items, $proveedores, $csrf_token
 */
$pageTitle = 'Nueva Orden de Compra';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-cart-plus-fill"></i> Nueva Orden de Compra</h1>
                <p class="mod-sub">
                    Cotización: <strong><?= htmlspecialchars($cotizacion['numero_cotizacion']) ?></strong>
                    &nbsp;|&nbsp; Cliente: <strong><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></strong>
                </p>
            </div>
            <a href="<?= $basePath ?>?module=cotizaciones&action=consultar"
               class="btn-mod-primary btn-secondary-custom">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'no_items'): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> Debe seleccionar al menos un ítem.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'proveedor_mixto'): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> No puedes combinar ítems de distintos proveedores en una misma orden. Filtra por proveedor y genera una orden por cada uno.</div>
        <?php endif; ?>

        <!-- Alerta dinámica JS para proveedor mixto -->
        <div id="alertaProveedorMixto" class="mod-alert mod-alert-err form-hidden-action"></div>

        <form method="POST" action="<?= $basePath ?>?module=ordenes&action=crear" id="formOrden">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="cotizacion_id" value="<?= (int)$cotizacion['id'] ?>">
            <input type="hidden" name="cotizacion_numero" value="<?= htmlspecialchars($cotizacion['numero_cotizacion']) ?>">

            <!-- ── PASO 1: Selección de ítems ── -->
            <div class="mod-table-wrap section-card-orden">
                <h3 class="section-title-orden">
                    <i class="bi bi-1-circle-fill"></i> Seleccione los productos a pedir
                </h3>

                <!-- Filtro rápido por proveedor -->
                <?php if (count($proveedores) > 1): ?>
                <div class="header-actions-wrap align-center mb-16">
                    <span class="filter-label-text">Filtrar por proveedor:</span>
                    <button type="button" class="oc-filter-btn active" data-proveedor="">
                        <i class="bi bi-grid-fill"></i> Todos
                    </button>
                    <?php foreach ($proveedores as $p): ?>
                    <button type="button" class="oc-filter-btn" data-proveedor="<?= htmlspecialchars($p) ?>">
                        <i class="bi bi-building"></i> <?= htmlspecialchars($p) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php elseif (count($proveedores) === 1): ?>
                <div class="provider-single-box">
                    <i class="bi bi-building"></i> Proveedor: <strong><?= htmlspecialchars($proveedores[0]) ?></strong>
                </div>
                <?php endif; ?>

                <div class="tabla-responsive">
                    <table class="mod-table" id="tablaItems">
                        <thead>
                            <tr>
                                <th class="col-check">
                                    <input type="checkbox" id="checkAll" title="Seleccionar todos">
                                </th>
                                <th>CÓD. PRD. PROVEEDOR</th>
                                <th>Producto / Descripción</th>
                                <th>Proveedor</th>
                                <th class="text-right">Cant. a pedir</th>
                                <th class="text-right">Precio Prov.</th>
                                <th class="text-right">IVA</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr><td colspan="8" class="mod-empty">Esta cotización no tiene ítems.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $it):
                                    $qty   = (int)$it['cantidad'];
                                    $pu    = (float)($it['precio_proveedor'] ?? 0);  // Precio del proveedor, no del cliente
                                    $pct   = (float)($it['porcentaje_iva'] ?? 19);
                                    $aplica= strtolower($it['iva']) === 'si';
                                    $sub   = $pu * $qty;
                                    $ivaV  = $aplica ? $sub * ($pct/100) : 0;
                                    $total = $sub + $ivaV;
                                    $prov  = $it['proveedor'] ?? '';
                                ?>
                                <tr class="item-row" data-proveedor="<?= htmlspecialchars($prov) ?>">
                                    <td class="text-center">
                                        <input type="checkbox" name="items_seleccionados[]"
                                               value="<?= (int)$it['id'] ?>"
                                               class="item-check"
                                               data-id="<?= (int)$it['id'] ?>">
                                        <!-- Datos del ítem como hidden (se envían junto al checkbox) -->
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][titulo]"
                                               value="<?= htmlspecialchars($it['titulo']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][descripcion]"
                                               value="<?= htmlspecialchars($it['descripcion']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][cantidad]"
                                               id="hdn-qty-<?= (int)$it['id'] ?>"
                                               value="<?= $qty ?>">

                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][iva]"
                                               value="<?= htmlspecialchars($it['iva']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][porcentaje_iva]"
                                               value="<?= $pct ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][codigo_proveedor]"
                                               id="cod-prov-hidden-<?= (int)$it['id'] ?>"
                                               value="<?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][proveedor]"
                                               value="<?= htmlspecialchars($prov) ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="oc-cod-input"
                                               placeholder="Código"
                                               value="<?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?>"
                                               maxlength="60"
                                               oninput="document.getElementById('cod-prov-hidden-<?= (int)$it['id'] ?>').value=this.value">
                                    </td>
                                    <td>
                                        <strong class="item-title"><?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 55, '…')) ?></strong>
                                        <?php if (!empty($it['descripcion'])): ?>
                                        <br><span class="item-desc">
                                            <?= htmlspecialchars(mb_strimwidth($it['descripcion'], 0, 80, '…')) ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prov): ?>
                                        <span class="tag-code">
                                            <?= htmlspecialchars($prov) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <input type="number"
                                               min="1"
                                               value="<?= $qty ?>"
                                               class="qty-input"
                                               data-id="<?= (int)$it['id'] ?>"
                                               title="Cantidad a pedir"
                                               oninput="document.getElementById('hdn-qty-<?= (int)$it['id'] ?>').value=this.value; actualizarFila(this.closest('tr'))">
                                    </td>
                                    <td class="text-right nowrap">
                                        <input type="number"
                                               step="0.01"
                                               value="<?= $pu ?>"
                                               class="precio-input"
                                               data-id="<?= (int)$it['id'] ?>"
                                               oninput="document.getElementById('precio-hidden-<?= (int)$it['id'] ?>').value=this.value; actualizarFila(this.closest('tr'))">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][precio]"
                                               id="precio-hidden-<?= (int)$it['id'] ?>"
                                               value="<?= $pu ?>">
                                    </td>
                                    <td class="text-right nowrap"><?= $aplica ? $pct . '%' : '0%' ?></td>
                                    <td class="celda-total text-right nowrap font-bold"
                                        data-pu="<?= $pu ?>" data-pct="<?= $pct ?>" data-aplica="<?= $aplica ? 1 : 0 ?>">
                                        $ <?= number_format($total, 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen de selección -->
                <div id="resumenSeleccion" class="resumen-seleccion-box">
                    <div class="header-actions-wrap align-center mb-10">
                        <span>Ítems seleccionados: <strong id="cntItems">0</strong></span>
                        <span>Subtotal: <strong id="cntSubtotal">$ 0</strong></span>
                        <span>IVA: <strong id="cntIva">$ 0</strong></span>
                    </div>
                    <div class="header-actions-wrap align-center pt-10 border-top-teal">
                        <span class="text-warning-gold">Retención (<span id="lblRetPct">2.5</span>%): <strong id="cntRet" class="text-warning-gold">$ 0</strong></span>
                        <div class="text-total-wrap font-bold">TOTAL: <span id="cntTotal" class="resumen-total-text">$ 0</span></div>
                    </div>
                </div>
            </div>

            <!-- ── PASO 2: Datos del proveedor / orden ── -->
            <div class="mod-table-wrap section-card-orden">
                <h3 class="section-title-orden">
                    <i class="bi bi-2-circle-fill"></i> Datos de la Orden de Compra
                </h3>

                <div class="grid-form-fields">

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-building"></i> Proveedor (TO:) <span class="required-star">*</span></label>
                        <input type="text" name="proveedor" class="oc-input" required
                               placeholder="Nombre del proveedor" maxlength="200"
                               value="<?= htmlspecialchars($proveedores[0] ?? '') ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-hash"></i> NIT del Proveedor</label>
                        <input type="text" name="proveedor_nit" class="oc-input"
                               placeholder="Ej: 79625307-6" maxlength="30">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-person-badge"></i> Tipo de Contribuyente</label>
                        <input type="text" name="tipo_contribuyente" class="oc-input"
                               placeholder="Ej: PERSON NATURAL O SUCCESION LIQUIDA" maxlength="100">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-calendar-date"></i> Fecha</label>
                        <input type="date" name="fecha" class="oc-input"
                               value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-credit-card"></i> Condiciones de Pago</label>
                        <input type="text" name="condiciones_pago" class="oc-input"
                               placeholder="Ej: Según acuerdo" maxlength="100"
                               value="Según acuerdo">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-percent"></i> IVA</label>
                        <input type="text" name="iva" class="oc-input"
                               placeholder="Ej: 19%" maxlength="20" value="19%">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-person-lines-fill"></i> Departamento de Compras (Responsable)</label>
                        <input type="text" name="departamento_compras" class="oc-input"
                               placeholder="Nombre del responsable" maxlength="100"
                               value="<?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-calculator"></i> Retención (%) — Se aplica sobre subtotal</label>
                        <input type="number" name="retencion" class="oc-input" id="inputRetencion"
                               placeholder="Ej: 2.5" min="0" max="100" step="0.01" value="2.5">
                    </div>

                </div>

                <div class="bank-details-wrap">
                    <h4 class="section-title-orden">
                        <i class="bi bi-bank"></i> Datos Bancarios (No visibles en PDF)
                    </h4>
                    <div class="grid-form-fields">
                        <div class="oc-field-group">
                            <label class="oc-label">Nombre del Banco</label>
                            <input type="text" name="banco_nombre" class="oc-input"
                                   placeholder="Ej: Bancolombia" maxlength="100">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Número de Cuenta</label>
                            <input type="text" name="banco_cuenta" class="oc-input"
                                   placeholder="Ej: 123456789" maxlength="100">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Tipo de Cuenta</label>
                            <select name="banco_tipo_cuenta" class="oc-input">
                                <option value="">Seleccione...</option>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-16">
                    <label class="oc-label"><i class="bi bi-chat-left-text"></i> Nota / Descripción (izquierda del PDF)</label>
                    <textarea name="nota" class="oc-input oc-textarea-note" rows="4"
                              placeholder="Ej:&#10;THANK YOU FOR YOUR BUSINESS !!&#10;NOTA:&#10;1. Compartir factura&#10;2. Compartir Guía de despacho"
                              maxlength="1000">THANK YOU FOR YOUR BUSINESS !!

NOTA:
1. Compartir factura
2. Compartir Guía de despacho
3. Carta de garantía
4. Fichas técnicas</textarea>
                </div>

                <div class="order-submit-actions">
                    <a href="<?= $basePath ?>?module=cotizaciones&action=consultar"
                       class="btn-mod-primary btn-secondary-custom">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="imo-btn-save" id="btnGenerarOrden" disabled>
                        <i class="bi bi-file-earmark-arrow-down-fill"></i> Generar Orden de Compra
                    </button>
                </div>
            </div>
        </form>

    </main>
</div>

<script>
(function(){
    const checkAll = document.getElementById('checkAll');
    const checks   = document.querySelectorAll('.item-check');
    const btnGen   = document.getElementById('btnGenerarOrden');
    const cntItems = document.getElementById('cntItems');
    const cntSub   = document.getElementById('cntSubtotal');

    // ── Actualizar cantidad hidden + total de fila ─────────────────────────
    document.querySelectorAll('.qty-input').forEach(inp => {
        // Evitar que Enter en este input envíe el form
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        inp.addEventListener('input', function() {
            const id  = this.dataset.id;
            let val   = parseInt(this.value) || 1;
            if (val < 1)   { val = 1;   this.value = 1; }

            const hdnQty = document.getElementById('hdn-qty-' + id);
            if (hdnQty) hdnQty.value = val;

            const celdaTot = this.closest('tr').querySelector('.celda-total');
            if (celdaTot) {
                const pu    = parseFloat(celdaTot.dataset.pu)   || 0;
                const pct   = parseFloat(celdaTot.dataset.pct)  || 0;
                const aplica= parseInt(celdaTot.dataset.aplica) === 1;
                const sub   = pu * val;
                const iva   = aplica ? sub * (pct / 100) : 0;
                celdaTot.textContent = '$ ' + Math.round(sub + iva).toLocaleString('es-CO');
            }
            actualizarResumen();
        });
    });

    // ── Actualizar precio y total de fila ──────────────────────────────────
    document.querySelectorAll('.precio-input').forEach(inp => {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
        inp.addEventListener('input', function() {
            const id  = this.dataset.id;
            const pu  = parseFloat(this.value) || 0;

            const hdnPrecio = document.getElementById('precio-hidden-' + id);
            if (hdnPrecio) hdnPrecio.value = pu;

            const row      = this.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            const celdaTot = row.querySelector('.celda-total');
            
            if (celdaTot && qtyInput) {
                const qty   = parseInt(qtyInput.value) || 1;
                const pct   = parseFloat(celdaTot.dataset.pct)  || 0;
                const aplica= parseInt(celdaTot.dataset.aplica) === 1;
                const sub   = pu * qty;
                const iva   = aplica ? sub * (pct / 100) : 0;
                celdaTot.textContent = '$ ' + Math.round(sub + iva).toLocaleString('es-CO');
                celdaTot.dataset.pu = pu;  // Actualizar el dataset también
            }
            actualizarResumen();
        });
    });

    // ── Evitar Enter en campos de código ──────────────────────────────────
    document.querySelectorAll('.oc-cod-input').forEach(inp => {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    });

    // ── Actualizar resumen cuando cambia el % de retención ────────────────
    const retInput = document.getElementById('inputRetencion');
    if (retInput) {
        retInput.addEventListener('input', actualizarResumen);
    }

    // ── Resumen de selección ──────────────────────────────────────────────
    const alertaProveedor = document.getElementById('alertaProveedorMixto');

    function actualizarResumen() {
        let cnt = 0, subSinIva = 0, ivaTotal = 0;
        const proveedoresSeleccionados = new Set();

        checks.forEach(c => {
            if (c.checked) {
                cnt++;
                const row      = c.closest('tr');
                const prov     = row.dataset.proveedor || '';
                if (prov) proveedoresSeleccionados.add(prov);

                // Leer precio, cantidad, IVA de la fila
                const qtyInp = row.querySelector('.qty-input');
                const puInp  = row.querySelector('.precio-input');
                const celdaTot = row.querySelector('.celda-total');

                const qty    = parseInt(qtyInp ? qtyInp.value : 1) || 1;
                const pu     = parseFloat(puInp ? puInp.value : 0) || 0;
                const pct    = parseFloat(celdaTot ? celdaTot.dataset.pct : 0) || 0;
                const aplica = parseInt(celdaTot ? celdaTot.dataset.aplica : 0) === 1;
                const sub    = pu * qty;
                subSinIva += sub;
                ivaTotal  += aplica ? sub * (pct / 100) : 0;
            }
        });

        // Leer porcentaje de retención del input
        const retInput = document.getElementById('inputRetencion');
        const retPct   = retInput ? (parseFloat(retInput.value) || 0) : 0;
        const retVal   = subSinIva * (retPct / 100);
        const totalNeto = subSinIva + ivaTotal - retVal;

        cntItems.textContent = cnt;
        cntSub.textContent   = '$ ' + Math.round(subSinIva).toLocaleString('es-CO');

        const cntIva  = document.getElementById('cntIva');
        const cntRet  = document.getElementById('cntRet');
        const cntTot  = document.getElementById('cntTotal');
        const lblRet  = document.getElementById('lblRetPct');
        if (cntIva)  cntIva.textContent  = '$ ' + Math.round(ivaTotal).toLocaleString('es-CO');
        if (cntRet)  cntRet.textContent  = '$ ' + Math.round(retVal).toLocaleString('es-CO');
        if (cntTot)  cntTot.textContent  = '$ ' + Math.round(totalNeto).toLocaleString('es-CO');
        if (lblRet)  lblRet.textContent  = retPct.toString();

        const provMixto = proveedoresSeleccionados.size > 1;
        if (alertaProveedor) {
            if (provMixto) {
                const lista = Array.from(proveedoresSeleccionados).join(', ');
                alertaProveedor.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> No puedes mezclar proveedores en una misma orden. Seleccionados: <strong>' + lista + '</strong>. Filtra por proveedor y genera una orden por cada uno.';
                alertaProveedor.style.display = 'flex';
            } else {
                alertaProveedor.style.display = 'none';
            }
        }

        btnGen.disabled = cnt === 0 || provMixto;
        checkAll.indeterminate = cnt > 0 && cnt < checks.length;
        checkAll.checked       = cnt === checks.length && checks.length > 0;
    }

    window.actualizarFila = function(row) {
        const qtyInp = row.querySelector('.qty-input');
        const puInp  = row.querySelector('.precio-input');
        const celdaTot = row.querySelector('.celda-total');
        if (!qtyInp || !puInp || !celdaTot) return;
        
        const qty = parseInt(qtyInp.value) || 0;
        const pu  = parseFloat(puInp.value) || 0;
        const pct = parseFloat(celdaTot.dataset.pct) || 0;
        const aplica = parseInt(celdaTot.dataset.aplica) === 1;
        
        const sub = pu * qty;
        const ivaVal = aplica ? sub * (pct / 100) : 0;
        const total = sub + ivaVal;
        
        celdaTot.textContent = '$ ' + Math.round(total).toLocaleString('es-CO');
        actualizarResumen();
    };

    checkAll.addEventListener('change', function(){
        document.querySelectorAll('.item-row:not(.oculta) .item-check').forEach(c => c.checked = this.checked);
        actualizarResumen();
    });

    checks.forEach(c => c.addEventListener('change', actualizarResumen));

    // ── Filtro por proveedor ──────────────────────────────────────────────
    document.querySelectorAll('.oc-filter-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            document.querySelectorAll('.oc-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const prov = this.dataset.proveedor;
            document.querySelectorAll('.item-row').forEach(row => {
                if (!prov || row.dataset.proveedor === prov) {
                    row.classList.remove('oculta');
                } else {
                    row.classList.add('oculta');
                    row.querySelector('.item-check').checked = false;
                }
            });
            actualizarResumen();
        });
    });

    // ── Auto-rellenar proveedor al seleccionar un ítem ────────────────────
    checks.forEach(c => {
        c.addEventListener('change', function(){
            if (this.checked) {
                const row  = this.closest('tr');
                const prov = row.dataset.proveedor;
                if (prov) {
                    const inp = document.querySelector('input[name="proveedor"]');
                    if (!inp.value.trim()) inp.value = prov;
                }
            }
        });
    });
})();
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
