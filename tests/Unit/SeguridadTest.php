<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SeguridadTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testGenerarYVerificarTokenCsrf(): void
    {
        $token = generar_token_csrf();
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token)); // 32 bytes en hex = 64 chars

        $this->assertTrue(verificar_token_csrf($token));
        $this->assertFalse(verificar_token_csrf('token_falso_invalido'));
        $this->assertFalse(verificar_token_csrf(''));
    }

    public function testRotarTokenCsrf(): void
    {
        $token1 = generar_token_csrf();
        $token2 = rotar_token_csrf();

        $this->assertNotSame($token1, $token2);
        $this->assertFalse(verificar_token_csrf($token1));
        $this->assertTrue(verificar_token_csrf($token2));
    }

    public function testSanitizarEntrada(): void
    {
        $entrada = "   Texto de prueba con espacios   ";
        $this->assertSame("Texto de prueba con espacios", sanitizar_entrada($entrada));

        $conSlashes = "Nombre\\ 'O\\'Reilly'";
        $this->assertSame("Nombre 'O'Reilly'", sanitizar_entrada($conSlashes));
    }

    public function testEscaparSalidaXss(): void
    {
        $xss = '<script>alert("hack")</script>';
        $escapado = escapar_salida($xss);
        $this->assertSame('&lt;script&gt;alert(&quot;hack&quot;)&lt;/script&gt;', $escapado);
        $this->assertStringNotContainsString('<script>', $escapado);
    }

    public function testValidarEmail(): void
    {
        $this->assertTrue(validar_email('usuario@impobiomedical.com'));
        $this->assertTrue(validar_email('admin.medico@hospital.gov.co'));
        $this->assertFalse(validar_email('correo_invalido'));
        $this->assertFalse(validar_email('usuario@.com'));
        $this->assertFalse(validar_email(''));
    }

    public function testValidarNumero(): void
    {
        $this->assertTrue(validar_numero(10));
        $this->assertTrue(validar_numero('25'));
        $this->assertTrue(validar_numero('100.5'));
        $this->assertFalse(validar_numero(0));
        $this->assertFalse(validar_numero(-5));
        $this->assertFalse(validar_numero('abc'));
        $this->assertFalse(validar_numero(''));
    }

    public function testRateLimitingPermiteDentroDelLimite(): void
    {
        for ($i = 0; $i < 5; $i++) {
            verificar_rate_limit(10, 60, 'test_action');
        }
        $this->assertCount(5, $_SESSION['rate_limit_test_action']);
    }
}
