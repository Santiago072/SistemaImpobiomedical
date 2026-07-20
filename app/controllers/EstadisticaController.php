<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * EstadisticaController — coordina la extracción de datos analíticos.
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
        verificar_admin();

        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin    = $_GET['fecha_fin']    ?? null;

        $kpis          = $this->model->getKpisGenerales($fechaInicio, $fechaFin);
        $topClientes   = $this->model->getTopClientes(5, $fechaInicio, $fechaFin);
        $topProductos  = $this->model->getTopProductos(5, $fechaInicio, $fechaFin);
        $topVendedores = $this->model->getTopVendedores(5, $fechaInicio, $fechaFin);
        $evolucion     = $this->model->getMetricasEvolucion($fechaInicio, $fechaFin);

        return compact('kpis', 'topClientes', 'topProductos', 'topVendedores', 'evolucion', 'fechaInicio', 'fechaFin');
    }

    public function reportePdf(): void
    {
        verificar_admin();

        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin    = $_GET['fecha_fin']    ?? null;

        $data = $this->model->getDatosReporte($fechaInicio, $fechaFin);
        extract($data); // $kpis, $topClientes, $topProductos, $topVendedores, $evolucion

        include __DIR__ . '/../views/estadisticas/reporte_pdf.php';
        exit();
    }
}
