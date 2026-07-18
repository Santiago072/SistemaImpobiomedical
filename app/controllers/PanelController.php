<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * PanelController — Dashboard con métricas del sistema.
 * - SRP: solo genera datos para el panel principal.
 */
class PanelController
{
    private CotizacionModel $model;

    public function __construct(\mysqli $conexion)
    {
        $this->model = new CotizacionModel($conexion);
    }

    public function index(): array
    {
        verificar_autenticacion();

        $usuarioId  = (int)$_SESSION['usuario_id'];
        $rol        = $_SESSION['rol'] ?? 'usuario';

        if ($rol === 'admin') {
            $totalCotizaciones    = $this->model->contarTotal();
            $cotizacionesMes      = $this->model->contarDelMes();
            $totalClientes        = $this->model->contarTotalClientes();
            $totalProductos       = $this->model->contarTotalProductos();
        } else {
            $totalCotizaciones    = $this->model->contarDelUsuario($usuarioId);
            $cotizacionesMes      = 0;
            $totalClientes        = 0;
            $totalProductos       = 0;
        }

        return compact('totalCotizaciones', 'cotizacionesMes', 'totalClientes', 'totalProductos');
    }
}
