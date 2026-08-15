<?php
/**
 * Menú lateral — partial puro.
 * Requiere sesión activa con $_SESSION['rol'] y $_SESSION['usuario_nombre'].
 */
if (!isset($_SESSION['usuario_nombre'])) {
    $base = defined('BASE_URL') ? BASE_URL : '/';
    header('Location: ' . $base);
    exit();
}
$rol      = $_SESSION['rol'];
$basePath = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>
<nav class="menu-principal">
    <div class="menu-lateral" id="menuLateral">
            <div class="logo-simple-wrap" style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 20px 0;">
                <div class="ecg-container-menu" style="width: 120px; height: 60px;">
                    <svg viewBox="0 0 500 100" preserveAspectRatio="none" style="width: 100%; height: 100%; stroke: #10757e; stroke-width: 4; fill: none; stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: dash 4s linear infinite; filter: drop-shadow(0 0 5px rgba(16,117,126,0.5));">
                        <polyline points="0,50 150,50 170,20 190,80 210,10 230,90 250,50 500,50" />
                    </svg>
                </div>
            </div>

        <ul class="lista-menu-lateral">
            <li>
                <a href="<?= $basePath ?>?module=panel" title="Panel">
                    <i class="bi bi-house-door-fill"></i>
                </a>
            </li>
            <?php if ($rol === 'admin'): ?>
            <li class="menu-desplegable" data-panel="admin" title="Administración">
                <a href="#"><i class="bi bi-gear-fill"></i></a>
            </li>
            <?php endif; ?>
            <li class="menu-desplegable" data-panel="cotizaciones" title="Cotizaciones">
                <a href="#"><i class="bi bi-file-earmark-text-fill"></i></a>
            </li>
            <li>
                <a href="<?= $basePath ?>?action=logout" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="panel-flotante" id="panel">
    <ul class="submenu" id="submenu-dinamico"></ul>
</div>

<script>
(function () {
    const panel   = document.getElementById('panel');
    const submenu = document.getElementById('submenu-dinamico');
    let   timeout;

    const menus = {
        admin: `
            <h3>Administración</h3>
            <li><a href="<?= $basePath ?>?module=usuarios"><i class="fas fa-users"></i> Gestión de Usuarios</a></li>
            <li><a href="<?= $basePath ?>?module=productos"><i class="fas fa-box-open"></i> Gestión de Productos</a></li>
            <li><a href="<?= $basePath ?>?module=clientes"><i class="fas fa-building"></i> Gestión de Clientes</a></li>
        `,
        cotizaciones: `
            <h3>Cotizaciones</h3>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=crear&nueva=1"><i class="fas fa-plus-circle"></i> Nueva Cotización</a></li>
            <li><a href="<?= $basePath ?>?module=cotizaciones&action=consultar"><i class="fas fa-search"></i> Consultar</a></li>
            <li><a href="<?= $basePath ?>?module=ordenes&action=consultar"><i class="fas fa-cart-arrow-down"></i> Órdenes de Compra</a></li>
            <?php if ($rol === 'admin'): ?>
            <li><a href="<?= $basePath ?>?module=estadisticas"><i class="fas fa-chart-bar"></i> Estadísticas y Reportes</a></li>
            <?php endif; ?>
        `
    };

    document.querySelectorAll('.menu-desplegable').forEach(item => {
        const tipo = item.dataset.panel;
        item.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            submenu.innerHTML = menus[tipo] || '';
            panel.classList.add('visible');
        });
        item.addEventListener('mouseleave', () => {
            timeout = setTimeout(() => panel.classList.remove('visible'), 300);
        });
    });

    panel.addEventListener('mouseenter', () => clearTimeout(timeout));
    panel.addEventListener('mouseleave', () => panel.classList.remove('visible'));
})();
</script>

<?php if (!empty($_SESSION['mostrar_modal_cambio_pass'])): ?>
<!-- Modal Cambio de Contraseña (Sugerido al ingresar con documento) -->
<div id="modal-force-pass" class="imo-modal-bg" style="display:flex; z-index:999999; background:rgba(15,23,42,0.75); backdrop-filter:blur(5px); position:fixed; inset:0; align-items:center; justify-content:center;">
    <div class="imo-modal" style="max-width:440px; width:90%; background:#ffffff; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); overflow:hidden; border:1px solid #cbd5e1; animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); position:relative;">
        
        <button type="button" onclick="omitirCambioModal()" style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.2); border:none; color:#ffffff; font-size:20px; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">&times;</button>

        <div style="background:linear-gradient(135deg, #10757e 0%, #0d5c63 100%); padding:24px 28px; color:#ffffff; text-align:center;">
            <div style="width:52px; height:52px; background:rgba(255,255,255,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:22px; backdrop-filter:blur(4px);">
                <i class="bi bi-key-fill"></i>
            </div>
            <h2 style="margin:0; font-size:19px; font-weight:700; color:#ffffff;">Actualizar Contraseña Inicial</h2>
            <p style="margin:6px 0 0; font-size:13px; color:rgba(255,255,255,0.9); line-height:1.4;">Se detectó que ingresó con su número de documento. Le sugerimos crear una contraseña personalizada.</p>
        </div>
        
        <form id="form-force-pass" action="<?= $basePath ?>?module=usuarios&action=cambiar_password_modal" method="POST" style="padding:20px 24px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generar_token_csrf()) ?>">
            
            <div id="force-pass-alert" style="display:none; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:14px; background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;"></div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Nueva Contraseña</label>
                <div style="position:relative;">
                    <input type="password" id="force_pass1" name="nueva_password" required minlength="6" placeholder="Mínimo 6 caracteres" style="width:100%; padding:9px 38px 9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; outline:none; transition:all 0.2s;" onfocus="this.style.borderColor='#10757e'">
                    <i class="bi bi-eye-slash" id="toggle_force_1" onclick="toggleForcePass('force_pass1', 'toggle_force_1')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#64748b; font-size:16px;"></i>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Confirmar Nueva Contraseña</label>
                <div style="position:relative;">
                    <input type="password" id="force_pass2" name="confirmar_password" required minlength="6" placeholder="Repita la contraseña" style="width:100%; padding:9px 38px 9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; outline:none; transition:all 0.2s;" onfocus="this.style.borderColor='#10757e'">
                    <i class="bi bi-eye-slash" id="toggle_force_2" onclick="toggleForcePass('force_pass2', 'toggle_force_2')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:#64748b; font-size:16px;"></i>
                </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center;">
                <button type="button" onclick="omitirCambioModal()" style="flex:1; padding:10px; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-align:center;">
                    Omitir por ahora
                </button>
                <button type="submit" id="btn-submit-force-pass" style="flex:1.4; padding:10px; background:#10757e; color:#ffffff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <i class="bi bi-check-circle-fill"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalPop {
    0% { opacity: 0; transform: scale(0.92); }
    100% { opacity: 1; transform: scale(1); }
}
</style>

<script>
function toggleForcePass(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const ico = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye-slash';
    }
}

function omitirCambioModal() {
    const m = document.getElementById('modal-force-pass');
    if (m) m.style.display = 'none';
    fetch('<?= $basePath ?>?module=usuarios&action=omitir_cambio_password', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('modal-force-pass')) {
        omitirCambioModal();
    }
});

document.getElementById('form-force-pass')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const p1 = document.getElementById('force_pass1').value;
    const p2 = document.getElementById('force_pass2').value;
    const alertDiv = document.getElementById('force-pass-alert');
    const btn = document.getElementById('btn-submit-force-pass');

    if (p1.length < 6) {
        alertDiv.style.display = 'block';
        alertDiv.textContent = 'La contraseña debe tener al menos 6 caracteres.';
        return;
    }
    if (p1 !== p2) {
        alertDiv.style.display = 'block';
        alertDiv.textContent = 'Las contraseñas no coinciden.';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Guardando...';

    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': formData.get('csrf_token')
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            window.location.reload();
        } else {
            alertDiv.style.display = 'block';
            alertDiv.textContent = data.message || 'Error al actualizar contraseña.';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Guardar';
        }
    })
    .catch(() => {
        alertDiv.style.display = 'block';
        alertDiv.textContent = 'Error de conexión. Intente nuevamente.';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Guardar';
    });
});
</script>
<?php endif; ?>
