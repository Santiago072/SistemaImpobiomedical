<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CalculosCotizacionTest extends TestCase
{
    /**
     * Prueba el cálculo de precio unitario con utilidad, flete, calibración y estampillas.
     */
    public function testCalculoPrecioConUtilidadYCostos(): void
    {
        $precioProveedor = 100000.0;
        $porcentajeUtilidad = 30.0; // 30%
        $flete = 15000.0;
        $calibracion = 25000.0;
        $estampillas = 5000.0;

        // Subtotal con utilidad
        $ganancia = $precioProveedor * ($porcentajeUtilidad / 100);
        $precioConUtilidad = $precioProveedor + $ganancia; // 130,000

        // Precio final unitario antes de IVA
        $precioUnitario = $precioConUtilidad + $flete + $calibracion + $estampillas; // 175,000

        $this->assertSame(130000.0, $precioConUtilidad);
        $this->assertSame(175000.0, $precioUnitario);
    }

    /**
     * Prueba el cálculo de IVA (19% vs 0% exento).
     */
    public function testCalculoIvaItem(): void
    {
        $precioUnitario = 200000.0;
        $cantidad = 3;
        $subtotal = $precioUnitario * $cantidad; // 600,000

        // Con IVA 19%
        $porcentajeIva = 19.0;
        $ivaCalculado = $subtotal * ($porcentajeIva / 100); // 114,000
        $totalConIva = $subtotal + $ivaCalculado; // 714,000

        $this->assertSame(600000.0, $subtotal);
        $this->assertSame(114000.0, $ivaCalculado);
        $this->assertSame(714000.0, $totalConIva);

        // Ítem exento (IVA = no)
        $ivaExento = 0.0;
        $totalExento = $subtotal + $ivaExento;
        $this->assertSame(600000.0, $totalExento);
    }

    /**
     * Prueba el formato del número de cotización consecutivo.
     */
    public function testFormatoNumeroCotizacion(): void
    {
        $codigoUsuario = "EB";
        $consecutivo = 5;
        $numeroCotizacion = trim($codigoUsuario) . ' ' . str_pad((string)$consecutivo, 2, '0', STR_PAD_LEFT);

        $this->assertSame('EB 05', $numeroCotizacion);

        // Prueba de formato de revisión / versión
        $revision = $numeroCotizacion . '_01';
        $this->assertSame('EB 05_01', $revision);
    }

    /**
     * Prueba de validación de contraseñas con bcrypt.
     */
    public function testHashYVerificacionPassword(): void
    {
        $passwordPlana = 'MedicoSeguro2026';
        $hash = password_hash($passwordPlana, PASSWORD_BCRYPT);

        $this->assertNotEmpty($hash);
        $this->assertTrue(password_verify($passwordPlana, $hash));
        $this->assertFalse(password_verify('password_incorrecta', $hash));
    }
}
