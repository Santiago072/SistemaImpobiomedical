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

    <style>
        :root {
            --primary: #10757e;
            --primary-dark: #084349;
            --primary-light: #169aa6;
            --cyan-accent: #0284c7;
            --cyan-glow: #38bdf8;
            --navy-deep: #0a111e;
            --navy-surface: #111e33;
            --navy-slate: #1e293b;
            --slate-text: #334155;
            --slate-muted: #64748b;
            --bg-canvas: #f8fafc;
            --white: #ffffff;
            --border-light: #e2e8f0;
            --glow-primary: rgba(16, 117, 126, 0.28);
            --shadow-card: 0 25px 50px -12px rgba(11, 21, 35, 0.12), 0 4px 12px rgba(0,0,0,0.04);
            --shadow-elevate: 0 30px 60px -15px rgba(10, 17, 30, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-canvas);
            color: var(--slate-text);
            line-height: 1.65;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
            color: var(--navy-deep);
            letter-spacing: -0.025em;
        }

        /* ── ANIMACIONES DE ALTO IMPACTO (KEYFRAMES) ── */
        @keyframes floatHeroCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        @keyframes pulseRadar {
            0% { transform: scale(0.9); opacity: 0.9; }
            50% { transform: scale(1.35); opacity: 0.35; }
            100% { transform: scale(1.7); opacity: 0; }
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        @keyframes shineSweep {
            0% { left: -120%; }
            50%, 100% { left: 140%; }
        }

        /* Animación fluida de la onda cardio en el Navbar */
        @keyframes cardioDashFlow {
            0% { stroke-dashoffset: 240; }
            100% { stroke-dashoffset: 0; }
        }

        @keyframes laserDotFlow {
            0% { offset-distance: 0%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { offset-distance: 100%; opacity: 0; }
        }

        @keyframes emblemGlow {
            0%, 100% { box-shadow: 0 4px 14px rgba(16, 117, 126, 0.25); }
            50% { box-shadow: 0 6px 22px rgba(56, 189, 248, 0.5); }
        }

        /* Animación de la onda biomédica del footer */
        @keyframes ecgMove {
            0% { stroke-dashoffset: 600; }
            100% { stroke-dashoffset: 0; }
        }

        @keyframes pulseGlowWave {
            0%, 100% { filter: drop-shadow(0 0 4px rgba(56, 189, 248, 0.5)); }
            50% { filter: drop-shadow(0 0 14px rgba(56, 189, 248, 0.9)); }
        }

        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── NAVBAR PRINCIPAL: IDENTIDAD BIOMÉDICA DINÁMICA & ELEGANTE ── */
        .navbar-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            transition: all 0.3s ease;
        }

        .navbar-top.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.07);
        }

        .nav-inner {
            max-width: 1340px;
            margin: 0 auto;
            padding: 10px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Enlace de Marca: Cardio Vivo Dinámico & Tipografía de Alta Gama */
        .brand-vector-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            position: relative;
            padding: 4px 0;
        }

        /* Emblema Biomédico Futurista / Consola Monitor */
        .brand-biomed-module {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 48px;
            padding: 0 16px 0 12px;
            background: linear-gradient(135deg, #042429 0%, #0b353d 50%, #0d223a 100%);
            border-radius: 14px;
            border: 1px solid rgba(56, 189, 248, 0.35);
            box-shadow: 0 4px 18px rgba(16, 117, 126, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Resplandor ambiental interactivo */
        .brand-biomed-module::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.18) 0%, transparent 70%);
            opacity: 0.8;
            pointer-events: none;
            animation: pulseGlowWave 3s ease-in-out infinite;
        }

        .brand-vector-logo:hover .brand-biomed-module {
            transform: translateY(-2px);
            border-color: #38bdf8;
            box-shadow: 0 8px 25px rgba(16, 117, 126, 0.4), 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .brand-cardio-svg {
            height: 38px;
            width: 130px;
            display: block;
            overflow: visible;
        }

        /* Trazados SVG del Cardio */
        .nav-ecg-bg-line {
            stroke: rgba(255, 255, 255, 0.18);
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .nav-ecg-stream {
            stroke: #38bdf8;
            stroke-width: 2.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
            stroke-dasharray: 45 130;
            animation: cardioDashFlow 1.8s linear infinite;
            filter: drop-shadow(0 0 7px #38bdf8);
        }

        .brand-text-block {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Nombre IMPOBIOMEDICAL pegado con tipografía de alto impacto */
        .brand-title-main {
            font-family: 'Outfit', sans-serif;
            font-size: 23px;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -0.035em;
            display: flex;
            align-items: center;
        }

        .brand-title-main .txt-impo {
            color: var(--navy-deep);
            letter-spacing: -0.03em;
        }

        .brand-title-main .txt-bio {
            color: #64748b;
            font-weight: 700;
        }

        .brand-title-main .txt-medical {
            color: var(--primary);
            font-weight: 900;
        }

        .brand-sub-badge {
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-muted);
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-sub-badge .live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
            box-shadow: 0 0 6px #10b981;
            animation: pulseDot 2s infinite;
        }

        .nav-menu-links {
            display: flex;
            align-items: center;
            gap: 34px;
            list-style: none;
        }

        .nav-menu-links a {
            text-decoration: none;
            color: var(--slate-text);
            font-size: 14.5px;
            font-weight: 600;
            position: relative;
            padding: 6px 0;
            transition: color 0.25s ease;
        }

        .nav-menu-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2.5px;
            background: var(--primary);
            border-radius: 2px;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-menu-links a:hover {
            color: var(--primary);
        }

        .nav-menu-links a:hover::after {
            width: 100%;
        }

        .btn-portal-cta {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
            padding: 11px 24px;
            border-radius: 9999px;
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(16, 117, 126, 0.32);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-portal-cta::after {
            content: '';
            position: absolute;
            top: 0;
            left: -120%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.35), transparent);
            transform: skewX(-25deg);
            animation: shineSweep 4.5s infinite;
        }

        .btn-portal-cta:hover {
            transform: translateY(-2.5px);
            box-shadow: 0 10px 24px rgba(16, 117, 126, 0.45);
            color: var(--white);
        }

        /* ── HERO CORPORATIVO: FONDOS SUTILES Y CUADRÍCULA ── */
        .hero-corp-section {
            padding: 155px 28px 90px;
            position: relative;
            background: 
                radial-gradient(circle at 88% 20%, rgba(16, 117, 126, 0.12) 0%, transparent 55%),
                radial-gradient(circle at 12% 75%, rgba(2, 132, 199, 0.08) 0%, transparent 60%),
                linear-gradient(180deg, #ffffff 0%, var(--bg-canvas) 100%);
            overflow: hidden;
        }

        .hero-corp-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(226, 232, 240, 0.6) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(226, 232, 240, 0.6) 1px, transparent 1px);
            opacity: 0.7;
            pointer-events: none;
        }

        .hero-corp-container {
            max-width: 1340px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 55px;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .company-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #e6f6f7;
            color: var(--primary-dark);
            border: 1px solid #bce6ea;
            padding: 7px 18px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(16, 117, 126, 0.1);
        }

        .pulse-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            position: relative;
        }

        .pulse-indicator::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: #10b981;
            animation: pulseRadar 1.8s infinite;
        }

        .hero-title-corp {
            font-size: 48px;
            line-height: 1.15;
            color: var(--navy-deep);
            font-weight: 900;
            margin-bottom: 22px;
        }

        .hero-title-corp .highlight-text {
            background: linear-gradient(135deg, var(--primary) 0%, var(--cyan-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .hero-lead-text {
            font-size: 17px;
            color: var(--slate-muted);
            margin-bottom: 34px;
            max-width: 580px;
            line-height: 1.7;
        }

        .hero-btn-row {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 38px;
        }

        .btn-outline-corp {
            background: var(--white);
            color: var(--navy-deep);
            border: 1.8px solid #cbd5e1;
            padding: 11px 26px;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .btn-outline-corp:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2.5px);
            box-shadow: 0 8px 18px rgba(16, 117, 126, 0.12);
        }

        .hero-stats-strip {
            display: flex;
            align-items: center;
            gap: 34px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-num {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 900;
            color: var(--navy-deep);
            line-height: 1;
        }

        .stat-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--slate-muted);
            margin-top: 4px;
        }

        /* ── TARJETA DERECHA: LOGOS EXTRA GRANDES Y MÁXIMA DEFINICIÓN ── */
        .hero-showcase-box {
            position: relative;
            animation: floatHeroCard 7s ease-in-out infinite;
        }

        .dashboard-preview-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 32px 30px;
            border: 1.5px solid rgba(226, 232, 240, 0.95);
            box-shadow: var(--shadow-card);
            position: relative;
            z-index: 2;
        }

        .dash-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 22px;
        }

        .dash-header-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dash-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulseDot 2s infinite;
        }

        .dash-header-text h3 {
            font-size: 16.5px;
            font-weight: 800;
            color: var(--navy-deep);
            margin: 0;
            line-height: 1.2;
        }

        .dash-header-text span {
            font-size: 12px;
            color: var(--slate-muted);
        }

        .dash-live-badge {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: var(--primary);
            background: #e6f6f7;
            padding: 5px 12px;
            border-radius: 9999px;
            border: 1px solid #bce6ea;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* BANNERS DE LOGOS CON DISEÑO PREMIUM & HOVER DE COLOR */
        .logos-hd-duo-container {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 22px;
        }

        .logo-hd-banner {
            background: linear-gradient(135deg, #ffffff 0%, #fbfdff 100%);
            border: 1.8px solid #e2e8f0;
            border-radius: 20px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .logo-hd-banner::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: linear-gradient(180deg, var(--primary) 0%, #38bdf8 100%);
            border-radius: 4px 0 0 4px;
        }

        .logo-hd-banner.banner-impomin::before {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        }

        /* Hover dinámico con los colores respectivos */
        .logo-hd-banner:hover {
            border-color: #bce6ea;
            box-shadow: 0 12px 28px rgba(16, 117, 126, 0.18);
            transform: translateY(-3px);
        }

        .logo-hd-banner.banner-impomin:hover {
            border-color: #fed7aa;
            box-shadow: 0 12px 28px rgba(245, 158, 11, 0.18);
            transform: translateY(-3px);
        }

        .logo-hd-img-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 6px 12px;
            min-height: 88px;
        }

        /* Tamaño original que no se desborda ni se agranda de más */
        .logo-hd-img-wrap img.logo-img-pdf {
            width: 100%;
            max-width: 380px;
            height: 84px;
            object-fit: contain;
            display: block;
        }

        .logo-hd-img-wrap img.logo-img-imp {
            width: 100%;
            max-width: 360px;
            height: 74px;
            object-fit: contain;
            display: block;
        }

        .logo-tag-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 10px 14px;
            flex-shrink: 0;
            text-align: right;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .logo-tag-info .brand-type {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--primary);
            letter-spacing: 0.6px;
            display: block;
            margin-bottom: 2px;
        }

        .logo-tag-info .brand-detail {
            font-size: 12px;
            font-weight: 700;
            color: var(--navy-deep);
        }

        .logo-hd-banner.banner-impomin .logo-tag-info .brand-type {
            color: #d97706;
        }

        /* Consola de Métricas */
        .dash-metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .metric-mini-tile {
            background: #f8fafc;
            border: 1px solid #eef2f6;
            border-radius: 14px;
            padding: 14px 12px;
            text-align: center;
            transition: all 0.25s ease;
        }

        .metric-mini-tile:hover {
            background: #ffffff;
            border-color: #bce6ea;
            box-shadow: 0 6px 14px rgba(16, 117, 126, 0.1);
            transform: translateY(-2px);
        }

        .tile-icon {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 6px;
            display: inline-block;
        }

        .tile-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .tile-value {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: var(--navy-deep);
        }

        .floating-badge-portal {
            position: absolute;
            bottom: -18px;
            left: 24px;
            background: #ffffff;
            color: var(--navy-deep);
            padding: 10px 20px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            gap: 9px;
            border: 1px solid #e2e8f0;
            z-index: 3;
            font-size: 12.5px;
            font-weight: 800;
        }

        .floating-badge-portal i {
            color: #10b981;
            font-size: 17px;
        }

        /* ── SECCIÓN: IDENTIDAD CORPORATIVA ── */
        .section-wrapper {
            max-width: 1340px;
            margin: 0 auto;
            padding: 95px 28px;
        }

        .section-tagline {
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .section-title-corp {
            font-size: 38px;
            color: var(--navy-deep);
            font-weight: 900;
            line-height: 1.22;
            margin-bottom: 20px;
        }

        .about-split-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 55px;
            align-items: center;
            margin-top: 35px;
        }

        .about-text-content p {
            font-size: 16px;
            color: var(--slate-muted);
            line-height: 1.75;
            margin-bottom: 20px;
        }

        .about-highlights {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 26px;
        }

        .about-card-mini {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }

        .about-card-mini:hover {
            transform: translateY(-3px);
            border-color: var(--primary);
            box-shadow: var(--shadow-card);
        }

        .about-card-mini i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 10px;
            display: block;
        }

        .about-card-mini h4 {
            font-size: 15px;
            font-weight: 800;
            color: var(--navy-deep);
            margin-bottom: 6px;
        }

        .about-card-mini p {
            font-size: 12.5px;
            color: var(--slate-muted);
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* Sedes y Ubicaciones */
        .locations-box {
            background: #ffffff;
            border-radius: 24px;
            padding: 36px 32px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-card);
            position: relative;
        }

        .locations-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .locations-header h3 {
            font-size: 20px;
            font-weight: 800;
            color: var(--navy-deep);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .location-item {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
            transition: transform 0.2s;
        }

        .location-item:hover {
            transform: translateX(4px);
        }

        .location-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .loc-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #f1f5f9;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .location-item:hover .loc-icon {
            background: var(--primary);
            color: #ffffff;
        }

        .loc-details h4 {
            font-size: 16px;
            font-weight: 800;
            color: var(--navy-deep);
            margin-bottom: 4px;
        }

        .loc-details p {
            font-size: 13.5px;
            color: var(--slate-muted);
            line-height: 1.5;
        }

        /* ── SECCIÓN: FLUJO REAL DEL SISTEMA ── */
        .process-section {
            background: #ffffff;
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
            padding: 95px 28px;
            position: relative;
        }

        .process-header-center {
            max-width: 720px;
            margin: 0 auto 55px;
            text-align: center;
        }

        .process-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1340px;
            margin: 0 auto;
        }

        .process-card {
            background: var(--bg-canvas);
            border: 1.5px solid var(--border-light);
            border-radius: 22px;
            padding: 34px 28px;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .process-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--cyan-accent));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .process-card:hover {
            transform: translateY(-8px);
            border-color: #bce6ea;
            box-shadow: var(--shadow-card);
            background: #ffffff;
        }

        .process-card:hover::before {
            opacity: 1;
        }

        .process-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .process-step-pill {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            background: #e6f6f7;
            padding: 5px 14px;
            border-radius: 9999px;
            letter-spacing: 0.5px;
        }

        .process-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }

        .process-title {
            font-size: 21px;
            font-weight: 800;
            color: var(--navy-deep);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .process-desc {
            font-size: 14.5px;
            color: var(--slate-muted);
            line-height: 1.65;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .process-points-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 18px;
        }

        .process-points-list li {
            font-size: 13px;
            color: var(--navy-slate);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .process-points-list li i {
            color: #10b981;
            font-size: 15px;
        }

        /* ── BANNER ACCESO PLATAFORMA ── */
        .banner-access-box {
            background: linear-gradient(135deg, var(--navy-deep) 0%, #0d3644 100%);
            border-radius: 28px;
            padding: 55px 48px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 40px;
            margin: 35px auto 0;
            max-width: 1340px;
            box-shadow: var(--shadow-elevate);
            position: relative;
            overflow: hidden;
        }

        .banner-access-box::after {
            content: '';
            position: absolute;
            right: -100px;
            bottom: -100px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 117, 126, 0.25) 0%, transparent 70%);
            pointer-events: none;
        }

        .banner-access-box h2 {
            font-size: 34px;
            color: #ffffff;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .banner-access-box p {
            color: #94a3b8;
            font-size: 16px;
            max-width: 650px;
            line-height: 1.6;
        }

        /* ── FOOTER CORPORATIVO REDISEÑADO CON ONDA VIVA ANIMADA (SIN IMÁGENES) ── */
        .footer-corp-dark {
            background: var(--navy-deep);
            color: #94a3b8;
            padding: 70px 28px 35px;
            border-top: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
        }

        .footer-wave-box {
            max-width: 1340px;
            margin: 0 auto 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .footer-wave-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .wave-svg-container {
            flex: 1;
            height: 48px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .ecg-path-bg {
            stroke: rgba(255, 255, 255, 0.06);
            stroke-width: 2;
            fill: none;
        }

        .ecg-path-pulse {
            stroke: #38bdf8;
            stroke-width: 2.5;
            fill: none;
            stroke-dasharray: 120 480;
            animation: ecgMove 4.5s linear infinite, pulseGlowWave 2s ease-in-out infinite;
        }

        .footer-corp-grid {
            max-width: 1340px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.8fr 1.2fr 1fr;
            gap: 50px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-brand-title i {
            color: #38bdf8;
            font-size: 24px;
        }

        .footer-heading {
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 18px;
        }

        .footer-bottom-bar {
            max-width: 1340px;
            margin: 28px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #64748b;
        }

        /* ── MODAL DE LOGIN (MÉTODO POST SEGURO) ── */
        .modal-overlay-bg {
            position: fixed;
            inset: 0;
            background: rgba(10, 17, 30, 0.82);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .modal-overlay-bg.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-login-dialog {
            background: #ffffff;
            width: 100%;
            max-width: 460px;
            border-radius: 28px;
            box-shadow: 0 35px 70px -15px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            transform: scale(0.92) translateY(20px);
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-overlay-bg.active .modal-login-dialog {
            transform: scale(1) translateY(0);
        }

        .modal-header-top {
            background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
            padding: 26px 30px;
            color: #ffffff;
            position: relative;
        }

        .btn-modal-close-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.18);
            border: none;
            color: #ffffff;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-modal-close-icon:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: rotate(90deg);
        }

        .modal-body-content {
            padding: 32px 30px;
        }

        .alert-error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        .form-row-modern {
            margin-bottom: 20px;
        }

        .form-row-modern label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--navy-deep);
            margin-bottom: 8px;
        }

        .input-group-styled {
            position: relative;
        }

        .input-group-styled input {
            width: 100%;
            padding: 13px 44px 13px 16px;
            border: 1.8px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14.5px;
            color: var(--navy-deep);
            outline: none;
            transition: all 0.25s;
            font-family: inherit;
        }

        .input-group-styled input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(16, 117, 126, 0.15);
        }

        .field-icon-symbol {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .btn-submit-login-action {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            border: none;
            padding: 13px;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 15.5px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: all 0.25s;
            margin-top: 12px;
            box-shadow: 0 6px 16px rgba(16, 117, 126, 0.3);
        }

        .btn-submit-login-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(16, 117, 126, 0.45);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .hero-corp-container { grid-template-columns: 1fr; text-align: center; gap: 50px; }
            .hero-title-corp { font-size: 42px; }
            .hero-lead-text { margin: 0 auto 30px; }
            .hero-btn-row { justify-content: center; }
            .hero-stats-strip { justify-content: center; }
            .about-split-grid { grid-template-columns: 1fr; }
            .process-grid-3 { grid-template-columns: 1fr; }
            .banner-access-box { flex-direction: column; text-align: center; padding: 40px 30px; }
            .footer-corp-grid { grid-template-columns: 1fr; }
            .nav-menu-links { display: none; }
            .floating-badge-portal { left: 50%; transform: translateX(-50%); }
            .footer-wave-box { flex-direction: column; align-items: flex-start; }
            .wave-svg-container { width: 100%; }
        }

        @media (max-width: 640px) {
            .logo-hd-banner { flex-direction: column; text-align: center; padding: 16px; }
            .logo-tag-info { text-align: center; border-left: none; border-top: 2px solid var(--primary); }
            .dash-metrics-grid { grid-template-columns: 1fr; }
            .hero-title-corp { font-size: 33px; }
            .hero-stats-strip { flex-direction: column; gap: 16px; }
            .brand-cardio-svg { width: 90px; }
        }
    </style>
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
                    <button class="btn-portal-cta" onclick="abrirAccesoPortal()" style="padding: 14px 30px; font-size: 15.5px;">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar a la Plataforma
                    </button>
                    <a href="#operacion" class="btn-outline-corp">
                        <i class="bi bi-diagram-3-fill" style="color:var(--primary);"></i> Ver Flujo Operativo
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
                            <i class="bi bi-circle-fill" style="font-size:7px;"></i> SISTEMA ACTIVO
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
                    <h3><i class="bi bi-geo-alt-fill" style="color:var(--primary);"></i> Sedes de Atención</h3>
                    <span style="font-size:12px; font-weight:700; color:var(--primary); background:#e6f6f7; padding:4px 10px; border-radius:9999px;">Nacional</span>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-building"></i></div>
                    <div class="loc-details">
                        <h4>Sede Florencia — Caquetá</h4>
                        <p>Cra. 10 #9-80 Barrio Cooperativa</p>
                        <p style="color: var(--slate-muted); font-size: 12.5px;">Atención a la región sur y Amazonía colombiana</p>
                    </div>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-building"></i></div>
                    <div class="loc-details">
                        <h4>Sede Sabaneta / Medellín — Antioquia</h4>
                        <p>Calle 61 Sur #43A-85 (Sabaneta) / Laureles (Medellín)</p>
                        <p style="color: var(--slate-muted); font-size: 12.5px;">Coordinación logística y enlace corporativo</p>
                    </div>
                </div>

                <div class="location-item">
                    <div class="loc-icon"><i class="bi bi-telephone-inbound-fill"></i></div>
                    <div class="loc-details">
                        <h4>Líneas y Canales Oficiales</h4>
                        <p>Telefax: (4) 322 27 79 &bull; Móvil: 317 345 3644 / 310 269 0595</p>
                        <p style="color: var(--primary); font-weight: 700; font-size: 13px;">impobiomedical@impomin.com</p>
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
            <p style="color: var(--slate-muted); font-size: 16px;">
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
    <section style="padding: 30px 28px 70px;">
        <div class="banner-access-box reveal">
            <div>
                <h2>Plataforma Comercial de IMPOMIN S.A.S</h2>
                <p>Acceso exclusivo para el personal comercial, directores de compras y administradores del sistema Impobiomedical.</p>
            </div>
            <button class="btn-portal-cta" onclick="abrirAccesoPortal()" style="background:#ffffff; color:var(--navy-deep); box-shadow:0 10px 25px rgba(0,0,0,0.35); flex-shrink:0; font-size:15px; padding:14px 28px;">
                <i class="bi bi-box-arrow-in-right" style="color:var(--primary);"></i> Iniciar Sesión en el Sistema
            </button>
        </div>
    </section>

    <!-- ══ FOOTER CORPORATIVO REDISEÑADO CON ONDA VIVA ANIMADA (SIN IMÁGENES) ══ -->
    <footer id="contacto" class="footer-corp-dark">
        <!-- Barra superior con línea de pulso biomédico animada -->
        <div class="footer-wave-box">
            <div class="footer-wave-label">
                <i class="bi bi-activity" style="color:#38bdf8; font-size:18px;"></i>
                <span>Monitoreo & Trazabilidad Biomédica Activa</span>
            </div>
            <div class="wave-svg-container">
                <svg viewBox="0 0 600 50" preserveAspectRatio="none" style="width:100%; height:40px;">
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

                <p style="font-size: 13.5px; line-height: 1.65; margin-bottom: 16px; color:#cbd5e1;">
                    Gestión comercial, estructuración de cotizaciones especializadas y enlace de suministros para el sector salud y clínico en Colombia.
                </p>
                <p style="font-size: 12.5px; color: #64748b;">
                    <strong>Razón Social:</strong> IMPOMIN S.A.S &bull; NIT: 900.535.843-3
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Sedes y Atención</h4>
                <p style="font-size: 13.5px; line-height: 1.6; margin-bottom: 12px; color:#cbd5e1;">
                    <strong style="color:#ffffff;">Florencia (Caquetá):</strong><br>
                    Cra. 10 #9-80 Barrio Cooperativa
                </p>
                <p style="font-size: 13.5px; line-height: 1.6; color:#cbd5e1;">
                    <strong style="color:#ffffff;">Antioquia:</strong><br>
                    Calle 61 Sur #43A-85 (Sabaneta) / Laureles (Medellín)
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Canales Oficiales</h4>
                <p style="font-size: 13.5px; margin-bottom: 10px; color:#cbd5e1;">
                    <i class="bi bi-envelope-fill" style="color: var(--primary-light);"></i> impobiomedical@impomin.com
                </p>
                <p style="font-size: 13.5px; margin-bottom: 16px; color:#cbd5e1;">
                    <i class="bi bi-telephone-fill" style="color: var(--primary-light);"></i> (4) 322 27 79 / 317 345 3644
                </p>
                <button class="btn-portal-cta" onclick="abrirAccesoPortal()" style="width: 100%; justify-content: center; padding: 11px 18px; font-size: 13.5px;">
                    <i class="bi bi-lock-fill"></i> Acceso Usuarios
                </button>
            </div>
        </div>

        <div class="footer-bottom-bar">
            <span>&copy; <?= date('Y') ?> IMPOMIN S.A.S &bull; Impobiomedical. Todos los derechos reservados.</span>
            <span>Sistema Comercial &bull; Cotizaciones & Órdenes de Compra</span>
        </div>
    </footer>

    <!-- ══ MODAL DE ACCESO SEGURO (MÉTODO POST) ══ -->
    <div id="modalPortalLogin" class="modal-overlay-bg <?= !empty($mensajeError) ? 'active' : '' ?>">
        <div class="modal-login-dialog">
            <div class="modal-header-top">
                <button type="button" class="btn-modal-close-icon" onclick="cerrarAccesoPortal()" aria-label="Cerrar">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="background:rgba(255,255,255,0.15); width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#38bdf8; font-size:20px;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 style="font-size:17.5px; margin:0; line-height:1.2; color:#ffffff;">Acceso al Sistema</h3>
                        <p style="font-size:11.5px; opacity:0.88; margin:0; color:#cbd5e1;">Cotizaciones & Órdenes de Compra</p>
                    </div>
                </div>
            </div>

            <div class="modal-body-content">
                <?php if (!empty($mensajeError)): ?>
                    <div class="alert-error-box">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size:18px;"></i>
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
                            <button type="button" onclick="alternarVerPass()" style="background:none; border:none; cursor:pointer;" class="field-icon-symbol" aria-label="Mostrar contraseña">
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
