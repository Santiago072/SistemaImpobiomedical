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
            <div class="logo-simple-wrap menu-logo-box">
                <div class="ecg-container-menu ecg-menu-box">
                    <svg viewBox="0 0 500 100" preserveAspectRatio="none" class="ecg-svg-menu">
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
<div id="modal-force-pass" class="modal-force-pass-bg">
    <div class="modal-force-pass-card">
        
        <button type="button" onclick="omitirCambioModal()" class="btn-modal-close-round">&times;</button>

        <div class="modal-force-pass-header">
            <div class="modal-force-pass-icon">
                <i class="bi bi-key-fill"></i>
            </div>
            <h2 class="modal-force-pass-title">Actualizar Contraseña Inicial</h2>
            <p class="modal-force-pass-desc">Se detectó que ingresó con su número de documento. Le sugerimos crear una contraseña personalizada.</p>
        </div>
        
        <form id="form-force-pass" action="<?= $basePath ?>?module=usuarios&action=cambiar_password_modal" method="POST" class="modal-force-pass-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generar_token_csrf()) ?>">
            
            <div id="force-pass-alert" class="force-pass-alert-box form-hidden-action"></div>

            <div class="mb-14">
                <label class="force-pass-label">Nueva Contraseña</label>
                <div class="password-input-wrap">
                    <input type="password" id="force_pass1" name="nueva_password" required minlength="6" placeholder="Mínimo 6 caracteres" class="force-pass-input">
                    <i class="bi bi-eye-slash force-pass-toggle" id="toggle_force_1" onclick="toggleForcePass('force_pass1', 'toggle_force_1')"></i>
                </div>
            </div>

            <div class="mb-18">
                <label class="force-pass-label">Confirmar Nueva Contraseña</label>
                <div class="password-input-wrap">
                    <input type="password" id="force_pass2" name="confirmar_password" required minlength="6" placeholder="Repita la contraseña" class="force-pass-input">
                    <i class="bi bi-eye-slash force-pass-toggle" id="toggle_force_2" onclick="toggleForcePass('force_pass2', 'toggle_force_2')"></i>
                </div>
            </div>

            <div class="header-actions-wrap align-center">
                <button type="button" onclick="omitirCambioModal()" class="btn-force-pass-skip">
                    Omitir por ahora
                </button>
                <button type="submit" id="btn-submit-force-pass" class="btn-force-pass-submit">
                    <i class="bi bi-check-circle-fill"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleForcePass(inputId, iconId) {
    const inp = document.getElementById(inputId);
    const ico = document.getElementById(iconId);
    if (!inp || !ico) return;
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'bi bi-eye force-pass-toggle';
    } else {
        inp.type = 'password';
        ico.className = 'bi bi-eye-slash force-pass-toggle';
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
