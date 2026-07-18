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

        // Filtros opcionales
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin    = $_GET['fecha_fin'] ?? null;

        $kpis = $this->model->getKpisGenerales($fechaInicio, $fechaFin);
        $topClientes = $this->model->getTopClientes();
        $topProductos = $this->model->getTopProductos();
        $topVendedores = $this->model->getTopVendedores();
        $evolucion = $this->model->getMetricasEvolucion();

        return compact('kpis', 'topClientes', 'topProductos', 'topVendedores', 'evolucion', 'fechaInicio', 'fechaFin');
    }
}
