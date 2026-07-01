<?php
/**
 * Vista: Login — Sistema Impobiomedical
 * Variables: $mensajeError (string), $csrf_token (string)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Impobiomedical</meta>
    <meta name="description" content="Sistema de cotizaciones Impobiomedical — acceso seguro">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/' ?>css/estilos.css">
    <style>
        #btnModo { position: fixed; top: 18px; right: 22px; z-index: 1000; padding: 10px 14px; }
        #btnModo .modo-label { display: none; }
    </style>
</head>
<body class="login-page">

<canvas id="particle-canvas"></canvas>
<div class="noise-overlay"></div>

<div class="loading-overlay" id="loadingOverlay">
    <div class="loader"></div>
    <div class="loading-text">VERIFICANDO...</div>
</div>

<button class="btn-modo" id="btnModo" title="Cambiar tema">
    <span class="modo-icon-dia"><i class="bi bi-sun-fill"></i></span>
    <span class="modo-icon-noche"><i class="bi bi-moon-stars-fill"></i></span>
    <span class="modo-label"></span>
</button>

<div class="card-wrapper" id="card3d">

    <!-- Panel izquierdo informativo -->
    <div class="panel-left">
        <div class="acc-ring acc-ring-1"></div>
        <div class="acc-ring acc-ring-2"></div>
        <div class="panel-brand">
            <p class="brand-eyebrow"><i class="bi bi-hospital-fill"></i> Sistema de Gestión</p>
            <h1>Soluciones<span>Biomédicas</span>de Calidad</h1>
            <div class="services">
                <div class="svc">
                    <div class="svc-icon"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                    <div class="svc-text"><strong>Cotizaciones Digitales</strong>Generación y descarga en PDF</div>
                </div>
                <div class="svc">
                    <div class="svc-icon"><i class="bi bi-boxes"></i></div>
                    <div class="svc-text"><strong>Catálogo de Productos</strong>Equipos y servicios biomédicos</div>
                </div>
                <div class="svc">
                    <div class="svc-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="svc-text"><strong>Gestión de Clientes</strong>Entidades y contactos centralizados</div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div class="footer-divider"></div>
            <p>© <?= date('Y') ?> Impobiomedical · Todos los derechos reservados</p>
        </div>
    </div>

    <!-- Panel derecho: formulario -->
    <div class="panel-right">
        <div class="logo-wrap">
            <div class="logo-ring-wrap">
                <div class="logo-ring"></div>
                <div class="logo-ring"></div>
                <div class="logo-ring"></div>
                <div class="logo-halo"></div>
                <div class="logo-halo"></div>
                <div class="logo-halo"></div>
                <div class="logo-dash"></div>
                <div class="logo-circle">
                    <img src="<?= defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/' ?>logo/logo.png" alt="Logo Impobiomedical">
                </div>
            </div>
            <h2 class="login-title">Iniciar Sesión</h2>
            <p class="login-sub">Ingresa tus credenciales para continuar</p>
        </div>

        <?php if (!empty($mensajeError)): ?>
        <div class="error-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($mensajeError) ?>
        </div>
        <?php endif; ?>

        <div class="form-body">
            <form action="<?= defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/' ?>index.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="fgroup">
                    <label for="correo"><i class="bi bi-envelope-fill"></i> Correo</label>
                    <div class="iw">
                        <i class="bi bi-envelope bi-left"></i>
                        <input type="email" id="correo" name="correo" placeholder="correo@impobiomedical.com"
                               required autocomplete="email" maxlength="100">
                    </div>
                </div>

                <div class="fgroup">
                    <label for="contrasena"><i class="bi bi-shield-lock-fill"></i> Contraseña</label>
                    <div class="iw">
                        <i class="bi bi-lock bi-left"></i>
                        <input type="password" id="contrasena" name="contrasena" placeholder="••••••••"
                               required autocomplete="current-password" maxlength="50">
                        <button type="button" class="eye-btn" id="eyeBtn">
                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right" id="btnIcon"></i>
                    <span id="btnText">Ingresar al Sistema</span>
                    <div class="btn-particles"></div>
                </button>
            </form>
        </div>
    </div>

</div>

<script src="<?= defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/' ?>public/js/script.js"></script>
</body>
</html>
