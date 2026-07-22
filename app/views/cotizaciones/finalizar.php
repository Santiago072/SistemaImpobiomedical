<?php
/**
 * Vista: Finalizar Cotización — datos del cliente, validez y condiciones.
 * Variables: $items, $cotizacion_id, $csrf_token, $mensajeError
 */
$pageTitle = 'Finalizar Cotización';
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/menu.php';
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>

<div class="layout-main">
    <?php include __DIR__ . '/../layout/topbar.php'; ?>

    <main class="contenido-principal">
        <div class="page-header">
            <h1 class="page-title"><i class="bi bi-person-lines-fill"></i> Completar Datos de la Cotización</h1>
            <p class="page-sub">Paso 2 de 2: datos del cliente y condiciones</p>
        </div>

        <?php if (!empty($mensajeError)): ?>
        <div class="mod-alert mod-alert-err"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="cot-grid">

            <!-- ── Formulario ── -->
            <div class="panel-form">
                <div class="mod-table-wrap" style="padding:24px; overflow:visible;">
                    <h2 class="mod-title" style="font-size:18px; margin-bottom:16px;"><i class="bi bi-building"></i> Datos del Cliente</h2>

                    <!-- Buscar cliente del catálogo -->
                    <div class="search-live" style="margin-bottom:16px;">
                        <label style="font-size:12px;color:#4b5563;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;">
                            Buscar cliente del catálogo (opcional)
                        </label>
                        <input type="text" id="busquedaCliente" placeholder="Nombre o NIT..." class="mod-search-input" style="width:100%; border:1.5px solid #e2e8f0; border-radius:9px; padding:11px 14px; background:#f8fafc;">
                        <div id="listaClientes" class="lista-sugerencias"></div>
                    </div>

                    <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=finalizar" id="formFinalizar">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="cliente_id" id="hdnClienteId" value="">

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Nombre / Entidad *</label>
                                <input type="text" name="cliente_nombre" id="inpClienteNombre" required maxlength="200" value="<?= htmlspecialchars($cotizacion['cliente_nombre'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>NIT / CC</label>
                                <input type="text" name="cliente_nit" id="inpClienteNit" maxlength="30" value="<?= htmlspecialchars($cotizacion['cliente_nit'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Nombre Contacto</label>
                                <input type="text" name="cliente_contacto" id="inpClienteContacto" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_contacto'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Ciudad</label>
                                <input type="text" name="cliente_ciudad" id="inpClienteCiudad" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_ciudad'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Teléfono</label>
                                <input type="text" name="cliente_telefono" id="inpClienteTel" maxlength="30" value="<?= htmlspecialchars($cotizacion['cliente_telefono'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Correo</label>
                                <input type="email" name="cliente_correo" id="inpClienteEmail" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_correo'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-group">
                            <label>Dirección</label>
                            <input type="text" name="cliente_direccion" id="inpClienteDir" maxlength="200" value="<?= htmlspecialchars($cotizacion['cliente_direccion'] ?? '') ?>">
                        </div>

                        <hr style="border:none; border-top:1px solid #e5e7eb; margin:24px 0;">
                        <h3 class="mod-title" style="font-size:16px; margin-bottom:16px;"><i class="bi bi-calendar3"></i> Condiciones</h3>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Fecha de Cotización *</label>
                                <input type="date" name="fecha_creacion" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Días de Validez *</label>
                                <input type="number" name="dias_validez" min="1" max="365" value="30" required>
                            </div>
                        </div>

                        <div class="imo-form-group">
                            <label>Condiciones de Pago</label>
                            <input type="text" name="condiciones_pago" maxlength="100" value="<?= htmlspecialchars($cotizacion['condiciones_pago'] ?? 'CONTADO') ?>">
                        </div>

                        <div class="imo-form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" style="padding:11px 14px; border:1.5px solid #e2e8f0; border-radius:9px; width:100%; height:100px; resize:vertical; outline:none;" maxlength="1000"
                                      placeholder="Información adicional para el cliente..."></textarea>
                        </div>

                        <div class="imo-modal-footer" style="border-top:none; padding-top:0;">
                            <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="imo-btn-cancel" style="text-decoration:none;">
                                <i class="bi bi-arrow-left"></i> Volver a ítems
                            </a>
                            <button type="submit" class="btn-mod-primary">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Resumen ── -->
            <div class="panel-lista">
                <div class="mod-table-wrap" style="padding:24px;">
                    <h2 class="mod-title" style="font-size:18px; margin-bottom:16px;"><i class="bi bi-receipt"></i> Resumen de Ítems</h2>
                    <?php
                    $totalBase = 0; $totalIva = 0;
                    foreach ($items as $it) {
                        $pu = (float)$it['precio']; $qty = (int)$it['cantidad'];
                        $pct = (float)($it['porcentaje_iva'] ?? 19);
                        $ivaAmt = ($it['iva'] === 'si') ? $pu * $qty * ($pct / 100) : 0;
                        $totalBase += $pu * $qty; $totalIva += $ivaAmt;
                    }
                    $granTotal = $totalBase + $totalIva;
                    ?>
                    <div class="tabla-responsive">
                        <table class="mod-table">
                            <thead><tr><th>Producto</th><th>Cant.</th><th>Total</th></tr></thead>
                            <tbody>
                            <?php foreach ($items as $it):
                                $pu = (float)$it['precio']; $qty = (int)$it['cantidad'];
                                $pct = (float)($it['porcentaje_iva'] ?? 19);
                                $ivaAmt = ($it['iva'] === 'si') ? $pu * $qty * ($pct / 100) : 0;
                                $sub = $pu * $qty + $ivaAmt;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars(mb_strimwidth($it['titulo'], 0, 35, '…')) ?></td>
                                    <td><?= $qty ?></td>
                                    <td>$<?= number_format($sub, 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="2" style="text-align:right;">Base</td><td>$<?= number_format($totalBase, 0, ',', '.') ?></td></tr>
                                <tr><td colspan="2" style="text-align:right;">IVA</td><td>$<?= number_format($totalIva, 0, ',', '.') ?></td></tr>
                                <tr style="font-weight:700;color:#f59e0b;"><td colspan="2" style="text-align:right;">TOTAL</td><td style="font-size:16px;">$<?= number_format($granTotal, 0, ',', '.') ?></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<style>
.lista-sugerencias {
    position: absolute; z-index: 999; width: 100%;
    background: #ffffff; border: 1px solid var(--copper);
    border-radius: 0 0 10px 10px; max-height: 280px; overflow-y: auto;
    display: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}
.search-live { position: relative; z-index: 999; }
.sugerencia-item {
    padding: 10px 14px; cursor: pointer; color: #1f2937;
    border-bottom: 1px solid #e5e7eb;
    transition: background .15s; font-size:13px;
}
.sugerencia-item:hover { background: rgba(26,138,138,.1); }
</style>

<script>
const BASE = '<?= $basePath ?>';
let timer;
document.getElementById('busquedaCliente').addEventListener('input', function() {
    clearTimeout(timer);
    timer = setTimeout(() => buscarClientes(this.value.trim()), 280);
});

function buscarClientes(q) {
    if (q.length < 2) { document.getElementById('listaClientes').style.display='none'; return; }
    fetch(BASE + '?module=cotizaciones&action=ajax_buscar_clientes&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(json => {
            if (json.status !== 'success') return;
            const lista = document.getElementById('listaClientes');
            lista.innerHTML = '';
            if (!json.data || !json.data.length) {
                lista.innerHTML = '<div class="sugerencia-item" style="color:#9ca3af">Sin resultados</div>';
            } else {
                json.data.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'sugerencia-item';
                    div.innerHTML = `<strong>${c.nombre}</strong> — NIT: ${c.nit || '-'} — ${c.municipio || ''}`;
                    div.addEventListener('click', () => autocompletar(c));
                    lista.appendChild(div);
                });
            }
            lista.style.display = 'block';
        });
}

function autocompletar(c) {
    document.getElementById('hdnClienteId').value        = c.id;
    document.getElementById('inpClienteNombre').value    = c.nombre;
    document.getElementById('inpClienteNit').value       = c.nit || '';
    document.getElementById('inpClienteDir').value       = c.direccion || '';
    document.getElementById('inpClienteTel').value       = c.telefono || '';
    document.getElementById('inpClienteEmail').value     = c.correo || '';
    document.getElementById('inpClienteContacto').value  = c.nombre_contacto || '';
    document.getElementById('inpClienteCiudad').value    = c.municipio || '';
    document.getElementById('listaClientes').style.display = 'none';
    document.getElementById('busquedaCliente').value = '';
}

document.addEventListener('click', e => {
    if (!e.target.closest('.search-live'))
        document.getElementById('listaClientes').style.display = 'none';
});
</script>

<script src="<?= $basePath ?>public/js/script.js"></script>
</body>
</html>
