<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * EstadisticaController — coordina la extracción de datos analíticos para la vista.
 * Exclusivo para el rol 'admin'.
 */
class EstadisticaController
{
    private EstadisticaModel $model;

    public function __construct(\mysqli $conexion)
    {
        $this->model = new EstadisticaModel($conexion);
    }

    public function index(): array
    {
        verificar_admin(); // Solo administradores

        $kpis = $this->model->getKpisGenerales();
        $topClientes = $this->model->getTopClientes();
        $topProductos = $this->model->getTopProductos();
        $evolucion = $this->model->getMetricasEvolucion();

        return compact('kpis', 'topClientes', 'topProductos', 'evolucion');
    }
}
