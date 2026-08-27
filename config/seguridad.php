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

    // Configurar directorio de sesiones que exista
    $sessionPath = __DIR__ . '/../sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0755, true);
    }
    session_save_path($sessionPath);

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_secure', (int)($_ENV['COOKIE_SECURE'] ?? getenv('COOKIE_SECURE') ?: 0));
    ini_set('session.cookie_samesite', 'Lax');

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

// ── Rate Limiting (Basado en IP + Archivos locales para evitar bypass por cookies) ───

function obtener_ip_cliente(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip  = trim($ips[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

function verificar_rate_limit(int $limite = 15, int $ventanaSegundos = 60, string $accion = 'global'): void
{
    iniciar_sesion_segura();
    $ip           = obtener_ip_cliente();
    $tiempoActual = time();
    $dirStorage   = sys_get_temp_dir() . '/impobiomedical_rate_limit';

    if (!is_dir($dirStorage)) {
        @mkdir($dirStorage, 0755, true);
    }

    $hashFile   = md5($ip . '_' . $accion);
    $filePath   = $dirStorage . '/rl_' . $hashFile . '.json';
    $timestamps = [];

    if (file_exists($filePath)) {
        $content    = @file_get_contents($filePath);
        $decoded    = json_decode((string)$content, true);
        $timestamps = is_array($decoded) ? $decoded : [];
    }

    // Filtrar marcas de tiempo dentro de la ventana de tiempo especificada
    $timestamps = array_values(array_filter($timestamps, function ($timestamp) use ($tiempoActual, $ventanaSegundos) {
        return ($tiempoActual - (int)$timestamp) < $ventanaSegundos;
    }));

    if (session_status() !== PHP_SESSION_NONE) {
        $_SESSION["rate_limit_" . $accion] = $timestamps;
    }

    if (count($timestamps) >= $limite) {
        http_response_code(429);
        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($esAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Demasiadas peticiones desde su dirección IP. Espere un momento.']);
        } else {
            // Mostrar vista de error 429 profesional en lugar de HTML crudo
            include dirname(__DIR__) . '/app/views/errores/429.php';
        }
        exit;
    }

    $timestamps[] = $tiempoActual;
    @file_put_contents($filePath, json_encode($timestamps), LOCK_EX);

    if (session_status() !== PHP_SESSION_NONE) {
        $_SESSION["rate_limit_" . $accion] = $timestamps;
    }
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
    
    // Prevenir caché para que el botón "Atrás" del navegador no muestre páginas protegidas
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
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
