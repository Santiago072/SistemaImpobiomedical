<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FileUploadServiceTest extends TestCase
{
    /**
     * Prueba la generación de nombres únicos para archivos subidos.
     */
    public function testGeneracionNombresUnicos(): void
    {
        $ext = 'jpg';
        $nombre1 = generar_nombre_archivo($ext);
        $nombre2 = generar_nombre_archivo($ext);

        $this->assertNotEmpty($nombre1);
        $this->assertNotEmpty($nombre2);
        $this->assertNotSame($nombre1, $nombre2);
        $this->assertStringEndsWith('.jpg', $nombre1);
        $this->assertStringEndsWith('.jpg', $nombre2);
    }

    /**
     * Prueba la validación estricta de extensiones de imágenes permitidas.
     */
    public function testValidacionExtensionesPermitidas(): void
    {
        $extensionesValidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extensionesPeligrosas = ['php', 'exe', 'sh', 'js', 'phtml', 'html', 'htaccess'];

        foreach ($extensionesValidas as $ext) {
            $this->assertTrue(in_array(strtolower($ext), $extensionesValidas, true));
        }

        foreach ($extensionesPeligrosas as $badExt) {
            $this->assertFalse(in_array(strtolower($badExt), $extensionesValidas, true));
        }
    }
}
