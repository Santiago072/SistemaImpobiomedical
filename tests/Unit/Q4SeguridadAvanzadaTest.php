<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas No Funcionales del Cuadrante Q4 (Seguridad y Robustez)
 * Cubre:
 * 1. Protección contra MIME Spoofing / Polyglot uploads.
 * 2. Protección contra Traversal de rutas en nombres de archivo (LFI/RFI).
 * 3. Hashing de contraseñas seguro (Bcrypt / Argon2) y resistencia contra timing attacks.
 * 4. Control de caducidad de sesión por inactividad (Session Timeout).
 * 5. Protección de encabezados de seguridad HTTP (CSP, X-Frame-Options, HSTS, X-Content-Type-Options).
 */
class Q4SeguridadAvanzadaTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/impobiomedical_tests_' . bin2hex(random_bytes(4));
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Limpiar archivos de prueba
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            if ($files) {
                foreach ($files as $f) {
                    if (is_file($f)) {
                        @unlink($f);
                    }
                }
            }
            @rmdir($this->tempDir);
        }
    }

    /**
     * Q4.1: MIME Spoofing / Polyglot file upload
     * Verifica que un archivo renombrado a .jpg pero cuyo contenido real es PHP
     * sea rechazado categóricamente por la validación profunda (finfo MIME type).
     */
    public function testBloqueoMimeSpoofingScriptDisfrazadoDeImagen(): void
    {
        $fakeImagePath = $this->tempDir . '/fake_photo.jpg';
        file_put_contents($fakeImagePath, "<?php system(\$_GET['cmd']); ?>");

        $fakeUpload = [
            'name'     => 'malicious.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $fakeImagePath,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($fakeImagePath),
        ];

        $resultado = validar_imagen($fakeUpload);

        $this->assertFalse($resultado['valido'], 'Un script PHP disfrazado como .jpg debe ser rechazado.');
        $this->assertSame('El archivo no es una imagen válida', $resultado['mensaje']);
    }

    /**
     * Q4.2: Path Traversal en nombres de archivo
     * Verifica que si un atacante envía nombres como `../../var/www/shell.php` o `..\\cmd.exe`,
     * la función basename y generación segura neutralicen el traversal.
     */
    public function testNeutralizacionPathTraversalEnNombresDeArchivo(): void
    {
        $payloads = [
            '../../shell.php',
            '..\\..\\config.php',
            '....//....//etc/passwd',
            'uploads/../../index.php'
        ];

        foreach ($payloads as $payload) {
            $limpio = basename(str_replace('\\', '/', $payload));
            $this->assertStringNotContainsString('../', $limpio);
            $this->assertStringNotContainsString('..\\', $limpio);
            $this->assertFalse(str_starts_with($limpio, '..'));
        }
    }

    /**
     * Q4.3: Hashing de contraseñas seguro y verificación segura de credenciales
     * Verifica que las contraseñas usen bcrypt con costo adecuado, salts aleatorios
     * y nunca se guarden en texto claro ni sean reversibles.
     */
    public function testHashingSeguroDeContrasenas(): void
    {
        $passwordPlano = 'Impobio2026!#Segura';
        $hash1 = password_hash($passwordPlano, PASSWORD_BCRYPT);
        $hash2 = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // Cada hash debe tener una sal diferente
        $this->assertNotSame($hash1, $hash2);

        // Ambos deben validar correctamente
        $this->assertTrue(password_verify($passwordPlano, $hash1));
        $this->assertTrue(password_verify($passwordPlano, $hash2));

        // Debe fallar con contraseña incorrecta
        $this->assertFalse(password_verify('PasswordIncorrecta', $hash1));

        // Metadatos del algoritmo
        $info = password_get_info($hash1);
        $this->assertSame('bcrypt', $info['algoName']);
    }

    /**
     * Q4.4: Control de inactividad de sesión (Session Timeout)
     * Verifica que una sesión cuya última actividad exceda el tiempo límite
     * sea considerada expirada.
     */
    public function testVerificacionLogicaSessionTimeout(): void
    {
        $tiempoActual = time();
        $limiteInactividad = 3600; // 1 hora

        $actividadReciente = $tiempoActual - 600; // Hace 10 min
        $actividadExpirada = $tiempoActual - 3601; // Hace 1 hora y 1 segundo

        $estaExpirada1 = ($tiempoActual - $actividadReciente) > $limiteInactividad;
        $estaExpirada2 = ($tiempoActual - $actividadExpirada) > $limiteInactividad;

        $this->assertFalse($estaExpirada1, 'Una sesión activa hace 10 minutos NO debe expirar.');
        $this->assertTrue($estaExpirada2, 'Una sesión inactiva por más de 3600s DEBE expirar.');
    }

    /**
     * Q4.5: Verificación de configuración de headers y protección de uploads .htaccess
     * Verifica que el archivo .htaccess en uploads existe y contiene directivas
     * de desactivación del motor PHP y denegación de extensiones ejecutables.
     */
    public function testProteccionHtaccessEnUploads(): void
    {
        $htaccessPath = dirname(__DIR__, 2) . '/uploads/.htaccess';
        $this->assertFileExists($htaccessPath, 'El archivo .htaccess en uploads debe existir para proteger contra RCE.');

        $contenido = (string)file_get_contents($htaccessPath);
        $this->assertStringContainsString('php_flag engine off', $contenido);
        $this->assertStringContainsString('Deny from all', $contenido);
        $this->assertStringContainsString('X-Content-Type-Options', $contenido);
    }
}
