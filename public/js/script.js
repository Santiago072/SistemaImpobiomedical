/* ── MENÚ LATERAL ── */
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

/* ── PARTÍCULAS CONECTADAS ── */
const canvas = document.getElementById('particle-canvas');
const ctx = canvas.getContext('2d');
let particles = [];
let mouse = { x: null, y: null, radius: 130 };

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
resizeCanvas();
window.addEventListener('resize', () => { resizeCanvas(); initParticles(); });

const PALETA_NOCHE = [
    { r: 234, g: 168, b: 57, a: 0.95 },
    { r: 255, g: 190, b: 70, a: 0.90 },
    { r: 212, g: 140, b: 50, a: 0.85 },
    { r: 255, g: 215, b: 120, a: 0.80 },
    { r: 200, g: 120, b: 40, a: 0.75 },
    { r: 255, g: 200, b: 80, a: 0.70 },
];

/* const PALETA_DIA = [
    { r: 139, g: 74, b: 14, a: 0.50 },
    { r: 160, g: 82, b: 13, a: 0.45 },
    { r: 200, g: 132, b: 62, a: 0.40 },
    { r: 180, g: 100, b: 35, a: 0.38 },
    { r: 220, g: 160, b: 90, a: 0.35 },
]; */
const PALETA_DIA = [
    { r: 139, g: 74, b: 14, a: 0.85 },  // Aumentado de 0.50
    { r: 160, g: 82, b: 13, a: 0.80 },  // Aumentado de 0.45
    { r: 200, g: 132, b: 62, a: 0.75 }, // Aumentado de 0.40
    { r: 180, g: 100, b: 35, a: 0.72 }, // Aumentado de 0.38
    { r: 220, g: 160, b: 90, a: 0.68 }, // Aumentado de 0.35
];

function getPaleta() {
    return document.body.classList.contains('modo-dia') ? PALETA_DIA : PALETA_NOCHE;
}

class Particle {
    constructor() { this.reset(true); }

    reset(aleatorio = false) {
        this.x = Math.random() * canvas.width;
        this.y = aleatorio ? Math.random() * canvas.height : canvas.height + 5;
        this.size = Math.random() * 2.2 + 0.4;
        this.baseSize = this.size;
        this.speedX = (Math.random() - 0.5) * 0.55;
        this.speedY = (Math.random() - 0.5) * 0.55;
        this.color = getPaleta()[Math.floor(Math.random() * getPaleta().length)];
        this.fase = Math.random() * Math.PI * 2;
        this.velPulso = 0.03 + Math.random() * 0.04;
        this.estrella = Math.random() > 0.82;
        this.rotEstrella = Math.random() * Math.PI;
        this.velRot = (Math.random() - 0.5) * 0.02;
    }

    update() {
        this.x += this.speedX;
        this.y += this.speedY;
        this.fase += this.velPulso;
        this.rotEstrella += this.velRot;
        this.size = this.baseSize + 0.4 * Math.sin(this.fase);

        if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;

        if (mouse.x != null) {
            const dx = mouse.x - this.x, dy = mouse.y - this.y;
            const d = Math.sqrt(dx * dx + dy * dy);
            if (d < mouse.radius) {
                const f = (mouse.radius - d) / mouse.radius;
                this.x -= (dx / d) * f * 2.8;
                this.y -= (dy / d) * f * 2.8;
            }
        }
    }

    draw() {
        const { r, g, b, a } = this.color;
        if (this.estrella) {
            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(this.rotEstrella);
            const s = this.size * 2.8;
            const halo = ctx.createRadialGradient(0, 0, 0, 0, 0, s * 2);
            halo.addColorStop(0, `rgba(${r},${g},${b},${(a * 0.75).toFixed(2)})`);
            halo.addColorStop(1, `rgba(${r},${g},${b},0)`);
            ctx.fillStyle = halo;
            ctx.beginPath(); ctx.arc(0, 0, s * 2, 0, Math.PI * 2); ctx.fill();
            ctx.strokeStyle = `rgba(${r},${g},${b},${a})`;
            ctx.lineWidth = 0.9;
            ctx.beginPath(); ctx.moveTo(-s, 0); ctx.lineTo(s, 0); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(0, -s); ctx.lineTo(0, s); ctx.stroke();
            ctx.fillStyle = `rgba(${r},${g},${b},${Math.min(a * 1.4, 1)})`;
            ctx.beginPath(); ctx.arc(0, 0, this.size * 0.7, 0, Math.PI * 2); ctx.fill();
            ctx.restore();
        } else {
            const halo = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size * 3);
            halo.addColorStop(0, `rgba(${r},${g},${b},${(a * 0.55).toFixed(2)})`);
            halo.addColorStop(1, `rgba(${r},${g},${b},0)`);
            ctx.fillStyle = halo;
            ctx.beginPath(); ctx.arc(this.x, this.y, this.size * 3, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = `rgba(${r},${g},${b},${a})`;
            ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
        }
    }
}

function initParticles() {
    particles = [];
    const cantidad = Math.floor((canvas.width * canvas.height) / 14000);
    for (let i = 0; i < Math.max(cantidad, 85); i++) {
        particles.push(new Particle());
    }
}
initParticles();

let t = 0;
function animateParticles() {
    t += 0.012;
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const isDia = document.body.classList.contains('modo-dia');
    const opacBase = isDia ? 0.50 : 0.60;

    for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const d = Math.sqrt(dx * dx + dy * dy);
            if (d < 145) {
                const opac = opacBase * (1 - d / 145);
                const mezcla = Math.sin(t + i * 0.3) * 0.5 + 0.5;
                const lr = isDia ? Math.round(139 + mezcla * 20) : Math.round(196 + mezcla * 16);
                const lg = isDia ? Math.round(74 + mezcla * 20) : Math.round(131 + mezcla * 37);
                const lb = isDia ? Math.round(14 + mezcla * 10) : Math.round(74 - mezcla * 20);
                ctx.beginPath();
                ctx.strokeStyle = `rgba(${lr},${lg},${lb},${opac.toFixed(3)})`;
                ctx.lineWidth = 0.7 + (1 - d / 145) * 0.5;
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.stroke();
            }
        }
    }

    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animateParticles);
}
animateParticles();

window.addEventListener('mousemove', e => { mouse.x = e.x; mouse.y = e.y; });
window.addEventListener('mouseout', () => { mouse.x = null; mouse.y = null; });

/* ── CONTADORES ANIMADOS ── */
const counters = document.querySelectorAll('.numero-principal');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = parseInt(entry.target.getAttribute('data-target'));
            const duration = 2000;
            const start = performance.now();
            const update = (currentTime) => {
                const elapsed = currentTime - start;
                const progress = Math.min(elapsed / duration, 1);
                const ease = 1 - Math.pow(1 - progress, 3);
                entry.target.textContent = Math.floor(ease * target);
                if (progress < 1) requestAnimationFrame(update);
                else entry.target.textContent = target;
            };
            requestAnimationFrame(update);
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });
counters.forEach(c => observer.observe(c));

/* ── MODO DÍA / NOCHE ── */
const CLAVE_TEMA = 'sodicol_tema';

function aplicarModo(modo) {
    document.body.classList.toggle('modo-dia', modo === 'dia');
    localStorage.setItem(CLAVE_TEMA, modo);
    // Actualizar colores de partículas al cambiar modo
    particles.forEach(p => {
        p.color = getPaleta()[Math.floor(Math.random() * getPaleta().length)];
    });
}

// Cargar preferencia guardada al iniciar
aplicarModo(localStorage.getItem(CLAVE_TEMA) || 'noche');

document.getElementById('btnModo').addEventListener('click', function () {
    const esDia = document.body.classList.contains('modo-dia');
    aplicarModo(esDia ? 'noche' : 'dia');
});



/* BUTTON LOADING */
const btn = document.getElementById('submitBtn');
const loginForm = document.getElementById('loginForm');
if (loginForm && btn) {
    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.add('active');
        btn.classList.add('loading');
        const icon = document.getElementById('btnIcon');
        const text = document.getElementById('btnText');
        if (icon) {
            icon.className = "bi bi-arrow-repeat";
            icon.style.animation = "spinIcon 1s linear infinite";
        }
        if (text) text.textContent = "Verificando...";
        setTimeout(() => { this.submit(); }, 2000);
    });
}

 /* EYE TOGGLE */
const eyeBtn = document.getElementById('eyeBtn');
if (eyeBtn) {
    const passIn = document.getElementById('contrasena');
    const eyeIcon = document.getElementById('eyeIcon');
    eyeBtn.addEventListener('click', () => {
        const show = passIn.type === 'password';
        passIn.type = show ? 'text' : 'password';
        eyeIcon.className = show ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
}

/* ── FILTRADO AUTOMÁTICO EN BUSCADORES ── 
   Se eliminó este bloque porque forzaba un form.submit() nativo que recargaba la página,
   interfiriendo con la búsqueda asíncrona real manejada en ajax_tables.js.
*/