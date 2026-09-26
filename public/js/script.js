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

    // ── VALIDACIÓN PREVENTIVA DE TAMAÑO DE ARCHIVOS (MÁXIMO 8 MB) ──
    const MAX_FILE_SIZE_BYTES = 8 * 1024 * 1024; // 8 MB

    function mostrarErrorArchivo(input, titulo, descripcion, sugerencia) {
        // Buscar o crear el contenedor de error junto al input
        let errorEl = input.parentElement.querySelector('.imo-file-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'imo-file-error';
            errorEl.style.cssText = 'margin-top:8px; padding:10px 14px; background:#fef2f2; border:1.5px solid #fca5a5; border-radius:8px; font-size:0.88rem; color:#7f1d1d;';
            input.parentElement.appendChild(errorEl);
        }
        errorEl.innerHTML =
            '<div style="display:flex; gap:10px; align-items:flex-start;">' +
            '  <i class="bi bi-exclamation-octagon-fill" style="color:#dc2626; font-size:1.2rem; flex-shrink:0; margin-top:1px;"></i>' +
            '  <div>' +
            '    <strong style="display:block; margin-bottom:3px;">' + titulo + '</strong>' +
            '    <span style="display:block; margin-bottom:6px;">' + descripcion + '</span>' +
            '    <span style="display:block; font-size:0.82rem; padding:4px 8px; background:#fef3c7; border-radius:5px; color:#92400e;">' +
            '      <i class="bi bi-lightbulb-fill" style="color:#d97706;"></i> <strong>Sugerencia:</strong> ' + sugerencia +
            '    </span>' +
            '  </div>' +
            '</div>';
        errorEl.style.display = 'block';
    }

    function limpiarErrorArchivo(input) {
        const errorEl = input.parentElement.querySelector('.imo-file-error');
        if (errorEl) errorEl.style.display = 'none';
    }

    function validarTamanoArchivo(input) {
        if (!input.files || !input.files[0]) return;
        const archivo = input.files[0];
        const pesoMB  = (archivo.size / (1024 * 1024)).toFixed(1);
        const nombre  = archivo.name;
        const ext     = nombre.split('.').pop().toLowerCase();
        const extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!extPermitidas.includes(ext)) {
            limpiarErrorArchivo(input);
            input.value = '';
            mostrarErrorArchivo(
                input,
                'Formato de imagen no compatible',
                'El archivo <strong>"' + nombre + '"</strong> no es una imagen válida. Solo se permiten archivos JPG, PNG o WebP.',
                'Si tienes un PDF o Word, toma una captura de pantalla y súbela como imagen JPG o PNG.'
            );
            return;
        }

        if (archivo.size > MAX_FILE_SIZE_BYTES) {
            limpiarErrorArchivo(input);
            input.value = '';

            // Limpiar preview asociado si existe
            const previewFoto = document.getElementById('previewFoto');
            if (previewFoto && !document.getElementById('hdnFotoActual')?.value) {
                previewFoto.innerHTML = '';
            }

            mostrarErrorArchivo(
                input,
                'Imagen demasiado pesada (' + pesoMB + ' MB)',
                'El archivo <strong>"' + nombre + '"</strong> pesa ' + pesoMB + ' MB y supera el límite máximo de <strong>8 MB</strong>.',
                'Puedes reducir el tamaño desde tu celular en <em>Editar foto → Recortar</em>, o comprimir la imagen en <a href="https://squoosh.app" target="_blank" rel="noopener">squoosh.app</a> antes de adjuntarla.'
            );
            return;
        }

        // Imagen válida: limpiar error previo
        limpiarErrorArchivo(input);
    }

    document.addEventListener('change', function (e) {
        if (e.target && e.target.type === 'file' && (e.target.accept || '').includes('image')) {
            validarTamanoArchivo(e.target);
        }
    });

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