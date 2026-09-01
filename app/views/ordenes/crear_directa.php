<?php
/**
 * Vista: Nueva Orden de Compra Directa / Mostrador
 * Variables disponibles: $csrf_token, $fechaActual, $infoProveedorInicial
 */
$pageTitle = 'Nueva Orden Directa / Mostrador';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
include dirname(__DIR__) . '/layout/header.php';
include dirname(__DIR__) . '/layout/menu.php';
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <h1 class="mod-title"><i class="bi bi-shop"></i> Nueva Orden Directa / Mostrador</h1>
                <p class="mod-sub">Generación de orden de compra directa para stock o mostrador de la empresa</p>
            </div>
            <a href="<?= $basePath ?>?module=ordenes&action=consultar"
               class="btn-mod-primary btn-secondary-custom">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="mod-alert mod-alert-err">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <form method="POST" action="<?= $basePath ?>?module=ordenes&action=crear_directa_guardar" id="formOrdenDirecta">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <!-- ── BUSCADOR PREDICTIVO DEL CATÁLOGO DE PRODUCTOS ── -->
            <div class="mod-table-wrap p-24 mb-20 overflow-visible">
                <h3 class="section-title-orden" style="margin-bottom: 12px;">
                    <i class="bi bi-search"></i> Buscar Producto del Catálogo
                </h3>
                <div class="search-live" style="position: relative;">
                    <input type="text" id="busquedaProductoDirecta" placeholder="Escriba el nombre o código del producto para autocompletar..." class="mod-search-input cot-live-input" style="width: 100%;" autocomplete="off">
                    <div id="listaProductosDirecta" class="lista-sugerencias" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-height: 260px; overflow-y: auto; margin-top: 4px;"></div>
                </div>
            </div>

            <!-- ── PASO 1: Ítems / Productos a pedir ── -->
            <div class="mod-table-wrap section-card-orden mb-24">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <h3 class="section-title-orden" style="margin: 0;">
                        <i class="bi bi-1-circle-fill"></i> Productos a Pedir (Mostrador)
                    </h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <button type="button" id="btnLimpiarProductos" style="padding: 8px 14px; font-size: 13px; font-weight: 600; color: #64748b; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseenter="this.style.background='#f1f5f9'; this.style.color='#ef4444'; this.style.borderColor='#fca5a5';" onmouseleave="this.style.background='#ffffff'; this.style.color='#64748b'; this.style.borderColor='#cbd5e1';">
                            <i class="bi bi-arrow-counterclockwise"></i> Limpiar Productos
                        </button>
                        <button type="button" class="btn-mod-primary" id="btnAgregarFila" style="padding: 8px 14px; font-size: 13px;">
                            <i class="bi bi-plus-circle-fill"></i> + Agregar Producto Manual
                        </button>
                    </div>
                </div>

                <div class="tabla-responsive">
                    <table class="mod-table" id="tablaItemsDirecta">
                        <thead>
                            <tr>
                                <th style="width: 125px;">Cód. Proveedor</th>
                                <th>Producto / Descripción *</th>
                                <th style="width: 90px;" class="text-right">Cantidad *</th>
                                <th style="width: 140px;" class="text-right">Precio Prov. ($) *</th>
                                <th style="width: 130px;" class="text-center">IVA</th>
                                <th style="width: 140px;" class="text-right">Total ($)</th>
                                <th style="width: 45px;" class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItemsDirecta">
                            <!-- Filas dinámicas -->
                        </tbody>
                    </table>
                </div>

                <!-- Resumen de selección dinámico -->
                <div id="resumenSeleccion" class="resumen-seleccion-box">
                    <div class="header-actions-wrap align-center mb-10">
                        <span>Total Ítems: <strong id="cntItems">0</strong></span>
                        <span>Subtotal: <strong id="cntSubtotal">$ 0</strong></span>
                        <span class="text-danger">Descuento: <strong id="cntDesc">$ 0</strong></span>
                        <span>Subtotal con Descuento: <strong id="cntSubNeto">$ 0</strong></span>
                    </div>
                    <div class="header-actions-wrap align-center pt-10 border-top-teal">
                        <span>IVA: <strong id="cntIva">$ 0</strong></span>
                        <span class="text-warning-gold">Retención (<span id="lblRetPct">2.5</span>%): <strong id="cntRet" class="text-warning-gold">$ 0</strong></span>
                        <span class="text-info">Flete: <strong id="cntFlete">$ 0</strong></span>
                        <div class="text-total-wrap font-bold">TOTAL A PAGAR: <span id="cntTotal" class="resumen-total-text">$ 0</span></div>
                    </div>
                </div>
            </div>

            <!-- ── PASO 2: Datos del proveedor y orden ── -->
            <div class="mod-table-wrap section-card-orden">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <h3 class="section-title-orden" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-2-circle-fill"></i> Datos de la Orden y Proveedor
                        <span id="badgeEstadoProv" class="mod-badge badge-gold" style="font-size: 12px; padding: 3px 10px; font-weight: 600;">
                            <i class="bi bi-plus-circle"></i> Proveedor Nuevo
                        </span>
                    </h3>
                </div>

                <div class="grid-form-fields">

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-building"></i> Proveedor (TO:) <span class="required-star">*</span></label>
                        <input type="text" name="proveedor" id="inputProveedor" class="oc-input" required
                               placeholder="Escriba el nombre del proveedor..." maxlength="200" autocomplete="off">
                        <input type="hidden" name="estado_proveedor" id="inputEstadoProveedor" value="nuevo">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-hash"></i> NIT del Proveedor</label>
                        <input type="text" name="proveedor_nit" id="inputProveedorNit" class="oc-input"
                               placeholder="Ej: 79625307-6" maxlength="30">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-person-badge"></i> Tipo de Contribuyente</label>
                        <input type="text" name="tipo_contribuyente" id="inputTipoContribuyente" class="oc-input"
                               placeholder="Ej: PERSONA NATURAL / JURIDICA" maxlength="100">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-calendar-date"></i> Fecha</label>
                        <input type="date" name="fecha" class="oc-input"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-credit-card"></i> Condiciones de Pago</label>
                        <input type="text" name="condiciones_pago" id="inputCondicionesPago" class="oc-input"
                               placeholder="Ej: Según acuerdo" maxlength="100"
                               value="Según acuerdo">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-percent"></i> IVA General</label>
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
                        <label class="oc-label"><i class="bi bi-calculator"></i> Retención (%)</label>
                        <input type="number" name="retencion" class="oc-input" id="inputRetencion"
                               placeholder="Ej: 2.5" min="0" max="100" step="0.01" value="2.5">
                    </div>

                    <!-- DESCUENTO Y FLETE -->
                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-tag-fill"></i> Descuento</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select name="tipo_descuento" id="inputTipoDescuento" class="oc-input" style="width: 95px; min-width: 95px; flex-shrink: 0; padding: 10px 8px; font-weight: 600; text-align: center;">
                                <option value="monto" selected>$ COP</option>
                                <option value="porcentaje">% Porc.</option>
                            </select>
                            <input type="number" name="descuento_valor" id="inputDescuentoValor" class="oc-input"
                                    placeholder="0" min="0" step="0.01" value="0" style="flex: 1;">
                        </div>
                        <input type="hidden" name="descuento" id="inputDescuentoCalculado" value="0">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-truck"></i> Valor de Flete ($)</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="number" name="flete" id="inputFlete" class="oc-input"
                                   placeholder="0" min="0" step="0.01" value="0" style="flex: 1;">
                            <select name="flete_iva" id="inputFleteIva" class="oc-input" style="width: 105px; min-width: 105px; flex-shrink: 0; padding: 10px 8px; font-weight: 600; text-align: center;">
                                <option value="no" selected>Sin IVA</option>
                                <option value="si">+ 19% IVA</option>
                            </select>
                            <input type="hidden" name="flete_porcentaje_iva" id="inputFletePorcentajeIva" value="19">
                        </div>
                    </div>

                </div>

                <!-- Datos bancarios -->
                <div class="bank-details-wrap">
                    <h4 class="section-title-orden">
                        <i class="bi bi-bank"></i> Datos Bancarios (No visibles en PDF)
                    </h4>
                    <div class="grid-form-fields">
                        <div class="oc-field-group">
                            <label class="oc-label">Nombre del Banco</label>
                            <input type="text" name="banco_nombre" id="inputBancoNombre" class="oc-input"
                                   placeholder="Ej: Bancolombia" maxlength="100">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Número de Cuenta</label>
                            <input type="text" name="banco_cuenta" id="inputBancoCuenta" class="oc-input"
                                   placeholder="Ej: 123456789" maxlength="100">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Tipo de Cuenta</label>
                            <select name="banco_tipo_cuenta" id="inputBancoTipoCuenta" class="oc-input">
                                <option value="">Seleccione...</option>
                                <option value="Ahorros">Ahorros</option>
                                <option value="Corriente">Corriente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Nota / Observación -->
                <div class="bank-details-wrap">
                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-chat-left-text"></i> Nota / Descripción (izquierda del PDF)</label>
                        <textarea name="nota" class="oc-input" rows="3"
                                  placeholder="Observaciones de la orden de mostrador..." maxlength="1000"></textarea>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="imo-modal-footer oc-footer-btns" style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                    <a href="<?= $basePath ?>?module=ordenes&action=consultar"
                       class="imo-btn-cancel" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-mod-primary" id="btnGenerarOrden" style="padding: 12px 28px; font-size: 15px;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Orden de Mostrador
                    </button>
                </div>

            </div>

        </form>

    </main>
</div>

<script>
(function() {
    let filaIndex = 0;
    const tbody = document.getElementById('tbodyItemsDirecta');
    const btnAgregar = document.getElementById('btnAgregarFila');
    const form = document.getElementById('formOrdenDirecta');

    function agregarFila(codigo = '', titulo = '', desc = '', cantidad = 1, precio = 0, aplicaIva = 'si', pctIva = 19) {
        filaIndex++;
        const tr = document.createElement('tr');
        tr.id = 'fila-item-' + filaIndex;
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${filaIndex}][codigo_proveedor]" class="oc-input"
                       placeholder="Cód. Prov" value="${codigo}" maxlength="60" style="padding: 6px 8px; font-size: 12.5px;">
            </td>
            <td>
                <input type="text" name="items[${filaIndex}][titulo]" class="oc-input item-titulo" required
                       placeholder="Nombre del producto o insumo *" value="${titulo}" maxlength="255" style="padding: 6px 8px; font-size: 13px; font-weight: 600; margin-bottom: 4px;">
                <textarea name="items[${filaIndex}][descripcion]" class="oc-input" rows="1"
                          placeholder="Descripción adicional (opcional)" maxlength="1000" style="padding: 4px 8px; font-size: 11.5px; resize: vertical;">${desc}</textarea>
            </td>
            <td class="text-right">
                <input type="number" name="items[${filaIndex}][cantidad]" class="oc-input item-cantidad" required
                       min="1" value="${cantidad}" style="padding: 6px 8px; font-size: 13px; text-align: right;">
            </td>
            <td class="text-right">
                <input type="number" name="items[${filaIndex}][precio]" class="oc-input item-precio" required
                       min="0" step="0.01" value="${precio}" placeholder="0.00" style="padding: 6px 8px; font-size: 13px; text-align: right;">
            </td>
            <td class="text-center">
                <select name="items[${filaIndex}][iva]" class="oc-input item-iva-sel" style="padding: 6px 8px; font-size: 12px; text-align: center; width: 100%; min-width: 105px;">
                    <option value="si" ${aplicaIva === 'si' ? 'selected' : ''}>Sí (19%)</option>
                    <option value="no" ${aplicaIva === 'no' ? 'selected' : ''}>No (0%)</option>
                </select>
                <input type="hidden" name="items[${filaIndex}][porcentaje_iva]" value="${pctIva}">
            </td>
            <td class="text-right item-total-fila font-bold" style="font-size: 13.5px; vertical-align: middle;">
                $ 0
            </td>
            <td class="text-center" style="vertical-align: middle;">
                <button type="button" class="mod-btn-del" title="Eliminar producto" style="padding: 6px 8px;">
                    <i class="bi bi-trash3-fill"></i>
                </button>
            </td>
        `;

        // Eventos en inputs de la fila
        tr.querySelector('.mod-btn-del').addEventListener('click', function() {
            if (tbody.querySelectorAll('tr').length > 1) {
                tr.remove();
                calcularTotales();
            } else {
                alert('Debe haber al menos un producto en la orden.');
            }
        });

        tr.querySelectorAll('.item-cantidad, .item-precio, .item-iva-sel').forEach(inp => {
            inp.addEventListener('input', calcularTotales);
            inp.addEventListener('change', calcularTotales);
        });

        tbody.appendChild(tr);
        calcularTotales();
    }

    // ── Búsqueda predictiva del catálogo de productos ──
    const inpBusqueda = document.getElementById('busquedaProductoDirecta');
    const listaSug    = document.getElementById('listaProductosDirecta');
    let timerProd     = null;

    if (inpBusqueda) {
        inpBusqueda.addEventListener('input', function() {
            clearTimeout(timerProd);
            const q = this.value.trim();
            if (q.length < 2) {
                listaSug.style.display = 'none';
                listaSug.innerHTML = '';
                return;
            }

            timerProd = setTimeout(() => {
                fetch('<?= $basePath ?>?module=cotizaciones&action=ajax_buscar_productos&busqueda=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(res => {
                        const productos = res.data || [];
                        listaSug.innerHTML = '';
                        if (!productos || productos.length === 0) {
                            listaSug.innerHTML = '<div style="padding: 10px 14px; color: #64748b; font-size: 12.5px;">No se encontraron productos en el catálogo.</div>';
                        } else {
                            productos.forEach(p => {
                                const div = document.createElement('div');
                                div.className = 'sugerencia-item';
                                div.style.cssText = 'padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: background 0.15s;';
                                div.onmouseenter = () => div.style.background = '#f8fafc';
                                div.onmouseleave = () => div.style.background = '#fff';
                                
                                const fotoHtml = p.foto 
                                    ? `<img src="<?= $basePath ?>uploads/${p.foto}" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; flex-shrink: 0;">`
                                    : `<div style="width: 38px; height: 38px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;"><i class="bi bi-box-seam"></i></div>`;

                                div.innerHTML = `
                                    ${fotoHtml}
                                    <div style="flex: 1;">
                                        <div style="font-weight: 600; font-size: 13px; color: #1e293b;">${p.titulo}</div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                            ${p.categoria ? `<span class="mod-badge badge-blue" style="font-size: 10px; padding: 1px 6px;">${p.categoria}</span> ` : ''}
                                            ${p.codigo_proveedor ? `Cód: <strong>${p.codigo_proveedor}</strong> | ` : ''}
                                            ${p.proveedor ? `Prov: <strong>${p.proveedor}</strong>` : ''}
                                        </div>
                                    </div>
                                    <span class="mod-badge badge-green" style="font-size: 11px;">+ Agregar</span>
                                `;

                                div.addEventListener('click', () => {
                                    // Si la primera fila está vacía, reemplazarla
                                    const filasExistentes = tbody.querySelectorAll('tr');
                                    if (filasExistentes.length === 1) {
                                        const tit = filasExistentes[0].querySelector('.item-titulo').value.trim();
                                        if (!tit) {
                                            filasExistentes[0].remove();
                                        }
                                    }

                                    agregarFila(
                                        p.codigo_proveedor || p.codigo_producto || '',
                                        p.titulo || '',
                                        p.descripcion || '',
                                        1,
                                        parseFloat(p.precio_proveedor || p.precio || 0),
                                        (p.iva || 'si').toLowerCase(),
                                        parseFloat(p.porcentaje_iva || 19)
                                    );

                                    // Si el producto trae proveedor y el input de proveedor está vacío, autocompletarlo
                                    if (p.proveedor && !inputProv.value.trim()) {
                                        inputProv.value = p.proveedor;
                                        verificarProveedor(p.proveedor);
                                    }

                                    listaSug.style.display = 'none';
                                    inpBusqueda.value = '';
                                });

                                listaSug.appendChild(div);
                            });
                        }
                        listaSug.style.display = 'block';
                    })
                    .catch(() => {
                        listaSug.style.display = 'none';
                    });
            }, 250);
        });

        // Ocultar lista al hacer clic afuera
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-live')) {
                listaSug.style.display = 'none';
            }
        });
    }

    btnAgregar.addEventListener('click', () => agregarFila());

    const btnLimpiar = document.getElementById('btnLimpiarProductos');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (confirm('¿Desea limpiar toda la lista de productos agregados?')) {
                tbody.innerHTML = '';
                filaIndex = 0;
                agregarFila(); // Deja una fila en blanco inicial
                calcularTotales();
            }
        });
    }

    // Agregar primera fila por defecto
    agregarFila();

    function formatPesos(num) {
        return '$ ' + Math.round(num).toLocaleString('es-CO');
    }

    function calcularTotales() {
        let count = 0;
        let subtotal = 0;
        let totalIva = 0;

        const filas = tbody.querySelectorAll('tr');
        filas.forEach(tr => {
            const cant = parseFloat(tr.querySelector('.item-cantidad').value) || 0;
            const precio = parseFloat(tr.querySelector('.item-precio').value) || 0;
            const aplicaIva = tr.querySelector('.item-iva-sel').value === 'si';
            const pctIva = aplicaIva ? 0.19 : 0.00;

            const subFila = cant * precio;
            const ivaFila = subFila * pctIva;
            const totalFila = subFila + ivaFila;

            tr.querySelector('.item-total-fila').textContent = formatPesos(totalFila);

            if (cant > 0 && precio > 0) {
                count++;
            }
            subtotal += subFila;
            totalIva += ivaFila;
        });

        // Descuento
        const tipoDesc = document.getElementById('inputTipoDescuento').value;
        const valDescInput = parseFloat(document.getElementById('inputDescuentoValor').value) || 0;
        let descCalculado = 0;

        if (tipoDesc === 'porcentaje') {
            descCalculado = subtotal * (Math.min(100, Math.max(0, valDescInput)) / 100);
        } else {
            descCalculado = Math.min(subtotal, Math.max(0, valDescInput));
        }
        document.getElementById('inputDescuentoCalculado').value = descCalculado.toFixed(2);

        const subtotalConDesc = Math.max(0, subtotal - descCalculado);

        // Retención
        const retPct = parseFloat(document.getElementById('inputRetencion').value) || 0;
        document.getElementById('lblRetPct').textContent = retPct;
        const retencion = subtotalConDesc * (retPct / 100);

        // Flete e IVA de flete
        const flete = parseFloat(document.getElementById('inputFlete').value) || 0;
        const fleteIvaSel = document.getElementById('inputFleteIva');
        const fleteAplicaIva = fleteIvaSel ? fleteIvaSel.value === 'si' : false;
        const fleteIvaVal = fleteAplicaIva ? flete * 0.19 : 0;
        const totalIvaFinal = totalIva + fleteIvaVal;

        // TOTAL FINAL
        const totalPagar = subtotalConDesc + totalIvaFinal - retencion + flete;

        document.getElementById('cntItems').textContent = filas.length;
        document.getElementById('cntSubtotal').textContent = formatPesos(subtotal);
        document.getElementById('cntDesc').textContent = '- ' + formatPesos(descCalculado);
        document.getElementById('cntSubNeto').textContent = formatPesos(subtotalConDesc);
        document.getElementById('cntIva').textContent = '+ ' + formatPesos(totalIvaFinal) + (fleteAplicaIva && flete > 0 ? ' (inc. IVA flete)' : '');
        document.getElementById('cntRet').textContent = '- ' + formatPesos(retencion);
        document.getElementById('cntFlete').textContent = '+ ' + formatPesos(flete) + (fleteAplicaIva ? ' (con IVA)' : '');
        document.getElementById('cntTotal').textContent = formatPesos(totalPagar);
    }

    // Escuchadores de cambio en totales
    ['inputTipoDescuento', 'inputDescuentoValor', 'inputRetencion', 'inputFlete', 'inputFleteIva'].forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('input', calcularTotales);
            elem.addEventListener('change', calcularTotales);
        }
    });

    // ── Búsqueda y autocompletado de proveedor ──
    const inputProv = document.getElementById('inputProveedor');
    const badgeProv = document.getElementById('badgeEstadoProv');
    const hdnEstado = document.getElementById('inputEstadoProveedor');
    let timerBusqueda = null;
    let ultimoProvAutocompletado = '';

    function limpiarCamposProveedor() {
        const nitInp   = document.getElementById('inputProveedorNit');
        const tcontInp = document.getElementById('inputTipoContribuyente');
        const condInp  = document.getElementById('inputCondicionesPago');
        const bnomInp  = document.getElementById('inputBancoNombre');
        const bctaInp  = document.getElementById('inputBancoCuenta');
        const btipoInp = document.getElementById('inputBancoTipoCuenta');

        if (nitInp) nitInp.value = '';
        if (tcontInp) tcontInp.value = '';
        if (condInp) condInp.value = 'Según acuerdo';
        if (bnomInp) bnomInp.value = '';
        if (bctaInp) bctaInp.value = '';
        if (btipoInp) btipoInp.value = '';
        ultimoProvAutocompletado = '';
    }

    function verificarProveedor(nombre) {
        if (!nombre || nombre.trim().length < 2) {
            if (badgeProv) {
                badgeProv.className = 'mod-badge badge-gold';
                badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
            }
            if (hdnEstado) hdnEstado.value = 'nuevo';
            limpiarCamposProveedor();
            return;
        }

        if (badgeProv) {
            badgeProv.className = 'mod-badge badge-gold';
            badgeProv.innerHTML = '<i class="bi bi-arrow-repeat"></i> Verificando...';
        }

        fetch('<?= $basePath ?>?module=ordenes&action=ajax_consultar_proveedor&term=' + encodeURIComponent(nombre.trim()))
            .then(res => res.json())
            .then(res => {
                if (res.registrado) {
                    if (badgeProv) {
                        badgeProv.className = 'mod-badge badge-green';
                        badgeProv.innerHTML = '<i class="bi bi-check-circle-fill"></i> Proveedor Registrado (' + res.ordenes + ' orden' + (res.ordenes > 1 ? 'es' : '') + ')';
                    }
                    if (hdnEstado) hdnEstado.value = 'registrado';

                    const d = res.datos;
                    if (d) {
                        const nitInp   = document.getElementById('inputProveedorNit');
                        const tcontInp = document.getElementById('inputTipoContribuyente');
                        const condInp  = document.getElementById('inputCondicionesPago');
                        const bnomInp  = document.getElementById('inputBancoNombre');
                        const bctaInp  = document.getElementById('inputBancoCuenta');
                        const btipoInp = document.getElementById('inputBancoTipoCuenta');

                        if (nitInp) nitInp.value = d.proveedor_nit || '';
                        if (tcontInp) tcontInp.value = d.tipo_contribuyente || '';
                        if (condInp) condInp.value = d.condiciones_pago || 'Según acuerdo';
                        if (bnomInp) bnomInp.value = d.banco_nombre || '';
                        if (bctaInp) bctaInp.value = d.banco_cuenta || '';
                        if (btipoInp) btipoInp.value = d.banco_tipo_cuenta || '';
                        ultimoProvAutocompletado = nombre.trim();
                    }
                } else {
                    if (badgeProv) {
                        badgeProv.className = 'mod-badge badge-gold';
                        badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
                    }
                    if (hdnEstado) hdnEstado.value = 'nuevo';
                    if (ultimoProvAutocompletado && ultimoProvAutocompletado !== nombre.trim()) {
                        limpiarCamposProveedor();
                    }
                }
            })
            .catch(() => {
                if (badgeProv) {
                    badgeProv.className = 'mod-badge badge-gold';
                    badgeProv.innerHTML = '<i class="bi bi-info-circle"></i> Proveedor Nuevo';
                }
                if (hdnEstado) hdnEstado.value = 'nuevo';
            });
    }

    if (inputProv) {
        inputProv.addEventListener('input', function() {
            clearTimeout(timerBusqueda);
            const val = this.value;
            if (!val.trim()) {
                limpiarCamposProveedor();
                if (badgeProv) {
                    badgeProv.className = 'mod-badge badge-gold';
                    badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
                }
                if (hdnEstado) hdnEstado.value = 'nuevo';
            } else {
                timerBusqueda = setTimeout(() => verificarProveedor(val), 300);
            }
        });
    }

    form.addEventListener('submit', function(e) {
        const filas = tbody.querySelectorAll('tr');
        if (filas.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto a la orden.');
        }
    });

})();
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
