<?php
/**
 * Vista: Landing Page Institucional — IMPOMIN S.A.S / IMPOBIOMEDICAL
 * Enfoque UI/UX: 
 *  - Presentación corporativa premium de alto impacto para exhibición en portafolio profesional.
 *  - Menú superior:
 *      * Se rediseñó el bloque de marca como una insignia integrada de alta tecnología ("Cardio Flow Unit").
 *      * Muestra el símbolo biomédico y una pantalla de pulso ECG viva y continua conectada orgánicamente al nombre "IMPOBIOMEDICAL".
 *      * Animación de trazo médico dinámico con destellos láser en tiempo real (onda continua, sin recuadros toscos ni bordes aislados).
 *  - Tarjeta derecha del Hero (Hero Showcase):
 *      * Banners de exhibición de logos a tamaño completo (altura de hasta 100px y ancho hasta 380px).
 *      * Imágenes HD sin recortes ni compresiones para visualización 100% nítida.
 *      * Eliminado el número de versión (se usa badge de estado funcional "● SISTEMA ACTIVO").
 *  - Footer corporativo:
 *      * Sin imágenes borrosas.
 *      * Onda de pulso biomédico / línea de electrocardiograma dinámica con animación CSS continua ("onda viva").
 *      * Branding tipográfico de alto contraste con gradientes.
 *  - Operación real del software: Cotizaciones comerciales con margen, filtrado por proveedor y Órdenes de Compra (P.O.).
 *  - Sedes oficiales: Florencia (Caquetá) y Sabaneta / Medellín (Antioquia).
 *  - Modal seguro de autenticación (POST a ?action=login, CSRF protegido).
 */
$base = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
$mensajeError = $mensajeError ?? '';
$csrf_token   = $csrf_token ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impobiomedical &bull; IMPOMIN S.A.S — Soluciones en Equipos Médicos y Compras Institucionales</title>
    <meta name="description" content="Plataforma empresarial de IMPOMIN S.A.S e Impobiomedical. Soluciones integrales para el sector salud en Colombia: gestión de cotizaciones comerciales y emisión de órdenes de compra.">
    
    <!-- Google Fonts: Outfit + Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Hoja de Estilos Modularizada -->
    <link rel="stylesheet" href="<?= $base ?>css/estilos.css">

    <!-- Favicon Oficial -->
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>public/favicon.svg?v=<?= time() ?>">
</head>
<body>

    <!-- ══ NAVBAR FLOTANTE CON ISOTIPO BIOMÉDICO Y CARDIO VIVO ══ -->
    <header class="navbar-top" id="mainNavbar">
        <div class="nav-inner">
            <a href="<?= $base ?>" class="brand-vector-logo" title="IMPOMIN S.A.S &bull; Impobiomedical">
                <!-- Unidad Biomédica Integrada: Sensor + Arco + Onda Cardio viva con pulso continuo -->
                <div class="brand-biomed-module">
                    <svg class="brand-cardio-svg" viewBox="0 0 135 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Sensor vertical biomédico del logo oficial -->
                        <line x1="12" y1="4" x2="12" y2="11" stroke="#38bdf8" stroke-width="3.5" stroke-linecap="round" />
                        <rect x="8" y="13" width="8" height="15" rx="2" fill="#38bdf8" />
                        <line x1="12" y1="28" x2="12" y2="39" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" />
                        <!-- Arco medial / semicírculo característico -->
                        <path d="M 18 12 A 14 14 0 0 1 18 36" stroke="#38bdf8" stroke-width="3.2" stroke-linecap="round" fill="none" opacity="0.9" />
                        <!-- Onda de fondo tenue -->
                        <path class="nav-ecg-bg-line" d="M 22 24 L 38 24 L 46 14 L 54 34 L 64 5 L 75 39 L 83 14 L 90 28 L 98 24 L 132 24" />
                        <!-- Onda Cardio Viva Animada (Pulso Cyan continuo) -->
                        <path class="nav-ecg-stream" d="M 22 24 L 38 24 L 46 14 L 54 34 L 64 5 L 75 39 L 83 14 L 90 28 L 98 24 L 132 24" />
                    </svg>
                </div>

                <div class="brand-text-block">
                    <!-- Nombre IMPOBIOMEDICAL continuo sin espacios -->
                    <div class="brand-title-main">
                        <span class="txt-medical">IMPO</span><span class="txt-bio">BIO</span><span class="txt-medical">MEDICAL</span>
                    </div>
                    <span class="brand-sub-badge">
                        <span class="live-dot"></span> IMPOMIN S.A.S &bull; NIT 900.535.843-3
                    </span>
                </div>
            </a>

            <ul class="nav-menu-links">
                <li><a href="#empresa">Nuestra Empresa</a></li>
                <li><a href="#operacion">Gestión de Cotizaciones</a></li>
                <li><a href="#sedes">Sedes y Cobertura</a></li>
                <li><a href="#contacto">Contacto</a></li>
            </ul>

            <button class="btn-portal-cta" onclick="abrirAccesoPortal()">
                <i class="bi bi-shield-lock-fill"></i> Acceso al Sistema
            </button>
        </div>
    </header>

    <!-- ══ HERO SECTION: VISUAL HERO CORPORATIVO PARA PORTAFOLIO ══ -->
    <section class="hero-corp-section">
        <div class="hero-corp-container">
            <div>
                <div class="company-pill reveal">
                    <span class="pulse-indicator"></span>
                    IMPOMIN S.A.S &bull; Nit: 900.535.843-3
                </div>

                <h1 class="hero-title-corp reveal">
                    Solidez y respaldo en el suministro de <span class="highlight-text">tecnología biomédica</span>
                </h1>

                <p class="hero-lead-text reveal">
                    En <strong>Impobiomedical</strong> respaldamos la operación clínica e institucional en Colombia. Centralizamos la elaboración de <strong>cotizaciones comerciales estructuradas</strong> y la generación formal de <strong>órdenes de compra por proveedor</strong> con total rigor administrativo.
                </p>

                <div class="hero-btn-row reveal">
                    <button class="btn-portal-cta btn-portal-cta-hero" onclick="abrirAccesoPortal()">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar a la Plataforma
                    </button>
                    <a href="#operacion" class="btn-outline-corp">
                        <i class="bi bi-diagram-3-fill icon-primary"></i> Ver Flujo Operativo
                    </a>
                </div>

                <div class="hero-stats-strip reveal">
                    <div class="stat-item">
                        <span class="stat-num">100%</span>
                        <span class="stat-label">Formalidad Institucional</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">2 Sedes</span>
                        <span class="stat-label">Florencia &bull; Medellín</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-num">B2B / IPS</span>
                        <span class="stat-label">Sector Clínico y Hospitalario</span>
                    </div>
                </div>
            </div>

            <!-- TARJETA DERECHA: CONSOLA CORPORATIVA CON LOGOS EN FORMATO MÁXIMO (100% NÍTIDOS) -->
            <div class="hero-showcase-box reveal">
                <div class="dashboard-preview-card">
                    <!-- Cabecera Limpia sin número de versión -->
                    <div class="dash-card-header">
                        <div class="dash-header-title">
                            <span class="dash-status-dot"></span>
                            <div class="dash-header-text">
                                <h3>Consola Corporativa</h3>
                                <span>Gestión Comercial & Órdenes de Compra</span>
                            </div>
                        </div>
                        <span class="dash-live-badge">
                            <i class="bi bi-circle-fill icon-system-status"></i> SISTEMA ACTIVO
                        </span>
                    </div>

                    <!-- EXHIBIDOR DE LOGOS EN TAMAÑO MÁXIMO Y NATIVO -->
                    <div class="logos-hd-duo-container">
                        <!-- Banner 1: Impobiomedical en Gran Formato -->
                        <div class="logo-hd-banner">
                            <div class="logo-hd-img-wrap">
                                <img src="<?= $base ?>logo/logopdf.png" alt="Impobiomedical - Soluciones en Tecnología Biomédica" class="logo-img-pdf">
                            </div>
                            <div class="logo-tag-info">
                                <span class="brand-type">Línea Médica</span>
                                <span class="brand-detail">Equipos & Insumos</span>
                            </div>
                        </div>

                        <!-- Banner 2: IMPOMIN S.A.S en Gran Formato -->
                        <div class="logo-hd-banner banner-impomin">
                            <div class="logo-hd-img-wrap">
                                <img src="<?= $base ?>logo/logoimp.png" alt="IMPOMIN S.A.S - NIT 900.535.843-3" class="logo-img-imp">
                            </div>
                            <div class="logo-tag-info">
                                <span class="brand-type">Razón Social</span>
                                <span class="brand-detail">NIT 900.535.843-3</span>
                            </div>
                        </div>
                    </div>

                    <!-- Métricas de Operación del Sistema Real -->
                    <div class="dash-metrics-grid">
                        <div class="metric-mini-tile">
                            <i class="bi bi-file-earmark-spreadsheet-fill tile-icon"></i>
                            <span class="tile-label">Módulo</span>
                            <span class="tile-value">Cotizaciones</span>
                        </div>
                        <div class="metric-mini-tile">
                            <i class="bi bi-percent tile-icon"></i>
                            <span class="tile-label">Cálculo</span>
                            <span class="tile-value">Rentabilidad</span>
                        </div>
                        <div class="metric-mini-tile">
                            <i class="bi bi-bag-check-fill tile-icon"></i>
                            <span class="tile-label">Emisión</span>
                            <span class="tile-value">Órdenes P.O.</span>
                        </div>
                    </div>
                </div>

                <!-- Insignia Flotante de Verificación -->
                <div class="floating-badge-portal">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Sociedad Legalmente Constituida en Colombia</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECCIÓN: SOBRE LA EMPRESA & SEDES ══ -->
    <section id="empresa" class="section-wrapper">
        <div class="about-split-grid">
            <div class="about-text-content reveal">
                <span class="section-tagline">Nuestra Empresa</span>
                <h2 class="section-title-corp">Aliados estratégicos de clínicas y profesionales en Colombia</h2>
                <p>
                    <strong>IMPOMIN S.A.S</strong>, a través de su división médica <strong>Impobiomedical</strong>, consolida una propuesta de valor basada en la transparencia, la puntualidad en el suministro y la seriedad administrativa.
                </p>
                <p>
                    Acompañamos a las entidades hospitalarias, centros médicos y consultorios especializados desde la formulación y costeo de sus necesidades tecnológicas hasta el enlace directo con los distribuidores e importadores autorizados.
                </p>

                <div class="about-highlights">
                    <div class="about-card-mini">
                        <i class="bi bi-award-fill"></i>
                        <h4>Respaldo Jurídico</h4>
                        <p>Facturación formal, registros mercantiles vigentes y cumplimiento normativo.</p>
                    </div>
                    <div class="about-card-mini">
                        <i class="bi bi-arrow-repeat"></i>
                        <h4>Eficiencia Operativa</h4>
                        <p>Plataforma propia para cotizar y comprar sin demoras ni errores manuales.</p>
                    </div>
                </div>
            </div>

            <!-- Sedes de Operación y Canales Directos -->
            <div class="locations-box reveal" id="sedes">
                <div class="locations-header">
                    <h3><i class="bi bi-geo-alt-fill icon-primary"></i> Sedes de Atención</h3>
                    <span class="badge-nacional-pill">Nacional</span>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-building"></i></div>
                    <div class="loc-details">
                        <h4>Sede Florencia — Caquetá</h4>
                        <p>Cra. 10 #9-80 Barrio Cooperativa</p>
                        <p class="text-muted-xs">Atención a la región sur y Amazonía colombiana</p>
                    </div>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-building"></i></div>
                    <div class="loc-details">
                        <h4>Sede Sabaneta / Medellín — Antioquia</h4>
                        <p>Calle 61 Sur #43A-85 (Sabaneta) / Laureles (Medellín)</p>
                        <p class="text-muted-xs">Coordinación logística y enlace corporativo</p>
                    </div>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-telephone-inbound-fill"></i></div>
                    <div class="loc-details">
                        <h4>Líneas y Canales Oficiales</h4>
                        <p>Telefax: (4) 322 27 79 &bull; Móvil: 317 345 3644 / 310 269 0595</p>
                        <p class="icon-primary fw-bold fs-sm">impobiomedical@impomin.com</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECCIÓN: FLUJO REAL DE COTIZACIONES Y ÓRDENES DE COMPRA ══ -->
    <section id="operacion" class="process-section">
        <div class="process-header-center reveal">
            <span class="section-tagline">Proceso del Sistema</span>
            <h2 class="section-title-corp">Gestión comercial y compras centralizadas</h2>
            <p class="text-lead-muted">
                Estructura operativa verídica implementada en la plataforma para garantizar precisión en los costos y agilidad hacia los proveedores.
            </p>
        </div>

        <div class="process-grid-3">
            <!-- Fase 1: Cotización -->
            <div class="process-card reveal">
                <div class="process-card-top">
                    <span class="process-step-pill">Fase 1</span>
                    <div class="process-icon-wrap"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
                </div>
                <h3 class="process-title">Creación de la Cotización</h3>
                <p class="process-desc">
                    El asesor ingresa o reutiliza productos del inventario, define cantidades, costos unitarios y porcentajes de rentabilidad comercial para emitir la propuesta oficial.
                </p>
                <ul class="process-points-list">
                    <li><i class="bi bi-check-circle-fill"></i> Búsqueda ágil y autocompletado</li>
                    <li><i class="bi bi-check-circle-fill"></i> Cálculo de márgenes en tiempo real</li>
                    <li><i class="bi bi-check-circle-fill"></i> Exportación a PDF corporativo</li>
                </ul>
            </div>

            <!-- Fase 2: Filtrado por Proveedor -->
            <div class="process-card reveal">
                <div class="process-card-top">
                    <span class="process-step-pill">Fase 2</span>
                    <div class="process-icon-wrap"><i class="bi bi-funnel-fill"></i></div>
                </div>
                <h3 class="process-title">Segmentación por Proveedor</h3>
                <p class="process-desc">
                    Una vez la cotización es autorizada, el sistema permite seleccionar y agrupar específicamente los ítems suministrados por cada fabricante o distribuidor.
                </p>
                <ul class="process-points-list">
                    <li><i class="bi bi-check-circle-fill"></i> Selección selectiva de productos</li>
                    <li><i class="bi bi-check-circle-fill"></i> Cero duplicidad o cruce de ítems</li>
                    <li><i class="bi bi-check-circle-fill"></i> Validación de disponibilidad</li>
                </ul>
            </div>

            <!-- Fase 3: Orden de Compra -->
            <div class="process-card reveal">
                <div class="process-card-top">
                    <span class="process-step-pill">Fase 3</span>
                    <div class="process-icon-wrap"><i class="bi bi-bag-check-fill"></i></div>
                </div>
                <h3 class="process-title">Emisión de Orden de Compra</h3>
                <p class="process-desc">
                    Se formaliza la orden de compra (P.O.) con los datos del proveedor seleccionado, condiciones de pago, fletes y retenciones, cerrando el ciclo comercial.
                </p>
                <ul class="process-points-list">
                    <li><i class="bi bi-check-circle-fill"></i> Generación de pedido formal (P.O.)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Trazabilidad del estado del pedido</li>
                    <li><i class="bi bi-check-circle-fill"></i> Historial y auditoría de compras</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ══ BANNER DE ACCESO A PLATAFORMA ══ -->
    <section class="section-banner-wrap">
        <div class="banner-access-box reveal">
            <div>
                <h2>Plataforma Comercial de IMPOMIN S.A.S</h2>
                <p>Acceso exclusivo para el personal comercial, directores de compras y administradores del sistema Impobiomedical.</p>
            </div>
            <button class="btn-portal-cta btn-banner-access" onclick="abrirAccesoPortal()">
                <i class="bi bi-box-arrow-in-right icon-primary"></i> Iniciar Sesión en el Sistema
            </button>
        </div>
    </section>

    <!-- ══ FOOTER CORPORATIVO REDISEÑADO CON ONDA VIVA ANIMADA (SIN IMÁGENES) ══ -->
    <footer id="contacto" class="footer-corp-dark">
        <!-- Barra superior con línea de pulso biomédico animada -->
        <div class="footer-wave-box">
            <div class="footer-wave-label">
                <i class="bi bi-activity icon-pulse-blue"></i>
                <span>Monitoreo & Trazabilidad Biomédica Activa</span>
            </div>
            <div class="wave-svg-container">
                <svg viewBox="0 0 600 50" preserveAspectRatio="none" class="svg-footer-wave">
                    <!-- Línea tenue de fondo -->
                    <path class="ecg-path-bg" d="M 0 25 L 70 25 L 85 10 L 95 40 L 105 5 L 115 45 L 125 25 L 220 25 L 235 8 L 245 42 L 255 2 L 265 48 L 275 25 L 370 25 L 385 12 L 395 38 L 405 6 L 415 44 L 425 25 L 520 25 L 535 10 L 545 40 L 555 5 L 565 45 L 575 25 L 600 25" />
                    <!-- Pulso animado en color Cyan/Teal -->
                    <path class="ecg-path-pulse" d="M 0 25 L 70 25 L 85 10 L 95 40 L 105 5 L 115 45 L 125 25 L 220 25 L 235 8 L 245 42 L 255 2 L 265 48 L 275 25 L 370 25 L 385 12 L 395 38 L 405 6 L 415 44 L 425 25 L 520 25 L 535 10 L 545 40 L 555 5 L 565 45 L 575 25 L 600 25" />
                </svg>
            </div>
        </div>

        <div class="footer-corp-grid">
            <div>
                <!-- Identidad Tipográfica limpia en el footer -->
                <div class="footer-brand-title">
                    <i class="bi bi-activity"></i>
                    <span>IMPOBIOMEDICAL</span>
                </div>

                <p class="footer-text-desc">
                    Gestión comercial, estructuración de cotizaciones especializadas y enlace de suministros para el sector salud y clínico en Colombia.
                </p>
                <p class="footer-text-meta">
                    <strong>Razón Social:</strong> IMPOMIN S.A.S &bull; NIT: 900.535.843-3
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Sedes y Atención</h4>
                <p class="footer-location-row">
                    <strong class="text-white">Florencia (Caquetá):</strong><br>
                    Cra. 10 #9-80 Barrio Cooperativa
                </p>
                <p class="footer-location-row-last">
                    <strong class="text-white">Antioquia:</strong><br>
                    Calle 61 Sur #43A-85 (Sabaneta) / Laureles (Medellín)
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Canales Oficiales</h4>
                <p class="footer-channel-row">
                    <i class="bi bi-envelope-fill icon-primary-light"></i> impobiomedical@impomin.com
                </p>
                <p class="footer-channel-row-last">
                    <i class="bi bi-telephone-fill icon-primary-light"></i> (4) 322 27 79 / 317 345 3644
                </p>
                <button class="btn-portal-cta btn-portal-footer-cta" onclick="abrirAccesoPortal()">
                    <i class="bi bi-lock-fill"></i> Acceso Usuarios
                </button>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <span>&copy; <?= date('Y') ?> IMPOMIN S.A.S &bull; Impobiomedical. Todos los derechos reservados.</span>
            <span>Sistema Comercial &bull; Cotizaciones & Órdenes de Compra</span>
        </div>
    </footer>

    <!-- ══ MODAL FLOTANTE DE AUTENTICACIÓN DIRECTA (MÉTODO POST) ══ -->
    <?php $debeAbrirModal = !empty($mensajeError) || isset($_GET['timeout']) || isset($_GET['login']) || (isset($_GET['action']) && $_GET['action'] === 'login'); ?>
    <div class="modal-overlay-bg <?= $debeAbrirModal ? 'active' : '' ?>" id="modalPortalLogin">
        <div class="modal-login-dialog">
            <div class="modal-header-top">
                <button type="button" class="btn-modal-close-icon" onclick="cerrarAccesoPortal()" aria-label="Cerrar">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="modal-brand-row">
                    <div class="modal-shield-icon-wrap">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 class="modal-brand-title">Acceso al Sistema</h3>
                        <p class="modal-brand-sub">Cotizaciones & Órdenes de Compra</p>
                    </div>
                </div>
            </div>

            <div class="modal-body-content">
                <?php if (!empty($mensajeError)): ?>
                    <div class="alert-error-box">
                        <i class="bi bi-exclamation-triangle-fill icon-warn-box"></i>
                        <span><?= htmlspecialchars($mensajeError) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= $base ?>?action=login" id="formAccesoPortal">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="form-row-modern">
                        <label for="docUsuario">Documento o Cédula</label>
                        <div class="input-group-styled">
                            <input type="text" id="docUsuario" name="documento" placeholder="Ej: 1000000000" required autocomplete="username">
                            <i class="bi bi-person-badge field-icon-symbol"></i>
                        </div>
                    </div>

                    <div class="form-row-modern">
                        <label for="passUsuario">Contraseña</label>
                        <div class="input-group-styled">
                            <input type="password" id="passUsuario" name="contrasena" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" onclick="alternarVerPass()" class="field-icon-symbol btn-toggle-eye" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" id="iconoVerPass"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-login-action" id="btnAccesoSubmit">
                        <span id="txtBtnAcceso">Ingresar a la Plataforma</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ══ SCRIPTS DE INTERACCIÓN, ANIMACIÓN Y ACCESO ══ -->
    <script>
        function abrirAccesoPortal() {
            document.getElementById('modalPortalLogin').classList.add('active');
            setTimeout(() => document.getElementById('docUsuario').focus(), 150);
        }

        function cerrarAccesoPortal() {
            document.getElementById('modalPortalLogin').classList.remove('active');
        }

        document.getElementById('modalPortalLogin').addEventListener('click', function(e) {
            if (e.target === this) cerrarAccesoPortal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarAccesoPortal();
        });

        function alternarVerPass() {
            const input = document.getElementById('passUsuario');
            const icon = document.getElementById('iconoVerPass');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        document.getElementById('formAccesoPortal').addEventListener('submit', function() {
            const btn = document.getElementById('btnAccesoSubmit');
            const txt = document.getElementById('txtBtnAcceso');
            btn.disabled = true;
            txt.textContent = 'Verificando credenciales...';
        });

        window.addEventListener('scroll', function() {
            const nav = document.getElementById('mainNavbar');
            if (window.scrollY > 40) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });

        const reveals = document.querySelectorAll('.reveal');
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    obs.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        });

        reveals.forEach(el => observer.observe(el));
    </script>
</body>
</html>
