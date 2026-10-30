<?php
/**
 * Test Bootstrap — Inicializa el entorno de pruebas unitarias.
 */
declare(strict_types=1);

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/SistemaImpobiomedical/');
}

// Configurar directorio temporal seguro para sesiones en pruebas (evita fallos de permisos en Linux/CI)
$testSessionDir = sys_get_temp_dir() . '/impobiomedical_test_sessions';
if (!is_dir($testSessionDir)) {
    @mkdir($testSessionDir, 0777, true);
}
@session_save_path($testSessionDir);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/seguridad.php';

// Limpiar sesiones previas si existieran
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}
