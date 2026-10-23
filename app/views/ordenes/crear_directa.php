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

<?php
$esAjuste = !empty($_SESSION['orden_ajustando_id']) && !empty($ordenAjustando);
$poAjuste = $esAjuste ? (int)$ordenAjustando['numero_po'] : 0;
$datosProvIni = $infoProveedorInicial['datos'] ?? [];
$esRegistradoIni = !empty($infoProveedorInicial['registrado']);
$numOrdenesIni = (int)($infoProveedorInicial['ordenes'] ?? 0);
?>

<div class="layout-main">
    <?php include dirname(__DIR__) . '/layout/topbar.php'; ?>

    <main class="contenido-principal">

        <div class="mod-header">
            <div>
                <?php if ($esAjuste): ?>
                <h1 class="mod-title"><i class="bi bi-wrench-adjustable-circle-fill"></i> Ajustando Orden de Mostrador: P.O. <?= $poAjuste ?></h1>
                <p class="mod-sub text-warning-gold"><i class="bi bi-info-circle-fill"></i> Corrección directa. Se conservará el <strong>mismo número de P.O. (<?= $poAjuste ?>)</strong> sin generar un nuevo registro.</p>
                <?php else: ?>
                <h1 class="mod-title"><i class="bi bi-shop"></i> Nueva Orden Directa / Mostrador</h1>
                <p class="mod-sub">Generación de orden de compra directa para stock o mostrador de la empresa</p>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <?php if ($esAjuste): ?>
                <a href="<?= $basePath ?>?module=ordenes&action=cancelar_ajuste"
                   class="btn-discard-draft" onclick="return confirm('¿Desea cancelar el ajuste de esta orden de compra? No se guardará ningún cambio.');">
                    <i class="bi bi-x-circle-fill"></i> Cancelar Ajuste
                </a>
                <?php else: ?>
                <a href="<?= $basePath ?>?module=ordenes&action=consultar"
                   class="btn-mod-primary btn-secondary-custom">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($_SESSION['flash_error'])):
            $alertaErrorMsg = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
            include __DIR__ . '/../layout/alerta_error.php';
        endif; ?>

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
                    </div>
                </div>

                <div class="tabla-responsive">
                    <table class="mod-table" id="tablaItemsDirecta">
                        <thead>
                            <tr>
                                <th style="width: 125px;">Cód. Producto</th>
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
                        <span id="wrapCntSaldoFavor" class="text-teal" style="display:none;">Saldo a Favor: <strong id="cntSaldoFavor" class="text-teal">- $ 0</strong></span>
                        <div class="text-total-wrap font-bold">TOTAL A PAGAR: <span id="cntTotal" class="resumen-total-text">$ 0</span></div>
                    </div>
                </div>
            </div>

            <!-- ── PASO 2: Datos del proveedor y orden ── -->
            <div class="mod-table-wrap section-card-orden">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                    <h3 class="section-title-orden" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-2-circle-fill"></i> Datos de la Orden y Proveedor
                        <span id="badgeEstadoProv" class="mod-badge <?= $esRegistradoIni ? 'badge-green' : 'badge-gold' ?>" style="font-size: 12px; padding: 3px 10px; font-weight: 600;">
                            <i class="bi <?= $esRegistradoIni ? 'bi-check-circle-fill' : 'bi-plus-circle' ?>"></i> <?= $esRegistradoIni ? 'Proveedor Registrado' . ($numOrdenesIni > 0 ? " ($numOrdenesIni orden" . ($numOrdenesIni > 1 ? 'es' : '') . ')' : '') : 'Proveedor Nuevo' ?>
                        </span>
                    </h3>
                </div>

                <div class="grid-form-fields">

                    <div class="oc-field-group search-live" style="position: relative;">
                        <label class="oc-label"><i class="bi bi-building"></i> Proveedor (TO:) <span class="required-star">*</span></label>
                        <input type="text" name="proveedor" id="inputProveedor" class="oc-input" required
                               placeholder="Escriba el nombre o NIT del proveedor..." maxlength="200" autocomplete="off"
                               value="<?= htmlspecialchars($ordenAjustando['proveedor'] ?? ($datosProvIni['proveedor'] ?? '')) ?>">
                        <input type="hidden" name="estado_proveedor" id="inputEstadoProveedor" value="<?= htmlspecialchars($esRegistradoIni ? 'registrado' : ($ordenAjustando['estado_proveedor'] ?? 'nuevo')) ?>">
                        <div id="listaProveedoresDirecta" class="lista-sugerencias" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; background:#ffffff; border:1.5px solid #0d9488; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); max-height:220px; overflow-y:auto;"></div>
                    </div>

                    <div class="oc-field-group search-live" style="position: relative;">
                        <label class="oc-label"><i class="bi bi-hash"></i> NIT del Proveedor</label>
                        <input type="text" name="proveedor_nit" id="inputProveedorNit" class="oc-input"
                               placeholder="Ej: 79625307-6" maxlength="30" autocomplete="off"
                               value="<?= htmlspecialchars($ordenAjustando['proveedor_nit'] ?? '') ?>">
                        <div id="listaProveedoresNit" class="lista-sugerencias" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; background:#ffffff; border:1.5px solid #0d9488; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); max-height:220px; overflow-y:auto;"></div>
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-person-badge"></i> Tipo de Contribuyente</label>
                        <input type="text" name="tipo_contribuyente" id="inputTipoContribuyente" class="oc-input"
                               placeholder="Ej: PERSONA NATURAL / JURIDICA" maxlength="100"
                               value="<?= htmlspecialchars(!empty($ordenAjustando['tipo_contribuyente']) ? $ordenAjustando['tipo_contribuyente'] : ($datosProvIni['tipo_contribuyente'] ?? '')) ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-calendar-date"></i> Fecha</label>
                        <input type="date" name="fecha" class="oc-input"
                               value="<?= htmlspecialchars($ordenAjustando['fecha'] ?? (new DateTime('now', new DateTimeZone('America/Bogota')))->format('Y-m-d')) ?>" required>
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-credit-card"></i> Condiciones de Pago</label>
                        <input type="text" name="condiciones_pago" id="inputCondicionesPago" class="oc-input"
                               placeholder="Ej: Según acuerdo" maxlength="100"
                               value="<?= htmlspecialchars($ordenAjustando['condiciones_pago'] ?? 'Según acuerdo') ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-percent"></i> IVA General</label>
                        <input type="text" name="iva" class="oc-input"
                               placeholder="Ej: 19%" maxlength="20"
                               value="<?= htmlspecialchars($ordenAjustando['iva'] ?? '19%') ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-person-lines-fill"></i> Departamento de Compras (Responsable)</label>
                        <input type="text" name="departamento_compras" class="oc-input"
                               placeholder="Nombre del responsable" maxlength="100"
                               value="<?= htmlspecialchars($ordenAjustando['departamento_compras'] ?? ($_SESSION['usuario_nombre'] ?? '')) ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-calculator"></i> Retención (%)</label>
                        <input type="number" name="retencion" class="oc-input" id="inputRetencion"
                               placeholder="Ej: 2.5" min="0" max="100" step="0.01"
                               value="<?= isset($ordenAjustando['retencion']) ? (float)$ordenAjustando['retencion'] : 2.5 ?>">
                    </div>

                    <!-- DESCUENTO Y FLETE -->
                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-tag-fill"></i> Descuento</label>
                        <?php $tdesc = $ordenAjustando['tipo_descuento'] ?? 'monto'; ?>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select name="tipo_descuento" id="inputTipoDescuento" class="oc-input" style="width: 95px; min-width: 95px; flex-shrink: 0; padding: 10px 8px; font-weight: 600; text-align: center;">
                                <option value="monto" <?= $tdesc === 'monto' ? 'selected' : '' ?>>$ COP</option>
                                <option value="porcentaje" <?= $tdesc === 'porcentaje' ? 'selected' : '' ?>>% Porc.</option>
                            </select>
                            <input type="number" name="descuento_valor" id="inputDescuentoValor" class="oc-input"
                                    placeholder="0" min="0" step="0.01"
                                    value="<?= isset($ordenAjustando['descuento_valor']) ? (float)$ordenAjustando['descuento_valor'] : 0 ?>" style="flex: 1;">
                        </div>
                        <input type="hidden" name="descuento" id="inputDescuentoCalculado" value="<?= isset($ordenAjustando['descuento']) ? (float)$ordenAjustando['descuento'] : 0 ?>">
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-truck"></i> Valor de Flete ($)</label>
                        <?php $fIva = $ordenAjustando['flete_iva'] ?? 'no'; ?>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="number" name="flete" id="inputFlete" class="oc-input"
                                   placeholder="0" min="0" step="0.01"
                                   value="<?= isset($ordenAjustando['flete']) ? (float)$ordenAjustando['flete'] : 0 ?>" style="flex: 1;">
                            <select name="flete_iva" id="inputFleteIva" class="oc-input" style="width: 105px; min-width: 105px; flex-shrink: 0; padding: 10px 8px; font-weight: 600; text-align: center;">
                                <option value="no" <?= $fIva === 'no' ? 'selected' : '' ?>>Sin IVA</option>
                                <option value="si" <?= $fIva === 'si' ? 'selected' : '' ?>>+ 19% IVA</option>
                            </select>
                            <input type="hidden" name="flete_porcentaje_iva" id="inputFletePorcentajeIva" value="<?= isset($ordenAjustando['flete_porcentaje_iva']) ? (float)$ordenAjustando['flete_porcentaje_iva'] : 19 ?>">
                        </div>
                    </div>

                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-wallet2"></i> Saldo a Favor ($)</label>
                        <input type="number" name="saldo_favor" id="inputSaldoFavor" class="oc-input"
                               placeholder="0" min="0" step="0.01"
                               value="<?= isset($ordenAjustando['saldo_favor']) ? (float)$ordenAjustando['saldo_favor'] : 0 ?>">
                    </div>

                </div>

                <!-- Datos bancarios y de contacto -->
                <div class="bank-details-wrap">
                    <h4 class="section-title-orden">
                        <i class="bi bi-bank"></i> Datos Bancarios (No visibles en PDF)
                    </h4>
                    <div class="grid-form-fields">
                        <div class="oc-field-group">
                            <label class="oc-label">Nombre del Banco</label>
                            <input type="text" name="banco_nombre" id="inputBancoNombre" class="oc-input"
                                   placeholder="Ej: Bancolombia" maxlength="100"
                                   value="<?= htmlspecialchars(!empty($ordenAjustando['banco_nombre']) ? $ordenAjustando['banco_nombre'] : ($datosProvIni['banco_nombre'] ?? '')) ?>">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Número de Cuenta</label>
                            <input type="text" name="banco_cuenta" id="inputBancoCuenta" class="oc-input"
                                   placeholder="Ej: 123456789" maxlength="100"
                                   value="<?= htmlspecialchars(!empty($ordenAjustando['banco_cuenta']) ? $ordenAjustando['banco_cuenta'] : ($datosProvIni['banco_cuenta'] ?? '')) ?>">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label">Tipo de Cuenta</label>
                            <?php $bTipo = !empty($ordenAjustando['banco_tipo_cuenta']) ? $ordenAjustando['banco_tipo_cuenta'] : ($datosProvIni['banco_tipo_cuenta'] ?? ''); ?>
                            <select name="banco_tipo_cuenta" id="inputBancoTipoCuenta" class="oc-input">
                                <option value="">Seleccione...</option>
                                <option value="Ahorros" <?= $bTipo === 'Ahorros' ? 'selected' : '' ?>>Ahorros</option>
                                <option value="Corriente" <?= $bTipo === 'Corriente' ? 'selected' : '' ?>>Corriente</option>
                            </select>
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label"><i class="bi bi-telephone-fill"></i> Celular / Teléfono</label>
                            <input type="text" name="proveedor_celular" id="inputProveedorCelular" class="oc-input"
                                   placeholder="Ej: 3001234567" maxlength="30"
                                   value="<?= htmlspecialchars(!empty($ordenAjustando['proveedor_celular']) ? $ordenAjustando['proveedor_celular'] : ($datosProvIni['celular'] ?? '')) ?>">
                        </div>
                        <div class="oc-field-group">
                            <label class="oc-label"><i class="bi bi-envelope-fill"></i> Correo Electrónico</label>
                            <input type="email" name="proveedor_correo" id="inputProveedorCorreo" class="oc-input"
                                   placeholder="Ej: ventas@proveedor.com" maxlength="100"
                                   value="<?= htmlspecialchars(!empty($ordenAjustando['proveedor_correo']) ? $ordenAjustando['proveedor_correo'] : ($datosProvIni['correo'] ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Nota / Observación -->
                <div class="bank-details-wrap">
                    <div class="oc-field-group">
                        <label class="oc-label"><i class="bi bi-chat-left-text"></i> Nota / Descripción (izquierda del PDF)</label>
                        <textarea name="nota" class="oc-input" rows="3"
                                  placeholder="Observaciones de la orden de mostrador..." maxlength="1000"><?= htmlspecialchars($ordenAjustando['nota'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="imo-modal-footer oc-footer-btns" style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                    <?php if ($esAjuste): ?>
                    <a href="<?= $basePath ?>?module=ordenes&action=cancelar_ajuste"
                       class="imo-btn-cancel" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-x-circle"></i> Cancelar Ajuste
                    </a>
                    <button type="submit" class="btn-mod-primary" id="btnGenerarOrden" style="padding: 12px 28px; font-size: 15px;">
                        <i class="bi bi-check-circle-fill"></i> Guardar Ajuste P.O. <?= $poAjuste ?>
                    </button>
                    <?php else: ?>
                    <a href="<?= $basePath ?>?module=ordenes&action=consultar"
                       class="imo-btn-cancel" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-mod-primary" id="btnGenerarOrden" style="padding: 12px 28px; font-size: 15px;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Orden de Mostrador
                    </button>
                    <?php endif; ?>
                </div>

            </div>

        </form>

    </main>
</div>

<script>
(function() {
    let filaIndex = 0;
    const tbody = document.getElementById('tbodyItemsDirecta');
    const form = document.getElementById('formOrdenDirecta');
    const inputProv = document.getElementById('inputProveedor');
    const badgeProv = document.getElementById('badgeEstadoProv');
    const hdnEstado = document.getElementById('inputEstadoProveedor');

    function sanitizarNumeroPositivo(input, minVal = 0, esEntero = false) {
        if (!input) return;
        input.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
        input.addEventListener('input', function() {
            let val = esEntero ? parseInt(this.value, 10) : parseFloat(this.value);
            if (!isNaN(val) && val < minVal) {
                this.value = minVal;
            }
        });
    }

    function verificarFilaVacia() {
        const filas = tbody.querySelectorAll('tr:not(#filaEmptyState)');
        const emptyRow = document.getElementById('filaEmptyState');
        if (filas.length === 0) {
            if (!emptyRow) {
                const trEmpty = document.createElement('tr');
                trEmpty.id = 'filaEmptyState';
                trEmpty.innerHTML = `
                    <td colspan="7" class="text-center" style="padding: 32px 16px; color: #64748b; background: #f8fafc;">
                        <i class="bi bi-search" style="font-size: 24px; color: #0d9488; display: block; margin-bottom: 8px;"></i>
                        <span style="font-size: 13.5px; font-weight: 600;">Aún no se han agregado productos a la orden.</span>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">Use el buscador de arriba para seleccionar productos del catálogo oficial e insertarlos automáticamente.</p>
                    </td>
                `;
                tbody.appendChild(trEmpty);
            }
        } else {
            if (emptyRow) {
                emptyRow.remove();
            }
        }
    }

    function agregarFila(codigo = '', titulo = '', desc = '', cantidad = 1, precio = 0, aplicaIva = 'si', pctIva = 19) {
        const emptyRow = document.getElementById('filaEmptyState');
        if (emptyRow) {
            emptyRow.remove();
        }

        filaIndex++;
        const tr = document.createElement('tr');
        tr.id = 'fila-item-' + filaIndex;
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${filaIndex}][codigo_proveedor]" class="oc-input"
                       placeholder="Cód. Prod" value="${codigo}" maxlength="60" style="padding: 6px 8px; font-size: 12.5px;">
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
            tr.remove();
            verificarFilaVacia();
            calcularTotales();
        });

        tr.querySelectorAll('.item-cantidad, .item-precio, .item-iva-sel').forEach(inp => {
            inp.addEventListener('input', calcularTotales);
            inp.addEventListener('change', calcularTotales);
        });

        sanitizarNumeroPositivo(tr.querySelector('.item-cantidad'), 1, true);
        sanitizarNumeroPositivo(tr.querySelector('.item-precio'), 0, false);

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
                                    if (p.proveedor && inputProv && !inputProv.value.trim()) {
                                        inputProv.value = p.proveedor;
                                        if (typeof verificarProveedor === 'function') {
                                            verificarProveedor(p.proveedor);
                                        }
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

    const btnLimpiar = document.getElementById('btnLimpiarProductos');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (confirm('¿Desea limpiar toda la lista de productos agregados?')) {
                tbody.innerHTML = '';
                filaIndex = 0;
                verificarFilaVacia();
                calcularTotales();
            }
        });
    }

    // Precargar ítems si está en modo ajuste, o dejar lista con empty state
    const itemsExistentes = <?= json_encode($itemsAjustando ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    if (itemsExistentes && itemsExistentes.length > 0) {
        itemsExistentes.forEach(it => {
            agregarFila(
                it.codigo_proveedor || '',
                it.titulo || '',
                it.descripcion || '',
                parseInt(it.cantidad) || 1,
                parseFloat(it.precio_unit) || 0,
                (it.iva || 'si').toLowerCase(),
                parseFloat(it.porcentaje_iva || 19)
            );
        });
    } else {
        verificarFilaVacia();
    }

    function formatPesos(num) {
        return '$ ' + Math.round(num).toLocaleString('es-CO');
    }

    function calcularTotales() {
        let count = 0;
        let subtotal = 0;
        let totalIva = 0;

        const filas = tbody.querySelectorAll('tr:not(#filaEmptyState)');
        filas.forEach(tr => {
            const cantInput = tr.querySelector('.item-cantidad');
            if (!cantInput) return;
            const cant = parseFloat(cantInput.value) || 0;
            const precio = parseFloat(tr.querySelector('.item-precio').value) || 0;
            const aplicaIva = tr.querySelector('.item-iva-sel').value === 'si';
            const pctIva = aplicaIva ? 0.19 : 0.00;

            const subFila = cant * precio;
            const ivaFila = subFila * pctIva;
            const totalFila = subFila + ivaFila;

            const totalFilaEl = tr.querySelector('.item-total-fila');
            if (totalFilaEl) {
                totalFilaEl.textContent = formatPesos(totalFila);
            }

            if (cant > 0 && precio > 0) {
                count++;
            }
            subtotal += subFila;
            totalIva += ivaFila;
        });

        // Descuento
        const tipoDesc = document.getElementById('inputTipoDescuento')?.value || 'cop';
        const valDescInput = parseFloat(document.getElementById('inputDescuentoValor')?.value) || 0;
        let descCalculado = 0;

        if (tipoDesc === 'porcentaje') {
            descCalculado = subtotal * (Math.min(100, Math.max(0, valDescInput)) / 100);
        } else {
            descCalculado = Math.min(subtotal, Math.max(0, valDescInput));
        }
        const descCalcEl = document.getElementById('inputDescuentoCalculado');
        if (descCalcEl) descCalcEl.value = descCalculado.toFixed(2);

        const subtotalConDesc = Math.max(0, subtotal - descCalculado);

        // Retención
        const retPct = parseFloat(document.getElementById('inputRetencion')?.value) || 0;
        const lblRet = document.getElementById('lblRetPct');
        if (lblRet) lblRet.textContent = retPct;
        const retencion = subtotalConDesc * (retPct / 100);

        // Flete e IVA de flete
        const flete = parseFloat(document.getElementById('inputFlete')?.value) || 0;
        const fleteIvaSel = document.getElementById('inputFleteIva');
        const fleteAplicaIva = fleteIvaSel ? fleteIvaSel.value === 'si' : false;
        const fleteIvaVal = fleteAplicaIva ? flete * 0.19 : 0;

        // Saldo a favor (descontar de total a pagar)
        const saldoFavor = parseFloat(document.getElementById('inputSaldoFavor')?.value) || 0;

        // IVA TOTAL (ítems + IVA de flete)
        const totalIvaFinal = totalIva + fleteIvaVal;

        // TOTAL FINAL
        const totalPagar = Math.max(0, subtotalConDesc + totalIvaFinal - retencion + flete - saldoFavor);

        const cntItemsEl = document.getElementById('cntItems');
        if (cntItemsEl) cntItemsEl.textContent = filas.length;

        const cntSubtotalEl = document.getElementById('cntSubtotal');
        if (cntSubtotalEl) cntSubtotalEl.textContent = formatPesos(subtotal);

        const cntDescEl = document.getElementById('cntDesc');
        if (cntDescEl) cntDescEl.textContent = '- ' + formatPesos(descCalculado);

        const cntSubNetoEl = document.getElementById('cntSubNeto');
        if (cntSubNetoEl) cntSubNetoEl.textContent = formatPesos(subtotalConDesc);

        const cntIvaEl = document.getElementById('cntIva');
        if (cntIvaEl) cntIvaEl.textContent = '+ ' + formatPesos(totalIvaFinal) + (fleteAplicaIva && flete > 0 ? ' (inc. IVA flete)' : '');

        const cntRetEl = document.getElementById('cntRet');
        if (cntRetEl) cntRetEl.textContent = '- ' + formatPesos(retencion);

        const cntFleteEl = document.getElementById('cntFlete');
        if (cntFleteEl) cntFleteEl.textContent = '+ ' + formatPesos(flete) + (fleteAplicaIva ? ' (con IVA)' : '');

        const wrapSaldo = document.getElementById('wrapCntSaldoFavor');
        const cntSaldo = document.getElementById('cntSaldoFavor');
        if (wrapSaldo && cntSaldo) {
            if (saldoFavor > 0) {
                wrapSaldo.style.display = 'inline';
                cntSaldo.textContent = '- ' + formatPesos(saldoFavor);
            } else {
                wrapSaldo.style.display = 'none';
            }
        }

        const cntTotalEl = document.getElementById('cntTotal');
        if (cntTotalEl) cntTotalEl.textContent = formatPesos(totalPagar);
    }

    // Escuchadores de cambio en totales y sanitización contra negativos
    ['inputTipoDescuento', 'inputDescuentoValor', 'inputRetencion', 'inputFlete', 'inputFleteIva', 'inputSaldoFavor'].forEach(id => {
        const elem = document.getElementById(id);
        if (elem) {
            elem.addEventListener('input', calcularTotales);
            elem.addEventListener('change', calcularTotales);
        }
    });

    ['inputDescuentoValor', 'inputFlete', 'inputSaldoFavor'].forEach(id => {
        sanitizarNumeroPositivo(document.getElementById(id), 0, false);
    });
    sanitizarNumeroPositivo(document.getElementById('inputRetencion'), 0, false);

    // ── Búsqueda y autocompletado de proveedor ──
    let timerBusqueda = null;
    let ultimoProvAutocompletado = '';

    function limpiarCamposProveedor(limpiarNit = true, limpiarNombre = false) {
        const provInp  = document.getElementById('inputProveedor');
        const nitInp   = document.getElementById('inputProveedorNit');
        const tcontInp = document.getElementById('inputTipoContribuyente');
        const condInp  = document.getElementById('inputCondicionesPago');
        const bnomInp  = document.getElementById('inputBancoNombre');
        const bctaInp  = document.getElementById('inputBancoCuenta');
        const btipoInp = document.getElementById('inputBancoTipoCuenta');
        const celInp   = document.getElementById('inputProveedorCelular');
        const corInp   = document.getElementById('inputProveedorCorreo');

        if (limpiarNombre && provInp) provInp.value = '';
        if (limpiarNit && nitInp) nitInp.value = '';
        if (tcontInp) tcontInp.value = '';
        if (condInp) condInp.value = 'Según acuerdo';
        if (bnomInp) bnomInp.value = '';
        if (bctaInp) bctaInp.value = '';
        if (btipoInp) btipoInp.value = '';
        if (celInp) celInp.value = '';
        if (corInp) corInp.value = '';
        ultimoProvAutocompletado = '';
    }

    function verificarProveedor(nombre) {
        if (!nombre || nombre.trim().length < 2) {
            if (badgeProv) {
                badgeProv.className = 'mod-badge badge-gold';
                badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
            }
            if (hdnEstado) hdnEstado.value = 'nuevo';
            return;
        }

        if (badgeProv) {
            badgeProv.className = 'mod-badge badge-gold';
            badgeProv.innerHTML = '<i class="bi bi-arrow-repeat"></i> Verificando...';
        }

        fetch('<?= $basePath ?>?module=ordenes&action=ajax_consultar_proveedor&term=' + encodeURIComponent(nombre.trim()))
            .then(res => res.json())
            .then(res => {
                const d = res.datos;
                if (res.registrado) {
                    if (badgeProv) {
                        badgeProv.className = 'mod-badge badge-green';
                        badgeProv.innerHTML = '<i class="bi bi-check-circle-fill"></i> Proveedor Registrado (' + res.ordenes + ' orden' + (res.ordenes > 1 ? 'es' : '') + ')';
                    }
                    if (hdnEstado) hdnEstado.value = 'registrado';
                } else {
                    if (badgeProv) {
                        badgeProv.className = 'mod-badge badge-gold';
                        badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
                    }
                    if (hdnEstado) hdnEstado.value = 'nuevo';
                }

                if (d) {
                    const nitInp   = document.getElementById('inputProveedorNit');
                    const tcontInp = document.getElementById('inputTipoContribuyente');
                    const condInp  = document.getElementById('inputCondicionesPago');
                    const bnomInp  = document.getElementById('inputBancoNombre');
                    const bctaInp  = document.getElementById('inputBancoCuenta');
                    const btipoInp = document.getElementById('inputBancoTipoCuenta');
                    const celInp   = document.getElementById('inputProveedorCelular');
                    const corInp   = document.getElementById('inputProveedorCorreo');

                    if (nitInp && !nitInp.value.trim() && d.proveedor_nit) nitInp.value = d.proveedor_nit;
                    if (tcontInp && (!tcontInp.value.trim() || tcontInp.value === 'PERSONA JURÍDICA')) tcontInp.value = d.tipo_contribuyente || tcontInp.value;
                    if (condInp && !condInp.value.trim())  condInp.value = d.condiciones_pago || 'Según acuerdo';
                    if (bnomInp && !bnomInp.value.trim())  bnomInp.value = d.banco_nombre || '';
                    if (bctaInp && !bctaInp.value.trim())  bctaInp.value = d.banco_cuenta || '';
                    if (btipoInp && !btipoInp.value.trim()) btipoInp.value = d.banco_tipo_cuenta || '';
                    if (celInp && !celInp.value.trim())    celInp.value = d.celular || '';
                    if (corInp && !corInp.value.trim())    corInp.value = d.correo || '';
                    ultimoProvAutocompletado = (d.proveedor || nombre).trim();
                } else if (!res.registrado) {
                    // Si no está registrado ni existe en proveedores, vaciar campos asociados
                    limpiarCamposProveedor(false, false);
                }
            })
            .catch(() => {
                if (badgeProv) {
                    badgeProv.className = 'mod-badge badge-gold';
                    badgeProv.innerHTML = '<i class="bi bi-info-circle"></i> Proveedor Nuevo';
                }
                if (hdnEstado) hdnEstado.value = 'nuevo';
                limpiarCamposProveedor(false, false);
            });
    }

    const listaProvDirecta = document.getElementById('listaProveedoresDirecta');
    const listaProvNit     = document.getElementById('listaProveedoresNit');

    function ocultarListasSugerencias() {
        if (listaProvDirecta) {
            listaProvDirecta.style.display = 'none';
            listaProvDirecta.innerHTML = '';
        }
        if (listaProvNit) {
            listaProvNit.style.display = 'none';
            listaProvNit.innerHTML = '';
        }
    }

    function seleccionarProveedorSugerido(p) {
        if (!p) return;
        inputProv.value = p.nombre_proveedor || '';
        
        const nitInp   = document.getElementById('inputProveedorNit');
        const tcontInp = document.getElementById('inputTipoContribuyente');
        const bnomInp  = document.getElementById('inputBancoNombre');
        const bctaInp  = document.getElementById('inputBancoCuenta');
        const btipoInp = document.getElementById('inputBancoTipoCuenta');
        const celInp   = document.getElementById('inputProveedorCelular');
        const corInp   = document.getElementById('inputProveedorCorreo');

        if (nitInp) nitInp.value = p.nit || '';
        if (tcontInp) tcontInp.value = p.tipo_contribuyente || 'PERSONA JURÍDICA';
        if (bnomInp) bnomInp.value = p.nombre_banco || '';
        if (bctaInp) bctaInp.value = p.numero_cuenta || '';
        if (btipoInp) btipoInp.value = p.tipo_cuenta || '';
        if (celInp) celInp.value = p.celular || '';
        if (corInp) corInp.value = p.correo || '';

        ultimoProvAutocompletado = (p.nombre_proveedor || '').trim();

        ocultarListasSugerencias();
        verificarProveedor(p.nit || p.nombre_proveedor);
    }

    function renderizarSugerencias(contenedor, proveedores) {
        contenedor.innerHTML = '';
        if (!proveedores || proveedores.length === 0) {
            contenedor.style.display = 'none';
            return;
        }

        proveedores.forEach(p => {
            const item = document.createElement('div');
            item.className = 'sugerencia-item';
            item.style.cssText = 'padding: 9px 12px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.15s; font-size: 13px;';
            item.onmouseenter = () => item.style.background = '#f0fdfa';
            item.onmouseleave = () => item.style.background = '#ffffff';

            item.innerHTML = `
                <div>
                    <div style="font-weight: 600; color: #0f172a;">${p.nombre_proveedor}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 1px;">
                        NIT: <strong style="color: #0d9488;">${p.nit}</strong>
                    </div>
                </div>
                <span class="mod-badge badge-teal" style="font-size: 10.5px; padding: 2px 8px; flex-shrink: 0;">Seleccionar</span>
            `;

            item.addEventListener('click', () => {
                seleccionarProveedorSugerido(p);
            });

            contenedor.appendChild(item);
        });

        contenedor.style.display = 'block';
    }

    function buscarSugerenciasProveedores(q, contenedor) {
        if (!contenedor) return;
        if (!q || q.trim().length < 2) {
            contenedor.style.display = 'none';
            contenedor.innerHTML = '';
            return;
        }

        fetch('<?= $basePath ?>?module=proveedores&action=ajax_buscar&term=' + encodeURIComponent(q.trim()))
            .then(res => res.json())
            .then(proveedores => {
                renderizarSugerencias(contenedor, proveedores);
            })
            .catch(() => {
                contenedor.style.display = 'none';
            });
    }

    if (inputProv) {
        inputProv.addEventListener('input', function() {
            clearTimeout(timerBusqueda);
            const val = this.value;
            if (listaProvNit) {
                listaProvNit.style.display = 'none';
                listaProvNit.innerHTML = '';
            }
            if (!val.trim()) {
                limpiarCamposProveedor(true, false);
                if (badgeProv) {
                    badgeProv.className = 'mod-badge badge-gold';
                    badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
                }
                if (hdnEstado) hdnEstado.value = 'nuevo';
                if (listaProvDirecta) {
                    listaProvDirecta.style.display = 'none';
                    listaProvDirecta.innerHTML = '';
                }
            } else {
                timerBusqueda = setTimeout(() => {
                    buscarSugerenciasProveedores(val, listaProvDirecta);
                    verificarProveedor(val);
                }, 250);
            }
        });
    }

    const inputNit = document.getElementById('inputProveedorNit');
    if (inputNit) {
        inputNit.addEventListener('input', function() {
            clearTimeout(timerBusqueda);
            const val = this.value.trim();
            if (listaProvDirecta) {
                listaProvDirecta.style.display = 'none';
                listaProvDirecta.innerHTML = '';
            }
            if (!val) {
                if (listaProvNit) {
                    listaProvNit.style.display = 'none';
                    listaProvNit.innerHTML = '';
                }
                // Al borrar el NIT, limpiamos los campos asociados (banco, cuenta, etc.)
                limpiarCamposProveedor(false, false);
                if (badgeProv) {
                    badgeProv.className = 'mod-badge badge-gold';
                    badgeProv.innerHTML = '<i class="bi bi-plus-circle"></i> Proveedor Nuevo';
                }
                if (hdnEstado) hdnEstado.value = 'nuevo';
            } else if (val.length >= 2) {
                timerBusqueda = setTimeout(() => {
                    buscarSugerenciasProveedores(val, listaProvNit);
                    verificarProveedor(val);
                }, 250);
            }
        });
    }

    // Ocultar sugerencias al hacer clic afuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-live')) {
            ocultarListasSugerencias();
        }
    });

    form.addEventListener('submit', function(e) {
        const filas = tbody.querySelectorAll('tr:not(#filaEmptyState)');
        if (filas.length === 0) {
            e.preventDefault();
            alert('⚠️ Debe agregar al menos un producto del catálogo a la orden.');
            return false;
        }

        for (let i = 0; i < filas.length; i++) {
            const tr = filas[i];
            const titulo = tr.querySelector('.item-titulo')?.value.trim() || 'Ítem ' + (i + 1);
            const cantInput = tr.querySelector('.item-cantidad');
            const precioInput = tr.querySelector('.item-precio');

            const cant = parseFloat(cantInput?.value);
            if (isNaN(cant) || cant < 1) {
                e.preventDefault();
                alert(`⚠️ La cantidad para "${titulo}" debe ser un número entero mayor o igual a 1.`);
                cantInput?.focus();
                return false;
            }

            const precio = parseFloat(precioInput?.value);
            if (isNaN(precio) || precio < 0) {
                e.preventDefault();
                alert(`⚠️ El precio para "${titulo}" no puede ser negativo.`);
                precioInput?.focus();
                return false;
            }
        }

        const fleteInp = document.getElementById('inputFlete');
        if (fleteInp && parseFloat(fleteInp.value) < 0) {
            e.preventDefault();
            alert('⚠️ El valor del flete no puede ser negativo.');
            fleteInp.focus();
            return false;
        }

        const descInp = document.getElementById('inputDescuentoValor');
        if (descInp && parseFloat(descInp.value) < 0) {
            e.preventDefault();
            alert('⚠️ El valor del descuento no puede ser negativo.');
            descInp.focus();
            return false;
        }

        const retInp = document.getElementById('inputRetencion');
        if (retInp && (parseFloat(retInp.value) < 0 || parseFloat(retInp.value) > 100)) {
            e.preventDefault();
            alert('⚠️ El porcentaje de retención debe estar entre 0% y 100%.');
            retInp.focus();
            return false;
        }

        const saldoInp = document.getElementById('inputSaldoFavor');
        if (saldoInp && parseFloat(saldoInp.value) < 0) {
            e.preventDefault();
            alert('⚠️ El saldo a favor no puede ser negativo.');
            saldoInp.focus();
            return false;
        }
    });

    // Si ya hay un proveedor precargado (modo ajuste), sincronizar badge y datos bancarios/contacto
    if (inputProv && inputProv.value.trim().length >= 2) {
        verificarProveedor(inputProv.value.trim());
    } else if (inputNit && inputNit.value.trim().length >= 3) {
        verificarProveedor(inputNit.value.trim());
    }

})();
</script>

<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
