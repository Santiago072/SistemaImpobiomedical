/* ── SCRIPT PARA LOGIN FULL-SCREEN ── */

/* EYE TOGGLE PARA CONTRASEÑA */
const eyeBtn = document.getElementById('eyeBtn');
if (eyeBtn) {
    const passIn = document.getElementById('contrasena');
    const eyeIcon = document.getElementById('eyeIcon');
    eyeBtn.addEventListener('click', () => {
        const isPassword = passIn.type === 'password';
        passIn.type = isPassword ? 'text' : 'password';
        eyeIcon.className = isPassword ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
}

/* BUTTON LOADING EFFECT */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
        const btn = document.getElementById('submitBtn');
        if (btn && !btn.classList.contains('loading')) {
            btn.classList.add('loading');
            const spanText = document.getElementById('btnText');
            if(spanText) spanText.style.display = 'none';
            btn.innerHTML = 'Verificando...';
            btn.style.opacity = '0.8';
            btn.style.pointerEvents = 'none';
        }
    });
}

/* ── MENÚ LATERAL (Para el Panel) ── */
const btnMenu = document.getElementById('btnMenu');
if(btnMenu) {
    btnMenu.addEventListener('click', function () {
        const menuLateral = document.querySelector('.menu-lateral');
        const contenidoPrincipal = document.querySelector('.contenido-principal');
        const cabeceraSuperior = document.querySelector('.cabecera-superior');
        const layoutMain = document.querySelector('.layout-main');

        if (menuLateral) menuLateral.classList.toggle('oculto');
        if (contenidoPrincipal) contenidoPrincipal.classList.toggle('completo');
        if (cabeceraSuperior) cabeceraSuperior.classList.toggle('completo');
        if (layoutMain) layoutMain.classList.toggle('menu-oculto');
        document.body.classList.toggle('menu-oculto');

        if (menuLateral && menuLateral.classList.contains('oculto')) {
            this.innerHTML = '<i class="fa-solid fa-bars"></i> Mostrar Menú';
        } else {
            this.innerHTML = '<i class="fa-solid fa-bars"></i> Ocultar Menú';
        }
    });
}

/* ── PROTECCIÓN ANTI-DOBLE ENVÍO GLOBAL EN FORMULARIOS POST ── */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(function (form) {
        if (form.id === 'loginForm') return;

        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            if (!form.checkValidity()) return;

            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                // Si el botón es un icono compacto de eliminar en tabla/tarjeta, no cambiarle el texto para evitar que se desborde
                const esBotonIcono = submitBtn.classList.contains('mod-btn-del') || form.classList.contains('form-inline-action');

                setTimeout(function () {
                    if (e.defaultPrevented) return;
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.7';
                    submitBtn.style.cursor = 'not-allowed';
                    if (submitBtn.tagName === 'BUTTON' && !esBotonIcono) {
                        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
                    }
                }, 20);
            }
        });
    });
});