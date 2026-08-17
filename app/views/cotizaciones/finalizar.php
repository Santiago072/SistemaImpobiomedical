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
                <div class="mod-table-wrap p-24 overflow-visible">
                    <h2 class="mod-title mb-16"><i class="bi bi-building"></i> Datos del Cliente</h2>

                    <!-- Buscar cliente del catálogo -->
                    <div class="search-live mb-16">
                        <label class="client-search-label">
                            Buscar cliente del catálogo (opcional)
                        </label>
                        <input type="text" id="busquedaCliente" placeholder="Nombre o NIT..." class="mod-search-input cot-live-input">
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
                                <label>Departamento</label>
                                <input type="text" name="cliente_departamento" id="inpClienteDepto" maxlength="60" value="<?= htmlspecialchars($cotizacion['cliente_departamento'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Ciudad / Municipio</label>
                                <input type="text" name="cliente_ciudad" id="inpClienteCiudad" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_ciudad'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Nombre Contacto</label>
                                <input type="text" name="cliente_contacto" id="inpClienteContacto" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_contacto'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Teléfono</label>
                                <input type="text" name="cliente_telefono" id="inpClienteTel" maxlength="30" value="<?= htmlspecialchars($cotizacion['cliente_telefono'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="imo-form-row">
                            <div class="imo-form-group">
                                <label>Dirección</label>
                                <input type="text" name="cliente_direccion" id="inpClienteDir" maxlength="200" value="<?= htmlspecialchars($cotizacion['cliente_direccion'] ?? '') ?>">
                            </div>
                            <div class="imo-form-group">
                                <label>Correo Electrónico</label>
                                <input type="email" name="cliente_correo" id="inpClienteEmail" maxlength="100" value="<?= htmlspecialchars($cotizacion['cliente_correo'] ?? '') ?>">
                            </div>
                        </div>

                        <hr class="section-divider">
                        <h3 class="mod-title font-16 mb-16"><i class="bi bi-calendar3"></i> Condiciones</h3>

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

                        <div class="imo-modal-footer footer-plain">
                            <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="imo-btn-cancel text-nodecor">
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
                <div class="mod-table-wrap p-24">
                    <h2 class="mod-title mb-16"><i class="bi bi-receipt"></i> Resumen de Ítems</h2>
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
                                <tr><td colspan="2" class="text-right">Base</td><td>$<?= number_format($totalBase, 0, ',', '.') ?></td></tr>
                                <tr><td colspan="2" class="text-right">IVA</td><td>$<?= number_format($totalIva, 0, ',', '.') ?></td></tr>
                                <tr class="total-row-highlight"><td colspan="2" class="text-right">TOTAL</td><td class="font-16">$<?= number_format($granTotal, 0, ',', '.') ?></td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

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
    document.getElementById('inpClienteDepto').value     = c.departamento || '';
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
