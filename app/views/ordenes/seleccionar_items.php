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

        <div class="mod-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 class="mod-title"><i class="bi bi-cart-plus-fill"></i> Nueva Orden de Compra</h1>
                <p class="mod-sub">
                    Cotización: <strong><?= htmlspecialchars($cotizacion['numero_cotizacion']) ?></strong>
                    &nbsp;|&nbsp; Cliente: <strong><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></strong>
                </p>
            </div>
            <a href="<?= $basePath ?>?module=cotizaciones&action=consultar"
               class="btn-mod-primary" style="background:#6b7280; border:none;">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'no_items'): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> Debe seleccionar al menos un ítem.</div>
        <?php endif; ?>

        <form method="POST" action="<?= $basePath ?>?module=ordenes&action=crear" id="formOrden">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="cotizacion_id" value="<?= (int)$cotizacion['id'] ?>">
            <input type="hidden" name="cotizacion_numero" value="<?= htmlspecialchars($cotizacion['numero_cotizacion']) ?>">

            <!-- ── PASO 1: Selección de ítems ── -->
            <div class="mod-table-wrap" style="padding:24px; margin-bottom:20px;">
                <h3 style="color:var(--amber); margin-bottom:16px; font-size:15px;">
                    <i class="bi bi-1-circle-fill"></i> Seleccione los productos a pedir
                </h3>

                <!-- Filtro rápido por proveedor -->
                <?php if (count($proveedores) > 1): ?>
                <div style="margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                    <span style="font-size:13px; color:var(--text-soft); font-weight:600;">Filtrar por proveedor:</span>
                    <button type="button" class="oc-filter-btn active" data-proveedor="">
                        <i class="bi bi-grid-fill" style="font-size:10px;"></i> Todos
                    </button>
                    <?php foreach ($proveedores as $p): ?>
                    <button type="button" class="oc-filter-btn" data-proveedor="<?= htmlspecialchars($p) ?>">
                        <i class="bi bi-building" style="font-size:10px;"></i> <?= htmlspecialchars($p) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php elseif (count($proveedores) === 1): ?>
                <div style="margin-bottom:14px; padding:8px 14px; background:#e8f8f8; border:1px solid #10757e; border-radius:8px; font-size:12px; color:#0a4f55; font-weight:600;">
                    <i class="bi bi-building"></i> Proveedor: <strong><?= htmlspecialchars($proveedores[0]) ?></strong>
                </div>
                <?php endif; ?>

                <div class="tabla-responsive">
                    <table class="mod-table" id="tablaItems">
                        <thead>
                            <tr>
                                <th style="width:44px;">
                                    <input type="checkbox" id="checkAll" title="Seleccionar todos"
                                           style="width:18px;height:18px;cursor:pointer;">
                                </th>
                                <th>CÓD. PRD. PROVEEDOR</th>
                                <th>Producto / Descripción</th>
                                <th>Proveedor</th>
                                <th style="text-align:right;">Cant. a pedir</th>
                                <th style="text-align:right;">Precio U.</th>
                                <th style="text-align:right;">IVA</th>
                                <th style="text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                            <tr><td colspan="8" class="mod-empty">Esta cotización no tiene ítems.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $it):
                                    $qty   = (int)$it['cantidad'];
                                    $pu    = (float)$it['precio'];
                                    $pct   = (float)($it['porcentaje_iva'] ?? 19);
                                    $aplica= strtolower($it['iva']) === 'si';
                                    $sub   = $pu * $qty;
                                    $ivaV  = $aplica ? $sub * ($pct/100) : 0;
                                    $total = $sub + $ivaV;
                                    $prov  = $it['proveedor'] ?? '';
                                ?>
                                <tr class="item-row" data-proveedor="<?= htmlspecialchars($prov) ?>">
                                    <td style="text-align:center;">
                                        <input type="checkbox" name="items_seleccionados[]"
                                               value="<?= (int)$it['id'] ?>"
                                               class="item-check"
                                               data-id="<?= (int)$it['id'] ?>"
                                               style="width:18px;height:18px;cursor:pointer;">
                                        <!-- Datos del ítem como hidden (se envían junto al checkbox) -->
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][titulo]"
                                               value="<?= htmlspecialchars($it['titulo']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][descripcion]"
                                               value="<?= htmlspecialchars($it['descripcion']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][cantidad]"
                                               id="hdn-qty-<?= (int)$it['id'] ?>"
                                               value="<?= $qty ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][precio]"
                                               value="<?= $pu ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][iva]"
                                               value="<?= htmlspecialchars($it['iva']) ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][porcentaje_iva]"
                                               value="<?= $pct ?>">
                                        <input type="hidden" name="items_data[<?= (int)$it['id'] ?>][codigo_proveedor]"
                                               class="cod-prov-hidden-<?= (int)$it['id'] ?>"
                                               value="<?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               class="oc-cod-input"
                                               placeholder="Código"
                                               value="<?= htmlspecialchars($it['codigo_proveedor'] ?? '') ?>"
                                               maxlength="60"
                                               style="width:90px; padding:5px 8px; border-radius:6px; border:1.5px solid rgba(45,190,203,.25); background:rgba(255,255,255,.06); color:var(--white); font-size:12px;"
                                               oninput="document.querySelector('.cod-prov-hidden-<?= (int)$it['id'] ?>').value=this.value">
                                    </td>
                                    <td>
                                        <strong style="font-size:13px;"><?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 55, '…')) ?></strong>
                                        <?php if (!empty($it['descripcion'])): ?>
                                        <br><span style="font-size:11px;color:var(--text-soft);">
                                            <?= htmlspecialchars(mb_strimwidth($it['descripcion'], 0, 80, '…')) ?>
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($prov): ?>
                                        <span style="font-size:12px; background:rgba(45,190,203,.12); padding:3px 8px; border-radius:20px; white-space:nowrap;">
                                            <?= htmlspecialchars($prov) ?>
                                        </span>
                                        <?php else: ?>
                                        <span style="font-size:11px; color:var(--text-soft);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <input type="number"
                                               min="1" max="<?= $qty ?>"
                                               value="<?= $qty ?>"
                                               class="qty-input"
                                               data-id="<?= (int)$it['id'] ?>"
                                               title="Máx: <?= $qty ?>"
                                               style="width:65px; padding:4px 6px; border-radius:6px; border:1.5px solid rgba(45,190,203,.3); background:rgba(255,255,255,.08); color:inherit; font-size:13px; font-weight:600; text-align:center;">
                                    </td>
                                    <td style="text-align:right; white-space:nowrap;">$ <?= number_format($pu, 0, ',', '.') ?></td>
                                    <td style="text-align:right; white-space:nowrap;"><?= $aplica ? $pct . '%' : '0%' ?></td>
                                    <td class="celda-total" style="text-align:right; white-space:nowrap; font-weight:600;"
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
                <div id="resumenSeleccion" style="margin-top:14px; padding:12px 16px; background:rgba(45,190,203,.08); border:1px solid rgba(45,190,203,.2); border-radius:8px; display:flex; gap:24px; flex-wrap:wrap; align-items:center; font-size:13px;">
                    <span>Ítems seleccionados: <strong id="cntItems">0</strong></span>
                    <span>Subtotal estimado: <strong id="cntSubtotal">$ 0</strong></span>
                </div>
            </div>

            <!-- ── PASO 2: Datos del proveedor / orden ── -->
            <div class="mod-table-wrap" style="padding:24px;">
                <h3 style="color:var(--amber); margin-bottom:20px; font-size:15px;">
                    <i class="bi bi-2-circle-fill"></i> Datos de la Orden de Compra
                </h3>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:16px;">

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-building"></i> Proveedor (TO:) <span style="color:#e03c3c;">*</span></label>
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
                        <label class="oc-label"><i class="bi bi-calculator"></i> Retención 2.5% (%) — Se aplica sobre subtotal</label>
                        <input type="number" name="retencion" class="oc-input"
                               placeholder="Ej: 2.5" min="0" max="100" step="0.01" value="2.5">
                    </div>

                </div>

                <div style="margin-top:16px;">
                    <label class="oc-label"><i class="bi bi-chat-left-text"></i> Nota / Descripción (izquierda del PDF)</label>
                    <textarea name="nota" class="oc-input" rows="4"
                              placeholder="Ej:&#10;THANK YOU FOR YOUR BUSINESS !!&#10;NOTA:&#10;1. Compartir factura&#10;2. Compartir Guía de despacho"
                              maxlength="1000" style="width:100%; resize:vertical;">THANK YOU FOR YOUR BUSINESS !!

NOTA:
1. Compartir factura
2. Compartir Guía de despacho
3. Carta de garantía
4. Fichas técnicas</textarea>
                </div>

                <div style="margin-top:24px; display:flex; justify-content:flex-end; gap:12px;">
                    <a href="<?= $basePath ?>?module=cotizaciones&action=consultar"
                       class="btn-mod-primary" style="background:#6b7280; border:none;">
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

<style>
.oc-label {
    display:block;
    font-size:12px;
    font-weight:600;
    color:rgba(255,255,255,.75);
    margin-bottom:6px;
    letter-spacing:.5px;
}
.oc-input {
    width:100%;
    padding:10px 13px;
    background:rgba(255,255,255,.07);
    border:1.5px solid rgba(45,190,203,.2);
    border-radius:9px;
    color:var(--white);
    font-size:13px;
    font-family:var(--font-main);
    transition:border-color .2s;
}
.oc-input:focus {
    outline:none;
    border-color:var(--amber);
    background:rgba(45,190,203,.08);
}
.oc-filter-btn {
    padding:6px 16px;
    border-radius:20px;
    border:1.5px solid #10757e;
    background:#e8f8f8;
    color:#0a4f55;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:all .2s;
    display:inline-flex;
    align-items:center;
    gap:5px;
    white-space:nowrap;
    box-shadow:0 1px 3px rgba(0,0,0,.08);
}
.oc-filter-btn:hover {
    background:#c8eef0;
    border-color:#0a4f55;
    color:#0a4f55;
}
.oc-filter-btn.active {
    background:#10757e;
    border-color:#10757e;
    color:#ffffff;
    box-shadow:0 2px 6px rgba(16,117,126,.35);
}
.item-row.oculta { display:none; }
</style>

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
            const max = parseInt(this.max) || 9999;
            let val   = parseInt(this.value) || 1;
            if (val < 1)   { val = 1;   this.value = 1; }
            if (val > max) { val = max; this.value = max; }

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

    // Evitar que Enter en el campo código dispare el submit
    document.querySelectorAll('.oc-cod-input').forEach(inp => {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    });

    // ── Resumen de selección ──────────────────────────────────────────────
    function actualizarResumen() {
        let cnt = 0, sub = 0;
        checks.forEach(c => {
            if (c.checked) {
                cnt++;
                const row      = c.closest('tr');
                const celdaTot = row.querySelector('.celda-total');
                if (celdaTot) {
                    const txt = celdaTot.textContent.replace(/[^\d]/g, '');
                    sub += parseInt(txt) || 0;
                }
            }
        });
        cntItems.textContent = cnt;
        cntSub.textContent   = '$ ' + sub.toLocaleString('es-CO', {minimumFractionDigits:0});
        btnGen.disabled      = cnt === 0;
        checkAll.indeterminate = cnt > 0 && cnt < checks.length;
        checkAll.checked       = cnt === checks.length && checks.length > 0;
    }

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
