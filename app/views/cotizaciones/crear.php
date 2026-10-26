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
            <?php if(isset($_SESSION['cotizacion_ajustando_id'])): ?>
            <h1 class="page-title"><i class="bi bi-wrench-adjustable-circle-fill"></i> Ajustando Cotización: <?= htmlspecialchars($_SESSION['cotizacion_ajustando_numero'] ?? '') ?></h1>
            <p class="text-warning-gold mb-10"><i class="bi bi-info-circle-fill"></i> Corrección directa. Se conservará el <strong>mismo número de cotización (<?= htmlspecialchars($_SESSION['cotizacion_ajustando_numero'] ?? '') ?>)</strong> sin generar una nueva versión.</p>
            <?php elseif(isset($_SESSION['cotizacion_revision_de'])): ?>
            <h1 class="page-title"><i class="bi bi-pencil-square"></i> Modificando Cotización: <?= htmlspecialchars($_SESSION['cotizacion_revision_de']) ?></h1>
            <p class="text-warning-gold mb-10"><i class="bi bi-info-circle-fill"></i> Puedes editar, eliminar o agregar productos. El nuevo PDF tendrá un número derivado al finalizar.</p>
            <?php else: ?>
            <h1 class="page-title"><i class="bi bi-plus-circle-fill"></i> Nueva Cotización</h1>
            <?php endif; ?>
            <div class="header-actions-wrap align-center mt-8">
                <p class="page-sub m-0">Ítems agregados: <strong><?= $totalItems ?></strong></p>
                <?php if ($totalItems > 0): ?>
                    <?php if (isset($_SESSION['cotizacion_ajustando_id'])): ?>
                    <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=guardar_ajuste_directo" style="display:inline; margin:0;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button type="submit" class="btn-mod-primary btn-sm-action" style="background:#0f766e; border-color:#0d9488; font-weight:700;" title="Aplica directamente los cambios a la cotización">
                            <i class="bi bi-check2-circle"></i> Guardar Ajuste
                        </button>
                    </form>
                    <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary btn-sm-action" style="background:#475569; border-color:#334155;">
                        <i class="bi bi-person-lines-fill"></i> Revisar Cliente
                    </a>
                    <?php else: ?>
                    <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary btn-sm-action">
                        <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Datos Cliente y PDF
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['cotizacion_ajustando_id'])): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=cancelar_ajuste" class="btn-discard-draft" onclick="return confirm('¿Seguro que deseas salir del ajuste? Los cambios no guardados se descartarán.');">
                    <i class="bi bi-x-circle-fill"></i> Cancelar Ajuste
                </a>
                <?php elseif (isset($_SESSION['cotizacion_revision_de'])): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=limpiar_borrador" class="btn-discard-draft" onclick="return confirm('¿Seguro que deseas cancelar la modificación y empezar una nueva cotización en blanco?');">
                    <i class="bi bi-x-circle-fill"></i> Cancelar Modificación y Empezar una Nueva
                </a>
                <?php elseif ($totalItems > 0): ?>
                <a href="<?= $basePath ?>?module=cotizaciones&action=limpiar_borrador" class="btn-discard-draft" onclick="return confirm('¿Seguro que deseas descartar esta cotización y empezar de cero?');">
                    <i class="bi bi-trash-fill"></i> Descartar Borrador
                </a>
                <?php endif; ?>
            </div>
        </div>



        <?php
        $errorRaw = urldecode($_GET['error'] ?? '');
        if (!empty($errorRaw)):
            // Mapa de mensajes amigables: [código/fragmento de error] => [icono, título, descripción, sugerencia]
            $erroresAmigables = [
                'csrf' => [
                    'icono'     => 'bi-shield-lock-fill',
                    'titulo'    => 'Sesión de seguridad expirada',
                    'desc'      => 'Tu sesión de seguridad venció por inactividad. Esto es normal si tardaste más de unos minutos completando el formulario.',
                    'sug'       => 'Recarga la página con F5 para obtener un nuevo token y vuelve a intentarlo. No perderás los ítems ya guardados.',
                ],
                'El nombre del proveedor es obligatorio' => [
                    'icono'     => 'bi-building-exclamation',
                    'titulo'    => 'Falta el proveedor',
                    'desc'      => 'No se escribió el nombre del proveedor del producto antes de guardar.',
                    'sug'       => 'En la sección derecha del formulario, escribe el nombre o NIT del proveedor en el campo <strong>Proveedor</strong> e intenta de nuevo.',
                ],
                'Cantidad y precio' => [
                    'icono'     => 'bi-calculator',
                    'titulo'    => 'Valores numéricos inválidos',
                    'desc'      => 'La cantidad debe ser mayor a 0 y el precio no puede ser negativo ni estar vacío.',
                    'sug'       => 'Revisa los campos <strong>Cantidad</strong> y <strong>Precio</strong> e ingresa valores válidos antes de guardar.',
                ],
                'No se pudo guardar el ítem' => [
                    'icono'     => 'bi-wifi-off',
                    'titulo'    => 'Error al conectar con el servidor',
                    'desc'      => 'No fue posible registrar el ítem en este momento.',
                    'sug'       => 'Verifica tu conexión a internet e intenta nuevamente. Si el problema persiste, contacta al administrador del sistema.',
                ],
                'IVA no válido' => [
                    'icono'     => 'bi-percent',
                    'titulo'    => 'Valor de IVA no reconocido',
                    'desc'      => 'El campo de IVA tiene un valor inesperado.',
                    'sug'       => 'Selecciona <strong>Sí</strong> o <strong>No</strong> en el campo <strong>¿Aplica IVA?</strong> e intenta de nuevo.',
                ],
                'demasiado grande' => [
                    'icono'     => 'bi-file-earmark-x-fill',
                    'titulo'    => 'Imagen demasiado pesada',
                    'desc'      => 'El archivo de imagen supera el límite permitido de <strong>8 MB</strong>.',
                    'sug'       => 'Comprime la imagen antes de adjuntarla. Puedes reducir su tamaño desde el celular en <em>Editar foto → Recortar/Reducir</em>, o usar una herramienta online gratuita.',
                ],
                'no es una imagen válida' => [
                    'icono'     => 'bi-image-alt',
                    'titulo'    => 'Formato de imagen no compatible',
                    'desc'      => 'El archivo que intentaste subir no es una imagen válida o su formato no está permitido.',
                    'sug'       => 'Usa solo imágenes en formato <strong>JPG, PNG o WebP</strong>. Si tienes un PDF o Word, toma una captura de pantalla y súbela como imagen.',
                ],
                'item_error' => [
                    'icono'     => 'bi-exclamation-triangle-fill',
                    'titulo'    => 'Error al procesar el ítem',
                    'desc'      => 'Ocurrió un inconveniente al intentar guardar el ítem de la cotización.',
                    'sug'       => 'Verifique que los datos ingresados sean correctos e intente nuevamente. Si el problema persiste, contacte al administrador.',
                ],
                'no_items' => [
                    'icono'     => 'bi-cart-x-fill',
                    'titulo'    => 'No hay productos en la cotización',
                    'desc'      => 'Debes agregar al menos un producto a la cotización antes de proceder a completarla o finalizarla.',
                    'sug'       => 'Selecciona un producto del catálogo o agrega un ítem manualmente antes de continuar.',
                ],
            ];

            $errorInfo = null;
            foreach ($erroresAmigables as $patron => $info) {
                if (stripos($errorRaw, $patron) !== false) {
                    $errorInfo = $info;
                    break;
                }
            }

            // Si no hay mapeo, mostrar el error técnico con un mensaje genérico
            if (!$errorInfo) {
                $errorInfo = [
                    'icono'  => 'bi-exclamation-triangle-fill',
                    'titulo' => 'No fue posible guardar el ítem',
                    'desc'   => htmlspecialchars($errorRaw),
                    'sug'    => 'Si el problema persiste, toma una captura de pantalla de esta pantalla y compártela con el administrador.',
                ];
            }
        ?>
        <div class="mod-alert mod-alert-err" style="display:flex; gap:14px; align-items:flex-start; flex-wrap:wrap;">
            <i class="bi <?= $errorInfo['icono'] ?>" style="font-size:1.6rem; flex-shrink:0; margin-top:2px;"></i>
            <div>
                <strong style="display:block; font-size:1rem; margin-bottom:4px;"><?= $errorInfo['titulo'] ?></strong>
                <span style="display:block; color:#7f1d1d; margin-bottom:6px;"><?= $errorInfo['desc'] ?></span>
                <span style="display:block; font-size:0.85rem; color:#92400e; background:#fef3c7; border-radius:6px; padding:5px 10px;">
                    <i class="bi bi-lightbulb-fill" style="color:#d97706;"></i> <strong>Sugerencia:</strong> <?= $errorInfo['sug'] ?>
                </span>
            </div>
        </div>
        <?php
            unset($erroresAmigables, $errorInfo, $patron, $info);
        endif; ?>

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
                                        <label>Precio Proveedor Base ($)</label>
                                        <input type="number" name="precio_proveedor" id="inpPrecioProveedor"
                                               min="0" step="0.01" placeholder="0.00" oninput="calcularTotales()">
                                    </div>
                                    <div class="imo-form-group search-live" style="position: relative;">
                                        <label>Proveedor (Buscar por NIT o Nombre)</label>
                                        <input type="text" name="proveedor" id="inpProveedor" maxlength="100"
                                               placeholder="Escribe NIT o Nombre..." autocomplete="off">
                                        <input type="hidden" name="proveedor_id" id="hdnProveedorId" value="">
                                        <input type="hidden" name="proveedor_nit" id="hdnProveedorNit" value="">
                                        <div id="listaProveedores" class="lista-sugerencias" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; background:#ffffff; border:1.5px solid #0d9488; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.12); max-height:220px; overflow-y:auto;"></div>
                                    </div>
                                </div>
                                <div class="imo-form-row">
                                    <div class="imo-form-group">
                                        <label>NIT del Proveedor</label>
                                        <input type="text" id="dispProveedorNit" readonly class="form-control-plaintext"
                                               placeholder="Se asigna automáticamente al seleccionar"
                                               style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:6px; padding:7px 12px; font-family:monospace; font-weight:700; color:#0f766e;">
                                    </div>
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
                                <option value="Servicios">Servicios</option>
                            </select>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Código del Producto</label>
                                <input type="text" name="codigo_producto" id="inpCodigoProducto" maxlength="60"
                                       placeholder="Ej: MQ-001">
                            </div>
                            <div class="imo-form-group">
                                <label>Nombre del Producto *</label>
                                <input type="text" name="titulo" id="inpTitulo" required maxlength="255"
                                       value="<?= htmlspecialchars($producto['titulo'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Marca *</label>
                                <input type="text" name="marca" id="inpMarca" required maxlength="100"
                                       placeholder="Ej: Philips, 3M, Mindray..."
                                       value="<?= htmlspecialchars($producto['marca'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Modelo <span id="reqModeloStar" style="color: #ef4444;">*</span></label>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="text" name="modelo" id="inpModelo" required maxlength="100"
                                           placeholder="Ej: Series 2000, Pro-X..."
                                           style="flex: 1;"
                                           value="<?= htmlspecialchars($producto['modelo'] ?? '') ?>">
                                    <label style="font-size: 11.5px; font-weight: 500; color: #475569; margin-bottom: 0; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; padding: 7px 10px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; user-select: none;">
                                        <input type="checkbox" name="sin_modelo" id="chkSinModelo" value="1" onchange="toggleSinModelo(this.checked)" style="cursor: pointer; margin: 0;">
                                        Sin modelo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="imo-form-group">
                                    <label>Cantidad *</label>
                                    <input type="number" name="cantidad" id="inpCantidad" min="1" value="<?= $producto['cantidad'] ?? 1 ?>" required>
                                </div>
                                <div class="imo-form-group">
                                    <label>Unidad de Medida</label>
                                    <input type="text" name="unidad" id="inpUnidad" maxlength="50"
                                           placeholder="Ej: Unidad, Caja..."
                                           value="<?= htmlspecialchars($producto['unidad'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="imo-form-group">
                                <label>Precio Unitario (sin IVA)
                                    <span id="lblPrecioFuente" class="price-source-lbl">(← de Estampillas)</span>
                                </label>
                                <input type="number" name="precio" id="inpPrecio" min="0" step="0.01"
                                       value="<?= $producto['precio'] ?? '' ?>" placeholder="0.00">
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
                            <small class="imo-hint" style="display:block; margin-top:4px; font-size:11px; color:#64748b;">Máx: 8MB · Formatos: JPG, PNG, WebP</small>
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
                                    <th style="width: 44px; text-align: center;">#</th>
                                    <th>Producto</th>
                                    <th style="text-align: center;">Cant.</th>
                                    <th>Precio</th>
                                    <th>IVA</th>
                                    <th>Subtotal</th>
                                    <th>Entrega</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCotizacionItems">
                            <?php $totalCot = 0; $numItems = count($items); foreach ($items as $idx => $it):
                                $pu  = (float)$it['precio'];
                                $qty = (int)$it['cantidad'];
                                $pct = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaAmt = ($it['iva'] === 'si') ? $pu * $qty * ($pct / 100) : 0;
                                $sub = $pu * $qty + $ivaAmt;
                                $totalCot += $sub;
                                $esPrimero = ($idx === 0);
                                $esUltimo  = ($idx === $numItems - 1);
                            ?>
                                <tr data-item-id="<?= (int)$it['id'] ?>" class="fila-cotizacion-item">
                                    <td style="text-align: center; color: #64748b; font-weight: 700; font-size: 12px;">
                                        <?= $idx + 1 ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($it['foto'])): ?>
                                        <img src="<?= $basePath ?>uploads/<?= htmlspecialchars($it['foto']) ?>"
                                             class="item-thumb-img">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 40, '…')) ?>
                                        <?php 
                                            $marcaShow = trim($it['marca'] ?? '');
                                            $modeloShow = trim($it['modelo'] ?? '');
                                            $esSinModelo = ($modeloShow === 'N/A' || $modeloShow === '-' || empty($modeloShow));
                                            $subTexto = '';
                                            if (!empty($marcaShow) && !$esSinModelo) {
                                                $subTexto = $marcaShow . ' · ' . $modeloShow;
                                            } elseif (!empty($marcaShow)) {
                                                $subTexto = $marcaShow;
                                            } elseif (!$esSinModelo) {
                                                $subTexto = $modeloShow;
                                            }
                                        ?>
                                        <?php if (!empty($subTexto)): ?>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                                <?= htmlspecialchars($subTexto) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="font-weight: 700;"><?= $qty ?></div>
                                        <?php if (!empty($it['unidad'])): ?>
                                            <div style="font-size: 11px; color: #0d9488; font-weight: 600; text-align: center;"><?= htmlspecialchars($it['unidad']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>$<?= number_format($pu, 0, ',', '.') ?></td>
                                    <td><?= $it['iva'] === 'si' ? $pct . '%' : 'No' ?></td>
                                    <td><strong>$<?= number_format($sub, 0, ',', '.') ?></strong></td>
                                    <td class="font-11"><?= htmlspecialchars($it['tiempo_entrega'] ?? '') ?></td>
                                    <td>
                                        <div class="mod-actions" style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                            <!-- Botones para reordenar -->
                                            <button type="button" 
                                                    onclick="moverFilaItem(<?= (int)$it['id'] ?>, 'arriba')" 
                                                    class="mod-btn-move" 
                                                    title="Mover arriba"
                                                    <?= $esPrimero ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" 
                                                    onclick="moverFilaItem(<?= (int)$it['id'] ?>, 'abajo')" 
                                                    class="mod-btn-move" 
                                                    title="Mover abajo"
                                                    <?= $esUltimo ? 'disabled style="opacity:0.3; cursor:not-allowed;"' : '' ?>>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>

                                            <a href="<?= $basePath ?>?module=cotizaciones&action=editar_item&id=<?= $it['id'] ?>"
                                               class="mod-btn-edit" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                            <button type="button" onclick="eliminarItem(<?= $it['id'] ?>)"
                                                    class="mod-btn-del" title="Eliminar"><i class="bi bi-trash-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>TOTAL ESTIMADO:</strong></td>
                                    <td colspan="3"><strong class="total-highlight">$<?= number_format($totalCot, 0, ',', '.') ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php if ($totalItems > 0): ?>
                    <div class="mt-24" style="display: flex; gap: 12px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                        <?php if (isset($_SESSION['cotizacion_ajustando_id'])): ?>
                            <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=guardar_ajuste_directo" style="display:inline; margin:0;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <button type="submit" class="btn-mod-primary" style="background:#0f766e; border-color:#0d9488; font-weight:700; padding:10px 20px; box-shadow:0 2px 6px rgba(15,118,110,0.25);" title="Aplica directamente los cambios a la cotización sin pasar por datos del cliente">
                                    <i class="bi bi-check2-circle"></i> Guardar Ajuste
                                </button>
                            </form>
                            <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary text-nodecor" style="background:#475569; border-color:#334155;">
                                <i class="bi bi-person-lines-fill"></i> Opcional: Revisar Datos del Cliente <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php else: ?>
                            <a href="<?= $basePath ?>?module=cotizaciones&action=finalizar" class="btn-mod-primary text-nodecor">
                                <i class="bi bi-arrow-right-circle-fill"></i> Continuar → Completar Datos del Cliente
                            </a>
                        <?php endif; ?>
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
                    div.style.cssText = 'padding: 10px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: background 0.15s;';
                    div.onmouseenter = () => div.style.background = '#f8fafc';
                    div.onmouseleave = () => div.style.background = '#fff';

                    const fotoHtml = p.foto 
                        ? `<img src="${BASE}uploads/${p.foto}" style="width: 38px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; flex-shrink: 0;">`
                        : `<div style="width: 38px; height: 38px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;"><i class="bi bi-box-seam"></i></div>`;

                    div.innerHTML = `
                        ${fotoHtml}
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 13px; color: #1e293b;">${p.titulo}</div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                ${p.categoria ? `<span class="mod-badge badge-blue" style="font-size: 10px; padding: 1px 6px;">${p.categoria}</span> ` : ''}
                                ${(p.marca || p.modelo) ? `<span style="color:#64748b; font-size:11px;">${[p.marca, p.modelo].filter(Boolean).join(' - ')}</span>` : ''}
                            </div>
                        </div>
                        <span class="mod-badge badge-green" style="font-size: 11px;">+ Agregar</span>
                    `;
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
    
    // Si la calculadora de ganancias ya tiene un precio calculado de estampillas, respetarlo y no sobreescribir
    const precioCalculadoEstampillas = parseFloat(document.getElementById('hdnEstampillas')?.value) || 0;
    const precioProveedorBase = parseFloat(document.getElementById('inpPrecioProveedor')?.value) || 0;
    
    if (precioProveedorBase <= 0) {
        document.getElementById('inpPrecio').value = p.precio;
        const lbl = document.getElementById('lblPrecioFuente');
        if (lbl) lbl.textContent = '(← de Estampillas)';
    } else {
        // Si hay calculadora activa, recalcular totales para mantener sincronizado
        calcularTotales();
    }

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
    if (document.getElementById('inpMarca')) {
        document.getElementById('inpMarca').value      = p.marca || '';
    }
    if (document.getElementById('inpModelo')) {
        const modVal = p.modelo || '';
        const esSinMod = (modVal === 'N/A' || modVal === '-');
        document.getElementById('chkSinModelo').checked = esSinMod;
        toggleSinModelo(esSinMod);
        if (!esSinMod) {
            document.getElementById('inpModelo').value = modVal;
        }
    }
    if (document.getElementById('inpUnidad')) {
        document.getElementById('inpUnidad').value     = p.unidad || '';
    }
    // Preservar código de proveedor si el usuario ya lo escribió en la calculadora
    const inpCodProv = document.getElementById('inpCodigoProveedor');
    if (inpCodProv) {
        if (p.codigo_proveedor) {
            inpCodProv.value = p.codigo_proveedor;
        }
    }
    
    document.getElementById('badgeAuto').style.display = 'inline';
    document.getElementById('listaProductos').style.display = 'none';
    document.getElementById('busquedaProducto').value = '';
    toggleIva((p.iva || 'si').toLowerCase());
    calcularPreview();
}

function toggleSinModelo(sinModelo) {
    const inp = document.getElementById('inpModelo');
    const star = document.getElementById('reqModeloStar');
    if (!inp) return;
    if (sinModelo) {
        if (inp.value && inp.value !== 'N/A') {
            inp.dataset.prevModelo = inp.value;
        }
        inp.value = 'N/A';
        inp.readOnly = true;
        inp.style.backgroundColor = '#f1f5f9';
        inp.style.color = '#94a3b8';
        if (star) star.style.display = 'none';
    } else {
        const prev = inp.dataset.prevModelo || '';
        inp.value = (prev !== 'N/A') ? prev : '';
        inp.readOnly = false;
        inp.style.backgroundColor = '';
        inp.style.color = '';
        if (star) star.style.display = 'inline';
    }
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
    if (document.getElementById('inpMarca')) document.getElementById('inpMarca').value = '';
    if (document.getElementById('chkSinModelo')) document.getElementById('chkSinModelo').checked = false;
    toggleSinModelo(false);
    if (document.getElementById('inpModelo')) document.getElementById('inpModelo').value = '';
    if (document.getElementById('inpUnidad')) document.getElementById('inpUnidad').value = '';
    
    document.getElementById('inpPrecioProveedor').value = '';
    document.getElementById('inpProveedor').value = '';
    document.getElementById('hdnProveedorId').value = '';
    document.getElementById('hdnProveedorNit').value = '';
    const dispNit = document.getElementById('dispProveedorNit');
    if (dispNit) dispNit.value = '';
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

// ── Búsqueda Predictiva de Proveedores por NIT o Nombre ─────────────────────
const inpProv = document.getElementById('inpProveedor');
const listaProv = document.getElementById('listaProveedores');
let timerProv = null;

if (inpProv && listaProv) {
    inpProv.addEventListener('input', function() {
        clearTimeout(timerProv);
        const term = this.value.trim();
        if (term.length < 2) {
            listaProv.style.display = 'none';
            listaProv.innerHTML = '';
            return;
        }

        timerProv = setTimeout(() => {
            fetch(BASE + '?module=proveedores&action=ajax_buscar&term=' + encodeURIComponent(term))
                .then(r => r.json())
                .then(data => {
                    listaProv.innerHTML = '';
                    if (!data || data.length === 0) {
                        listaProv.innerHTML = '<div class="prov-search-empty"><i class="bi bi-info-circle"></i> No se encontró proveedor registrado con ese NIT o nombre.</div>';
                    } else {
                        data.forEach(p => {
                            const item = document.createElement('div');
                            item.className = 'prov-search-item';
                            item.innerHTML = `
                                <div>
                                    <div style="font-size:13.5px; color:#0f172a; font-weight:700;">${p.nombre_proveedor}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                        NIT: <strong style="color:#0d9488;">${p.nit}</strong>
                                    </div>
                                </div>
                                <span class="mod-badge badge-green" style="font-size:11px; padding:3px 10px;">Seleccionar</span>
                            `;
                            item.addEventListener('click', () => {
                                inpProv.value = p.nombre_proveedor;
                                document.getElementById('hdnProveedorId').value = p.id;
                                document.getElementById('hdnProveedorNit').value = p.nit;
                                const dNit = document.getElementById('dispProveedorNit');
                                if (dNit) dNit.value = p.nit;
                                listaProv.style.display = 'none';
                            });
                            listaProv.appendChild(item);
                        });
                    }
                    listaProv.style.display = 'block';
                })
                .catch(err => {
                    console.error('Error buscando proveedores:', err);
                });
        }, 250);
    });
}

// ── Reordenar ítem (Subir / Bajar con flechas fluidas en vivo) ───────────────
function moverFilaItem(id, direccion) {
    const fila = document.querySelector(`tr[data-item-id="${id}"]`);
    if (!fila) return;

    const tbody = document.getElementById('tbodyCotizacionItems');
    if (!tbody) return;

    const filaDestino = (direccion === 'arriba') ? fila.previousElementSibling : fila.nextElementSibling;
    if (!filaDestino || !filaDestino.classList.contains('fila-cotizacion-item')) {
        return; // Ya está al tope o al final
    }

    // 1. Intercambio visual inmediato en el DOM sin recargas ni alerts
    if (direccion === 'arriba') {
        tbody.insertBefore(fila, filaDestino);
    } else {
        tbody.insertBefore(filaDestino, fila);
    }

    // 2. Actualizar numeración visual (# 1, 2, 3...) y estado de los botones
    actualizarNumerosYBotonesReorden();

    // 3. Sincronizar en segundo plano con el servidor
    const formData = new FormData();
    formData.append('csrf_token', CSRF);
    formData.append('id', id);
    formData.append('direccion', direccion);

    fetch(BASE + '?module=cotizaciones&action=reordenar_item', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.status !== 'success') {
            console.error('Error al sincronizar orden:', res.message);
        }
    })
    .catch(err => {
        console.error('Error al comunicar reordenamiento:', err);
    });
}

function actualizarNumerosYBotonesReorden() {
    const filas = document.querySelectorAll('#tbodyCotizacionItems tr.fila-cotizacion-item');
    const total = filas.length;

    filas.forEach((tr, index) => {
        // Actualizar número #
        const tdNum = tr.querySelector('td:first-child');
        if (tdNum) tdNum.textContent = index + 1;

        // Actualizar estado de las flechas (arriba / abajo)
        const btnUp   = tr.querySelector('.mod-btn-move[title="Mover arriba"]');
        const btnDown = tr.querySelector('.mod-btn-move[title="Mover abajo"]');

        if (btnUp) {
            if (index === 0) {
                btnUp.disabled = true;
                btnUp.style.opacity = '0.3';
                btnUp.style.cursor = 'not-allowed';
            } else {
                btnUp.disabled = false;
                btnUp.style.opacity = '1';
                btnUp.style.cursor = 'pointer';
            }
        }

        if (btnDown) {
            if (index === total - 1) {
                btnDown.disabled = true;
                btnDown.style.opacity = '0.3';
                btnDown.style.cursor = 'not-allowed';
            } else {
                btnDown.disabled = false;
                btnDown.style.opacity = '1';
                btnDown.style.cursor = 'pointer';
            }
        }
    });
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

// Cerrar listas sugerencias al hacer clic fuera
document.addEventListener('click', e => {
    if (!e.target.closest('.search-live')) {
        const lp = document.getElementById('listaProductos');
        if (lp) lp.style.display = 'none';
        const lprov = document.getElementById('listaProveedores');
        if (lprov) lprov.style.display = 'none';
    }
});

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

sanitizarNumeroPositivo(document.getElementById('inpCantidad'), 1, true);
sanitizarNumeroPositivo(document.getElementById('inpPrecio'), 0, false);
sanitizarNumeroPositivo(document.getElementById('inpPrecioProveedor'), 0, false);
sanitizarNumeroPositivo(document.getElementById('inpPctIva'), 0, false);

// Serializar calcState en hidden antes de enviar el formulario con validación previa
document.getElementById('formItem').addEventListener('submit', function(e) {
    const cantInp = document.getElementById('inpCantidad');
    const precioInp = document.getElementById('inpPrecio');
    const cant = parseFloat(cantInp?.value);
    const precio = parseFloat(precioInp?.value);

    if (isNaN(cant) || cant < 1) {
        e.preventDefault();
        alert('⚠️ La cantidad debe ser un número entero mayor o igual a 1.');
        cantInp?.focus();
        return false;
    }
    if (isNaN(precio) || precio < 0) {
        e.preventDefault();
        alert('⚠️ El precio unitario no puede ser negativo.');
        precioInp?.focus();
        return false;
    }

    document.getElementById('hdnCalcOps').value = JSON.stringify(calcState);
});

toggleIva('si');
calcularPreview();
renderCalculadoraInputs();
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
