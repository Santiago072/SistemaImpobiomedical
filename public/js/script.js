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
        document.querySelector('.menu-lateral').classList.toggle('oculto');
        document.querySelector('.contenido-principal').classList.toggle('completo');
        if (document.querySelector('.menu-lateral').classList.contains('oculto')) {
            this.innerHTML = '<i class="fa-solid fa-bars"></i> Mostrar Menú';
        } else {
            this.innerHTML = '<i class="fa-solid fa-bars"></i> Ocultar Menú';
        }
    });
}