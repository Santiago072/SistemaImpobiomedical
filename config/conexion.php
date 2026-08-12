<?php
/**
 * conexion.php — Configuración de conexión a la base de datos via PDO.
 *
 * Principios aplicados:
 *   - SRP: este archivo SOLO gestiona la conexión a la BD.
 *   - La carga del .env fue extraída a EnvLoader.
 *   - PDO con ERRMODE_EXCEPTION para que los errores sean capturados
 *     automáticamente por el manejador global de Throwable en index.php.
 */

require_once __DIR__ . '/EnvLoader.php';

EnvLoader::load(__DIR__ . '/.env');

/**
 * Crea y devuelve una conexión PDO a la base de datos.
 * En caso de error, registra en log y termina de forma segura.
 *
 * @return \PDO
 */
function conexion(): \PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
    $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
    $db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'sistema_impobiomedical';

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (\PDOException $e) {
        error_log('Error de conexión a la BD: ' . $e->getMessage());
        http_response_code(503);
        die('El servicio no está disponible temporalmente. Por favor intente más tarde.');
    }

    return $pdo;
}
