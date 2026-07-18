<?php
/**
 * Vista: Login — Sistema Impobiomedical
 * Diseño Premium v4 — Teal Palette + Glassmorphism sutil
 */
$base = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Impobiomedical</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>css/estilos.css">
</head>
<body class="login-page-body">

<div class="card">

    <!-- ═══════════════════════════════
         PANEL IZQUIERDO — BRANDING
    ═══════════════════════════════ -->
    <div class="left-panel">

        <!-- Top -->
        <div class="left-top">
            
            <div class="badge-pill">
                <div class="dot"></div>
                <span>Sistema de Gestión</span>
            </div>

            <h1 class="hero-title">
                Tecnología<br>
                <span class="accent">Biomédica</span><br>
                de confianza
            </h1>
            <p class="hero-sub">
                Administración de cotizaciones,<br>
                clientes y equipos médicos.
            </p>
        </div>

        <!-- ECG -->
        <div class="ecg-wrap">
            <svg class="ecg-svg" viewBox="0 0 500 50" preserveAspectRatio="none">
                <polyline points="0,25 90,25 115,5 140,45 165,3 190,47 215,25 500,25"/>
            </svg>
        </div>

        <!-- Bottom: Impomin -->
        <div class="left-bottom">
            <div class="partner-label">Empresa Asociada</div>
            <div class="partner-card">
                <img src="<?= $base ?>logo/logoimp.png" alt="Impomin — ONAC Acreditado ISO/IEC 17025">
            </div>
            <div class="cert-chips">
                <div class="cert-chip">
                    <i class="bi bi-patch-check-fill"></i>
                    ISO/IEC 17025
                </div>
                <div class="cert-chip">
                    <i class="bi bi-shield-fill-check"></i>
                    ONAC 23-LAC-033
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════
         PANEL DERECHO — FORMULARIO
    ═══════════════════════════════ -->
    <div class="right-panel">
        <div class="form-box">

            <!-- Logo Impobiomedical horizontal -->
            <div class="form-logo">
                <img src="<?= $base ?>logo/logo.png"
                     alt="Impobiomedical — Soluciones y Servicios de Tecnología Biomédica">
            </div>

            <!-- Separador elegante -->
            <div class="form-sep">
                <div class="line"></div>
                <span>Acceso seguro</span>
                <div class="line right"></div>
            </div>

            <!-- Título -->
            <div class="form-title">
                <h2>Bienvenido de nuevo</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Error (espacio fijo sin layout shift) -->
            <div class="error-slot">
                <?php if (!empty($mensajeError)): ?>
                <div class="error-msg">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($mensajeError) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <form action="<?= $base ?>index.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                <div class="field">
                    <label for="documento">Número de Documento</label>
                    <div class="field-wrap">
                        <input type="text" id="documento" name="documento"
                                placeholder="Ej: 1000000000"
                                required autocomplete="username">
                        <i class="bi bi-person-badge field-icon"></i>
                    </div>
                </div>

                <div class="field">
                    <label for="contrasena">Contraseña</label>
                    <div class="field-wrap">
                        <input type="password" id="contrasena" name="contrasena"
                               placeholder="••••••••"
                               required autocomplete="current-password">
                        <i class="bi bi-lock field-icon"></i>
                        <button type="button" class="eye-btn" id="eyeBtn"
                                aria-label="Mostrar contraseña">
                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span id="btnText">Ingresar al Sistema</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </button>
            </form>

            <div class="form-footer">
                <i class="bi bi-shield-lock-fill"></i>
                Conexión segura · Impobiomedical &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>

</div>

<script>
    // Toggle de contraseña
    document.getElementById('eyeBtn').addEventListener('click', function () {
        const input = document.getElementById('contrasena');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });

    // Focus: cambiar color del ícono
    document.getElementById('documento').addEventListener('focus', function () {
        this.parentNode.querySelector('.field-icon').style.color = '#10757e';
    });
    document.getElementById('documento').addEventListener('blur', function () {
        this.parentNode.querySelector('.field-icon').style.color = '#9ca3af';
    });
    document.getElementById('contrasena').addEventListener('focus', function () {
        this.parentNode.querySelector('.field-icon').style.color = '#10757e';
    });
    document.getElementById('contrasena').addEventListener('blur', function () {
        this.parentNode.querySelector('.field-icon').style.color = '#9ca3af';
    });

    // Loader al enviar
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn  = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        btn.disabled = true;
        text.textContent = 'Verificando...';
    });
</script>
</body>
</html>
