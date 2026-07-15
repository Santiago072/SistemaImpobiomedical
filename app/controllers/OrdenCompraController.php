<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * OrdenCompraController — gestión de órdenes de compra.
 *
 * Flujo:
 *   1. seleccionar_items  — muestra los ítems de una cotización para seleccionar
 *   2. crear              — POST con los ítems seleccionados + datos del proveedor → guarda y redirige al PDF
 *   3. consultar          — lista todas las órdenes con filtros
 *   4. generar_pdf        — genera el PDF de una orden por su P.O.
 *   5. eliminar           — elimina una orden (solo admin)
 */
class OrdenCompraController
{
    private OrdenCompraModel $model;
    private CotizacionModel  $cotizacionModel;
    private int $porPagina = 10;

    public function __construct(\mysqli $conexion)
    {
        $this->model           = new OrdenCompraModel($conexion);
        $this->cotizacionModel = new CotizacionModel($conexion);
    }

    // ── PASO 1: Seleccionar ítems de la cotización ────────────────────────────

    public function seleccionarItems(): array
    {
        verificar_autenticacion();
        $csrf_token = generar_token_csrf();

        $numero = sanitizar_entrada($_GET['cotizacion'] ?? '');
        if (empty($numero)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $cotizacion = $this->cotizacionModel->buscarPorNumero($numero);
        if (!$cotizacion) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $items = $this->cotizacionModel->obtenerItems((int)$cotizacion['id']);

        // Agrupar proveedores únicos para mostrar info
        $proveedores = [];
        foreach ($items as $it) {
            $p = trim($it['proveedor'] ?? '');
            if ($p && !in_array($p, $proveedores, true)) {
                $proveedores[] = $p;
            }
        }

        return compact('cotizacion', 'items', 'proveedores', 'csrf_token');
    }

    // ── PASO 2: Guardar orden + redirigir al PDF ──────────────────────────────

    public function crear(): void
    {
        verificar_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=csrf');
            exit();
        }

        verificar_rate_limit(10, 60, 'orden_crear');

        $cotizacionId     = (int)($_POST['cotizacion_id'] ?? 0);
        $cotizacionNumero = sanitizar_entrada($_POST['cotizacion_numero'] ?? '');
        $usuarioId        = (int)$_SESSION['usuario_id'];

        // Datos del proveedor y orden
        $proveedor          = mb_substr(sanitizar_entrada($_POST['proveedor'] ?? ''), 0, 200);
        $proveedorNit       = mb_substr(sanitizar_entrada($_POST['proveedor_nit'] ?? ''), 0, 30);
        $tipoContribuyente  = mb_substr(sanitizar_entrada($_POST['tipo_contribuyente'] ?? ''), 0, 100);
        $condicionesPago    = mb_substr(sanitizar_entrada($_POST['condiciones_pago'] ?? 'Según acuerdo'), 0, 100);
        $iva                = mb_substr(sanitizar_entrada($_POST['iva'] ?? '19%'), 0, 20);
        $departamentoCompras= mb_substr(sanitizar_entrada($_POST['departamento_compras'] ?? ''), 0, 100);
        $nota               = mb_substr(sanitizar_entrada($_POST['nota'] ?? ''), 0, 1000);
        $retencion          = (float)($_POST['retencion'] ?? 0);
        $fecha              = mb_substr(sanitizar_entrada($_POST['fecha'] ?? date('Y-m-d')), 0, 10);

        if (!$cotizacionId || empty($proveedor)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=datos');
            exit();
        }

        // Ítems seleccionados
        $itemsIds = $_POST['items_seleccionados'] ?? [];
        if (empty($itemsIds)) {
            header('Location: ' . BASE_URL . '?module=ordenes&action=seleccionar_items&cotizacion='
                . urlencode($cotizacionNumero) . '&error=no_items');
            exit();
        }

        // Validar que todos los ítems sean del mismo proveedor
        $itemsData = $_POST['items_data'] ?? [];
        $proveedoresSeleccionados = [];
        foreach ($itemsIds as $itemId) {
            $d = $itemsData[(int)$itemId] ?? [];
            $prov = trim(sanitizar_entrada($d['proveedor'] ?? ''));
            if ($prov && !in_array($prov, $proveedoresSeleccionados, true)) {
                $proveedoresSeleccionados[] = $prov;
            }
        }
        if (count($proveedoresSeleccionados) > 1) {
            header('Location: ' . BASE_URL . '?module=ordenes&action=seleccionar_items&cotizacion='
                . urlencode($cotizacionNumero) . '&error=proveedor_mixto');
            exit();
        }

        // Crear la orden
        $ordenId = $this->model->crearOrden(
            $cotizacionId, $cotizacionNumero, $usuarioId,
            $proveedor, $proveedorNit, $tipoContribuyente,
            $condicionesPago, $iva, $departamentoCompras,
            $nota, $retencion, $fecha
        );

        // Insertar los ítems seleccionados
        $itemsData = $_POST['items_data'] ?? [];
        foreach ($itemsIds as $itemId) {
            $itemId = (int)$itemId;
            $d      = $itemsData[$itemId] ?? [];

            $codigoProveedor = mb_substr(sanitizar_entrada($d['codigo_proveedor'] ?? ''), 0, 60);
            $titulo          = mb_substr(sanitizar_entrada($d['titulo'] ?? ''), 0, 255);
            $descripcion     = mb_substr(sanitizar_entrada($d['descripcion'] ?? ''), 0, 2000);
            $cantidad        = max(1, (int)($d['cantidad'] ?? 1));
            $precioUnit      = (float)($d['precio'] ?? 0);
            $ivaItem         = sanitizar_entrada($d['iva'] ?? 'si');
            $pctIva          = (float)($d['porcentaje_iva'] ?? 19);

            $this->model->insertarItem(
                $ordenId, $itemId, $codigoProveedor,
                $titulo, $descripcion, $cantidad,
                $precioUnit, $ivaItem, $pctIva
            );
        }

        // Redirigir al PDF
        header('Location: ' . BASE_URL . '?module=ordenes&action=generar_pdf&id=' . $ordenId);
        exit();
    }

    // ── CONSULTAR órdenes ─────────────────────────────────────────────────────

    public function consultar(): array
    {
        verificar_autenticacion();
        $csrf_token   = generar_token_csrf();
        $ordenes      = [];
        $totalPaginas = 0;
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;

        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol       = $_SESSION['rol'] ?? 'usuario';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filtros = [];
            if (!empty($_POST['proveedor']))          $filtros['proveedor']          = sanitizar_entrada($_POST['proveedor']);
            if (!empty($_POST['numero_po']))           $filtros['numero_po']           = sanitizar_entrada($_POST['numero_po']);
            if (!empty($_POST['cotizacion_numero']))   $filtros['cotizacion_numero']   = sanitizar_entrada($_POST['cotizacion_numero']);
            if (!empty($_POST['fecha']))               $filtros['fecha']               = sanitizar_entrada($_POST['fecha']);
            $_SESSION['orden_filtros'] = $filtros;
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        if (isset($_GET['limpiar'])) {
            unset($_SESSION['orden_filtros']);
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $filtros      = $_SESSION['orden_filtros'] ?? [];
        $total        = $this->model->contarConFiltros($filtros, $usuarioId, $rol);
        $totalPaginas = (int)ceil($total / $this->porPagina);
        $ordenes      = $this->model->listarConFiltros($filtros, $offset, $this->porPagina, $usuarioId, $rol);

        $busquedaProveedor  = $filtros['proveedor'] ?? '';
        $busquedaPO         = $filtros['numero_po'] ?? '';
        $busquedaCotizacion = $filtros['cotizacion_numero'] ?? '';
        $busquedaFecha      = $filtros['fecha'] ?? '';

        return compact('ordenes', 'csrf_token', 'paginaActual', 'totalPaginas',
                       'busquedaProveedor', 'busquedaPO', 'busquedaCotizacion', 'busquedaFecha');
    }

    // ── GENERAR PDF ───────────────────────────────────────────────────────────

    public function generarPdf(): array
    {
        verificar_autenticacion();
        verificar_rate_limit(15, 60, 'orden_pdf');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $orden = $this->model->buscarPorId($id);
        if (!$orden) {
            http_response_code(404);
            die('Orden de compra no encontrada.');
        }

        $items  = $this->model->obtenerItems($id);
        $forzar = isset($_GET['descargar']);

        return compact('orden', 'items', 'forzar');
    }

    // ── ELIMINAR ──────────────────────────────────────────────────────────────

    public function eliminar(): void
    {
        verificar_admin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->model->eliminar($id);
        }
        header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
        exit();
    }
}
