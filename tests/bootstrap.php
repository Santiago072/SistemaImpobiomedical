<?php
/**
 * Test Bootstrap — Inicializa el entorno de pruebas unitarias.
 */
declare(strict_types=1);

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/SistemaImpobiomedical/');
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/seguridad.php';

// Limpiar sesiones previas si existieran
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_destroy();
}
