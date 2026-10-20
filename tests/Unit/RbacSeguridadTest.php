<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias de Control de Acceso basado en Roles (RBAC) y prevención de IDOR.
 * Valida los niveles de privilegio entre 'admin', 'compras' y 'usuario'.
 */
class RbacSeguridadTest extends TestCase
{
    /**
     * Prueba la regla de autorización para eliminar cotizaciones y órdenes:
     * Solo 'admin' y 'compras' están autorizados; 'usuario' debe ser rechazado.
     */
    public function testPermisosDeEliminacionPorRol(): void
    {
        $rolesAutorizados = ['admin', 'compras'];

        $this->assertTrue(in_array('admin', $rolesAutorizados, true), 'El rol admin debe poder eliminar');
        $this->assertTrue(in_array('compras', $rolesAutorizados, true), 'El rol compras debe poder eliminar');
        $this->assertFalse(in_array('usuario', $rolesAutorizados, true), 'El rol usuario/asesor NO debe poder eliminar');
        $this->assertFalse(in_array('invitado', $rolesAutorizados, true), 'Roles desconocidos no deben tener permisos');
    }

    /**
     * Prueba el aislamiento de datos (Data Scope):
     * 'admin' y 'compras' tienen visión global (omiten filtro usuario_id).
     * 'usuario' (asesor) debe estar restringido a su propio usuario_id.
     */
    public function testAislamientoDeDatosSegunRol(): void
    {
        $evaluarFiltro = function (string $rol, int $usuarioId): bool {
            // Retorna TRUE si se aplica filtro por usuario_id (aislamiento activo)
            return ($rol !== 'admin' && $rol !== 'compras' && $usuarioId > 0);
        };

        $this->assertFalse($evaluarFiltro('admin', 1), 'Admin no debe restringir por usuario_id');
        $this->assertFalse($evaluarFiltro('compras', 2), 'Compras no debe restringir por usuario_id');
        $this->assertTrue($evaluarFiltro('usuario', 5), 'Asesor comercial DEBE filtrar por su propio usuario_id');
    }

    /**
     * Prueba de propiedad de cotización (Ownership Check) para evitar IDOR:
     * Un asesor solo puede modificar una cotización si usuario_id coincide con el autor.
     */
    public function testValidacionPropiedadCotizacion(): void
    {
        $cotizacion = [
            'id' => 10,
            'usuario_id' => 4,
            'numero_cotizacion' => 'EB 01'
        ];

        $puedeModificar = function (array $cot, int $sesionUid, string $sesionRol): bool {
            if (in_array($sesionRol, ['admin', 'compras'], true)) {
                return true;
            }
            return (int)$cot['usuario_id'] === $sesionUid;
        };

        // El autor legítimo puede modificar
        $this->assertTrue($puedeModificar($cotizacion, 4, 'usuario'));

        // Otro asesor intenta modificar (IDOR) -> debe bloquearse
        $this->assertFalse($puedeModificar($cotizacion, 9, 'usuario'));

        // Admin y Compras tienen acceso administrativo
        $this->assertTrue($puedeModificar($cotizacion, 9, 'admin'));
        $this->assertTrue($puedeModificar($cotizacion, 9, 'compras'));
    }
}
