<?php
/**
 * seguridad.php — Funciones de seguridad del sistema Impobiomedical.
 *
 * - SRP: cada función tiene una única responsabilidad.
 * - Seguridad: CSRF, Rate Limiting, sanitización, escape de salida, control de acceso.
 */

// ── Sesión segura ─────────────────────────────────────────────────────────────

function iniciar_sesion_segura(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', (int)($_ENV['COOKIE_SECURE'] ?? getenv('COOKIE_SECURE') ?: 0));
    ini_set('session.cookie_samesite', 'Strict');

    session_start();

    // Timeout por inactividad
    $timeout = (int)($_ENV['SESSION_LIFETIME'] ?? getenv('SESSION_LIFETIME') ?: 3600);
    $base    = defined('BASE_URL') ? BASE_URL : '/';

    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        session_unset();
        session_destroy();
        header("Location: {$base}?timeout=1");
        exit();
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}

// ── CSRF ──────────────────────────────────────────────────────────────────────

function generar_token_csrf(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_token_csrf(string $token): bool
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function rotar_token_csrf(): string
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// ── Rate Limiting ─────────────────────────────────────────────────────────────

function verificar_rate_limit(int $limite = 15, int $ventanaSegundos = 60, string $accion = 'global'): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $clave        = "rate_limit_" . $accion;
    $tiempoActual = time();

    if (!isset($_SESSION[$clave])) {
        $_SESSION[$clave] = [];
    }

    $_SESSION[$clave] = array_filter($_SESSION[$clave], function ($timestamp) use ($tiempoActual, $ventanaSegundos) {
        return ($tiempoActual - $timestamp) < $ventanaSegundos;
    });

    if (count($_SESSION[$clave]) >= $limite) {
        http_response_code(429);
        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Demasiadas peticiones. Espere un momento.']);
        } else {
            echo "<h1>429 - Demasiadas peticiones</h1><p>Por favor, espere un momento.</p>";
        }
        exit;
    }

    $_SESSION[$clave][] = $tiempoActual;
}

// ── Sanitización y escape ─────────────────────────────────────────────────────

/**
 * Limpia una entrada de texto. NO aplica htmlspecialchars (eso es de SALIDA).
 */
function sanitizar_entrada($data): string
{
    return stripslashes(trim((string)$data));
}

/**
 * Escapa para imprimir en HTML. Usar en TODAS las vistas.
 */
function escapar_salida($data): string
{
    return htmlspecialchars((string)$data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Validadores ───────────────────────────────────────────────────────────────

function validar_email(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validar_numero($numero): bool
{
    return is_numeric($numero) && $numero > 0;
}

function validar_imagen(array $archivo): array
{
    $extensionesPermitidas = explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? getenv('ALLOWED_EXTENSIONS') ?: 'jpg,jpeg,png,gif,webp');
    $maxSize               = (int)($_ENV['UPLOAD_MAX_SIZE'] ?? getenv('UPLOAD_MAX_SIZE') ?: 5242880);

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['valido' => false, 'mensaje' => 'Error al subir el archivo'];
    }
    if ($archivo['size'] > $maxSize) {
        return ['valido' => false, 'mensaje' => 'El archivo es demasiado grande (máximo 5 MB)'];
    }

    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesPermitidas, true)) {
        return ['valido' => false, 'mensaje' => 'Tipo de archivo no permitido'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    $mimesPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $mimesPermitidos, true)) {
        return ['valido' => false, 'mensaje' => 'El archivo no es una imagen válida'];
    }

    return ['valido' => true, 'mensaje' => 'Archivo válido'];
}

function generar_nombre_archivo(string $extension): string
{
    return time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
}

// ── Control de acceso ─────────────────────────────────────────────────────────

function verificar_autenticacion(): void
{
    if (!isset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'])) {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        header("Location: {$base}");
        exit();
    }
}

function verificar_admin(): void
{
    verificar_autenticacion();
    if (($_SESSION['rol'] ?? '') !== 'admin') {
        $base = defined('BASE_URL') ? BASE_URL : '/';
        header("Location: {$base}?module=panel");
        exit();
    }
}

function regenerar_sesion(): void
{
    session_regenerate_id(true);
}
