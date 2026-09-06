<?php
/**
 * Layout header — emite el <head> HTML completo.
 * Variables esperadas:
 *   $pageTitle  string  — título de la pestaña
 *   $extraHead  string  — HTML adicional opcional
 */
$pageTitle = $pageTitle ?? 'Impobiomedical';
$basePath  = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
$extraHead = $extraHead ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts: Inter & Outfit (Tipografía exacta del sistema HDMI) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>css/estilos.css?v=<?= time() ?>">
    <!-- Favicon Oficial -->
    <link rel="icon" type="image/svg+xml" href="<?= $basePath ?>public/favicon.svg?v=<?= time() ?>">
    <link rel="alternate icon" type="image/png" sizes="32x32" href="<?= $basePath ?>public/favicon-32x32.png?v=<?= time() ?>">
    <title><?= htmlspecialchars($pageTitle) ?> — Impobiomedical</title>
    <?= $extraHead ?>
</head>
<body>
<canvas id="particle-canvas"></canvas>
<div class="noise-overlay"></div>
