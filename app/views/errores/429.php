<?php
/**
 * Vista de error 429 - Demasiadas peticiones (Rate Limiting)
 */
$base = defined('BASE_URL') ? BASE_URL : '/SistemaImpobiomedical/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demasiadas Peticiones — Impobiomedical</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $base ?>css/estilos.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
        }
        .error-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .error-card {
            background: #ffffff;
            padding: 45px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            max-width: 480px;
            width: 100%;
            box-sizing: border-box;
        }
        .error-icon {
            font-size: 72px;
            color: #f59e0b;
            margin-bottom: 20px;
            display: inline-block;
        }
        .error-code {
            font-size: 14px;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 12px 0;
            line-height: 1.3;
        }
        .error-desc {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 12px 0;
        }
        .error-timer {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 16px;
            margin: 20px 0;
            font-size: 14px;
            color: #b45309;
            font-weight: 600;
        }
        .timer-display {
            font-size: 28px;
            font-weight: 800;
            color: #f59e0b;
            margin: 8px 0;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #10757e !important;
            color: #ffffff !important;
            padding: 13px 28px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 14px rgba(16, 117, 126, 0.35);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-back:hover {
            background: #0a4f55 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 117, 126, 0.45);
        }
        .btn-back i {
            font-size: 18px;
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-code">Error 429</div>
            <i class="bi bi-exclamation-circle error-icon"></i>
            <h1 class="error-title">Demasiadas Peticiones</h1>
            <p class="error-desc">
                Has excedido el límite de intentos permitidos desde tu dirección IP.
            </p>
            <div class="error-timer">
                <div>Por favor espera antes de intentar nuevamente:</div>
                <div class="timer-display" id="timer">60</div>
                <div id="timerLabel">segundos</div>
            </div>
            <a href="<?= $base ?>" class="btn-back" id="btnBack" style="display:none;">
                <i class="bi bi-arrow-left-circle-fill"></i>
                Volver al Login
            </a>
        </div>
    </div>

    <script>
        let timeLeft = 60;
        const timerEl = document.getElementById('timer');
        const timerLabel = document.getElementById('timerLabel');
        const btnBack = document.getElementById('btnBack');

        const countdown = setInterval(() => {
            timeLeft--;
            timerEl.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                document.querySelector('.error-timer').innerHTML = '✅ Ahora puedes intentar nuevamente';
                document.querySelector('.error-timer').style.background = '#d1fae5';
                document.querySelector('.error-timer').style.borderColor = '#a7f3d0';
                document.querySelector('.error-timer').style.color = '#065f46';
                btnBack.style.display = 'inline-flex';
            }
        }, 1000);
    </script>
</body>
</html>
