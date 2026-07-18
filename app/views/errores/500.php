<?php
/**
 * Vista de error 500 - Mensaje amistoso.
 */
$base = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Sistema — Impobiomedical</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>css/estilos.css">
    <style>
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-body);
            text-align: center;
            padding: 20px;
        }
        .error-card {
            background: var(--bg-card);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            max-width: 500px;
            width: 100%;
        }
        .error-icon {
            font-size: 80px;
            color: var(--teal-accent);
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        .error-desc {
            font-size: 16px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--teal-primary);
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: var(--teal-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16,117,126,0.3);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <i class="bi bi-tools error-icon"></i>
            <h1 class="error-title">¡Ups! Algo no salió como esperábamos</h1>
            <p class="error-desc">
                Parece que hemos tenido un inconveniente técnico intentando procesar tu solicitud. 
                Por favor, intenta nuevamente en unos momentos o regresa a la página anterior.
            </p>
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left-circle-fill"></i>
                Volver atrás
            </a>
        </div>
    </div>
</body>
</html>
