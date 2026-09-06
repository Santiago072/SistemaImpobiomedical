<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * PanelController — Dashboard con métricas del sistema.
 * - SRP: solo genera datos para el panel principal.
 */
class PanelController
{
    private CotizacionModel $model;
    private ProductoModel   $productoModel;

    public function __construct(\PDO $conexion, ?CotizacionModel $model = null, ?ProductoModel $productoModel = null)
    {
        $this->model         = $model ?? new CotizacionModel($conexion);
        $this->productoModel = $productoModel ?? new ProductoModel($conexion);
    }

    public function index(): array
    {
        verificar_autenticacion();

        $usuarioId  = (int)$_SESSION['usuario_id'];
        $rol        = $_SESSION['rol'] ?? 'usuario';

        if ($rol === 'admin') {
            $totalCotizaciones    = $this->model->contarTotal();
            $cotizacionesMes      = $this->model->contarDelMes();
        } else {
            $totalCotizaciones    = $this->model->contarDelUsuario($usuarioId);
            $cotizacionesMes      = $this->model->contarMesDelUsuario($usuarioId);
        }
        $totalClientes        = $this->model->contarTotalClientes();
        $totalProductos       = $this->model->contarTotalProductos();
        
        // Obtener las cotizaciones más recientes
        $cotizacionesRecientes = $this->model->buscarConFiltros([], 0, 5, $usuarioId, $rol);

        // Obtener los productos más recientes del catálogo
        $ultimosProductos = $this->productoModel->listar(0, 5);

        return compact('totalCotizaciones', 'cotizacionesMes', 'totalClientes', 'totalProductos', 'cotizacionesRecientes', 'ultimosProductos');
    }
}

