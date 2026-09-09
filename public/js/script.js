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

/* ── MENÚ LATERAL (Estructura HDMI) ── */
function toggleSidebarMenu() {
    const sidebar = document.getElementById('menuLateral');
    const backdrop = document.getElementById('sidebar-backdrop');
    const contenido = document.querySelector('.contenido-principal');
    const cabecera = document.querySelector('.cabecera-superior');
    const isMobile = window.innerWidth <= 1024;

    if (isMobile) {
        if (sidebar) sidebar.classList.toggle('is-open');
        if (backdrop) backdrop.classList.toggle('is-hidden');
    } else {
        if (sidebar) sidebar.classList.toggle('oculto');
        if (contenido) contenido.classList.toggle('completo');
        if (cabecera) cabecera.classList.toggle('completo');
        document.body.classList.toggle('menu-oculto');
    }
}

const btnMenu = document.getElementById('btnMenu');
if (btnMenu) {
    btnMenu.addEventListener('click', toggleSidebarMenu);
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
                        if (!submitBtn.dataset.originalHtml) {
                            submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                        }
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
                    }
                }, 20);
            }
        });
    });

    // Restaurar botones de envío si el usuario regresa con el botón Atrás del navegador (Bfcache)
    function restaurarBotonesEnvio() {
        document.querySelectorAll('form button[type="submit"], form input[type="submit"]').forEach(function (btn) {
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
                delete btn.dataset.originalHtml;
            }
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
        });
    }

    window.addEventListener('pageshow', function (event) {
        restaurarBotonesEnvio();
    });

    // ── RESALTAR ASTERISCOS (*) OBLIGATORIOS EN ROJO GLOBALMENTE ──
    function resaltarAsteriscosObligatorios(contexto) {
        (contexto || document).querySelectorAll('label, .oc-label, .cot-date-label').forEach(function (lbl) {
            if (lbl.querySelector('.required-star')) return;
            if (lbl.innerHTML.includes('*')) {
                lbl.innerHTML = lbl.innerHTML.replace(/\s*\*/g, ' <span class="required-star">*</span>');
            }
        });
    }

    resaltarAsteriscosObligatorios(document);

    // Observar inserciones dinámicas de modales o contenido para aplicar el estilo
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) {
                    resaltarAsteriscosObligatorios(node);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
});