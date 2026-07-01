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
        <div class="alerta alerta-error"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <div class="cot-grid">

            <!-- ── Formulario ── -->
            <div class="panel-form">
                <div class="card-panel">
                    <h2 class="card-title"><i class="bi bi-building"></i> Datos del Cliente</h2>

                    <!-- Buscar cliente del catálogo -->
                    <div class="search-live" style="margin-bottom:16px;">
                        <label style="font-size:12px;color:rgba(255,255,255,.6);display:block;margin-bottom:4px;">
                            Buscar cliente del catálogo (opcional)
                        </label>
                        <input type="text" id="busquedaCliente" placeholder="Nombre o NIT..." class="input-field">
                        <div id="listaClientes" class="lista-sugerencias"></div>
                    </div>

                    <form method="POST" action="<?= $basePath ?>?module=cotizaciones&action=finalizar" id="formFinalizar">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="cliente_id" id="hdnClienteId" value="">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre / Entidad *</label>
                                <input type="text" name="cliente_nombre" id="inpClienteNombre" class="input-field" required maxlength="200">
                            </div>
                            <div class="form-group">
                                <label>NIT / CC</label>
                                <input type="text" name="cliente_nit" id="inpClienteNit" class="input-field" maxlength="30">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre Contacto</label>
                                <input type="text" name="cliente_contacto" id="inpClienteContacto" class="input-field" maxlength="100">
                            </div>
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="cliente_ciudad" id="inpClienteCiudad" class="input-field" maxlength="100">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="cliente_telefono" id="inpClienteTel" class="input-field" maxlength="30">
                            </div>
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" name="cliente_correo" id="inpClienteEmail" class="input-field" maxlength="100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="cliente_direccion" id="inpClienteDir" class="input-field" maxlength="200">
                        </div>

                        <hr style="border-color:var(--glass-border); margin:16px 0;">
                        <h3 class="card-title" style="font-size:14px;"><i class="bi bi-calendar3"></i> Condiciones</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Fecha de Cotización *</label>
                                <input type="date" name="fecha_creacion" class="input-field" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="form-group">
                                <label>Días de Validez *</label>
                                <input type="number" name="dias_validez" class="input-field" min="1" max="365" value="30" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Condiciones de Pago</label>
                            <input type="text" name="condiciones_pago" class="input-field" maxlength="100" value="CONTADO">
                        </div>

                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="input-field textarea-field" maxlength="1000"
                                      placeholder="Información adicional para el cliente..."></textarea>
                        </div>

                        <div class="btn-group-form">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                            </button>
                            <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="btn btn-outline">
                                <i class="bi bi-arrow-left"></i> Volver a ítems
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Resumen ── -->
            <div class="panel-lista">
                <div class="card-panel">
                    <h2 class="card-title"><i class="bi bi-receipt"></i> Resumen de Ítems</h2>
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
                        <table class="tabla-datos">
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
                                <tr><td colspan="2">Base</td><td>$<?= number_format($totalBase, 0, ',', '.') ?></td></tr>
                                <tr><td colspan="2">IVA</td><td>$<?= number_format($totalIva, 0, ',', '.') ?></td></tr>
                                <tr style="font-weight:700;color:var(--amber);"><td colspan="2">TOTAL</td><td>$<?= number_format($granTotal, 0, ',', '.') ?></td></tr>
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
    position: absolute; z-index: 200; width: 100%;
    background: #0f2a30; border: 1px solid var(--copper);
    border-radius: 0 0 10px 10px; max-height: 280px; overflow-y: auto;
    display: none;
}
.search-live { position: relative; }
.sugerencia-item {
    padding: 10px 14px; cursor: pointer;
    border-bottom: 1px solid rgba(255,255,255,.06);
    transition: background .15s; font-size:12px;
}
.sugerencia-item:hover { background: rgba(26,138,138,.2); }
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
            const lista = document.getElementById('listaClientes');
            lista.innerHTML = '';
            (json.data || []).forEach(c => {
                const div = document.createElement('div');
                div.className = 'sugerencia-item';
                div.innerHTML = `<strong>${c.nombre}</strong> — NIT: ${c.nit || '-'} — ${c.municipio || ''}`;
                div.addEventListener('click', () => autocompletar(c));
                lista.appendChild(div);
            });
            lista.style.display = json.data?.length ? 'block' : 'none';
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
