<?php
/**
 * conexion.php — Configuración de conexión a la base de datos.
 *
 * Principios aplicados:
 *   - SRP: este archivo SOLO gestiona la conexión a la BD.
 *     La carga del .env fue extraída a EnvLoader.
 */

require_once __DIR__ . '/EnvLoader.php';

EnvLoader::load(__DIR__ . '/.env');

/**
 * Crea y devuelve una conexión mysqli a la base de datos.
 * En caso de error, registra en log y termina de forma segura.
 *
 * @return \mysqli
 */
function conexion(): \mysqli
{
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    $db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'sistema_impobiomedical';

    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        error_log('Error de conexión a la BD: ' . mysqli_connect_error());
        http_response_code(503);
        die('El servicio no está disponible temporalmente. Por favor intente más tarde.');
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}
