<?php
/**
 * Menú lateral — Layout estilo HDMI con identidad visual y paleta corporativa Impobiomedical.
 * Requiere sesión activa con $_SESSION['rol'] y $_SESSION['usuario_nombre'].
 */
if (!isset($_SESSION['usuario_nombre'])) {
    $base = defined('BASE_URL') ? BASE_URL : '/';
    header('Location: ' . $base);
    exit();
}
$rol          = $_SESSION['rol'] ?? 'usuario';
$basePath     = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
$currentMod   = $_GET['module'] ?? 'panel';
$currentAct   = $_GET['action'] ?? '';
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuarioCodigo = $_SESSION['usuario_codigo'] ?? '';
?>

<!-- Backdrop móvil para menú -->
<div id="sidebar-backdrop" class="sidebar-backdrop is-hidden" onclick="toggleSidebarMenu()"></div>

<nav class="menu-principal" id="mainSidebarNav">
    <div class="menu-lateral" id="menuLateral">
        
        <!-- Encabezado del Sidebar: Solo Animación Cardio Viva Ampliada -->
        <div class="sidebar-header-corp">
            <a href="<?= $basePath ?>?module=panel" class="sidebar-brand-link" title="Impobiomedical &bull; Panel Principal">
                <div class="sidebar-cardio-badge">
                    <svg class="sidebar-ecg-svg" viewBox="0 0 170 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <!-- Gradiente brillante del pulso ECG -->
                            <linearGradient id="cardioGlowGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#0284c7" stop-opacity="0.2" />
                                <stop offset="40%" stop-color="#0284c7" />
                                <stop offset="75%" stop-color="#22d3ee" />
                                <stop offset="100%" stop-color="#38bdf8" />
                            </linearGradient>
                            <!-- Resplandor neón médico -->
                            <filter id="ecgNeonGlow" x="-20%" y="-40%" width="140%" height="180%">
                                <feGaussianBlur stdDeviation="1.8" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>

                        <!-- Mini cuadrícula tenue médica de fondo (ECG paper grid) -->
                        <g class="ecg-grid-lines" opacity="0.18">
                            <line x1="0" y1="12" x2="170" y2="12" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                            <line x1="0" y1="25" x2="170" y2="25" stroke="#0ea5e9" stroke-width="0.8" />
                            <line x1="0" y1="38" x2="170" y2="38" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                            <line x1="30" y1="0" x2="30" y2="50" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                            <line x1="65" y1="0" x2="65" y2="50" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                            <line x1="100" y1="0" x2="100" y2="50" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                            <line x1="135" y1="0" x2="135" y2="50" stroke="#0ea5e9" stroke-width="0.7" stroke-dasharray="2 3" />
                        </g>

                        <!-- Onda base completa (trazado anatómico ECG P-Q-R-S-T perfecto) -->
                        <path class="sidebar-ecg-bg-line"
                              d="M 4 25 L 24 25 Q 28 25 30 22 Q 32 25 36 25 L 44 25 L 48 29 L 54 7 L 60 41 L 64 22 L 68 25 L 76 25 Q 82 20 88 25 L 102 25 Q 106 25 108 22 Q 110 25 114 25 L 122 25 L 126 29 L 132 7 L 138 41 L 142 22 L 146 25 L 154 25 Q 160 20 166 25" />

                        <!-- Onda viva iluminada con flujo de pulso continuo -->
                        <path class="sidebar-ecg-stream" filter="url(#ecgNeonGlow)"
                              d="M 4 25 L 24 25 Q 28 25 30 22 Q 32 25 36 25 L 44 25 L 48 29 L 54 7 L 60 41 L 64 22 L 68 25 L 76 25 Q 82 20 88 25 L 102 25 Q 106 25 108 22 Q 110 25 114 25 L 122 25 L 126 29 L 132 7 L 138 41 L 142 22 L 146 25 L 154 25 Q 160 20 166 25" />

                        <!-- Punto de pulso cardiológico que recorre la cresta -->
                        <circle class="sidebar-ecg-heart-ping" r="3.2" fill="#38bdf8" filter="url(#ecgNeonGlow)">
                            <animateMotion 
                                path="M 4 25 L 24 25 Q 28 25 30 22 Q 32 25 36 25 L 44 25 L 48 29 L 54 7 L 60 41 L 64 22 L 68 25 L 76 25 Q 82 20 88 25 L 102 25 Q 106 25 108 22 Q 110 25 114 25 L 122 25 L 126 29 L 132 7 L 138 41 L 142 22 L 146 25 L 154 25 Q 160 20 166 25"
                                dur="2.4s"
                                repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>
            </a>
            <button type="button" class="btn-close-sidebar-mobile" onclick="toggleSidebarMenu()" aria-label="Cerrar Menú">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Lista de Navegación Principal con Íconos y Etiquetas -->
        <div class="sidebar-nav-scroll">
            <div class="sidebar-nav-label">NAVEGACIÓN PRINCIPAL</div>
            <ul class="lista-menu-lateral">
                <li>
                    <a href="<?= $basePath ?>?module=panel" class="<?= $currentMod === 'panel' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-speedometer2"></i></span>
                            <span class="sidebar-link-text">Dashboard</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=cotizaciones&action=crear" class="<?= ($currentMod === 'cotizaciones' && $currentAct === 'crear') ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-plus-circle-fill"></i></span>
                            <span class="sidebar-link-text">Nueva Cotización</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=cotizaciones&action=consultar" class="<?= ($currentMod === 'cotizaciones' && $currentAct === 'consultar') ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-search"></i></span>
                            <span class="sidebar-link-text">Consultar Cotizaciones</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=ordenes&action=consultar" class="<?= $currentMod === 'ordenes' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-cart-check-fill"></i></span>
                            <span class="sidebar-link-text">Órdenes de Compra</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=clientes" class="<?= $currentMod === 'clientes' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-building"></i></span>
                            <span class="sidebar-link-text">Directorio Clientes</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=proveedores" class="<?= $currentMod === 'proveedores' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-truck"></i></span>
                            <span class="sidebar-link-text">Proveedores</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=productos" class="<?= $currentMod === 'productos' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-box-seam-fill"></i></span>
                            <span class="sidebar-link-text">Catálogo Productos</span>
                        </div>
                    </a>
                </li>
                <?php if ($rol === 'admin'): ?>
                <li>
                    <a href="<?= $basePath ?>?module=usuarios" class="<?= $currentMod === 'usuarios' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
                            <span class="sidebar-link-text">Gestión Usuarios</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= $basePath ?>?module=estadisticas" class="<?= $currentMod === 'estadisticas' ? 'active' : '' ?>">
                        <div class="sidebar-link-inner">
                            <span class="sidebar-link-icon"><i class="bi bi-bar-chart-fill"></i></span>
                            <span class="sidebar-link-text">Estadísticas</span>
                        </div>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Tarjeta de Perfil de Usuario en el Pie del Sidebar -->
        <div class="sidebar-footer-card">
            <div class="sidebar-user-box">
                <div class="sidebar-user-avatar">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="sidebar-user-details">
                    <span class="sidebar-user-name" title="<?= htmlspecialchars($usuarioNombre) ?>">
                        <?= htmlspecialchars($usuarioNombre) ?>
                    </span>
                    <span class="sidebar-user-role">
                        <?= strtoupper(htmlspecialchars($rol)) ?> <?= !empty($usuarioCodigo) ? '&bull; ' . htmlspecialchars($usuarioCodigo) : '' ?>
                    </span>
                </div>
                <a href="<?= $basePath ?>?action=logout" class="sidebar-btn-logout" title="Cerrar Sesión">
                    <i class="bi bi-power"></i>
                </a>
            </div>
        </div>

    </div>
</nav>

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
