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

    public function __construct(\PDO $conexion)
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

        // Blindaje backend: solo permitir emitir órdenes de cotizaciones en estado comercial 'pendiente'
        $estCom = $cotizacion['estado_comercial'] ?? 'pendiente';
        if ($estCom !== 'pendiente') {
            $_SESSION['flash_error'] = 'No es posible generar una orden de compra para una cotización que está ' . $estCom . '.';
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

        if (!$cotizacionId) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=datos');
            exit();
        }

        // Blindaje backend en guardado: validar que la cotización exista y esté pendiente
        $cotizacion = $this->cotizacionModel->buscarPorId($cotizacionId);
        if (!$cotizacion) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=no_existe');
            exit();
        }

        $estCom = $cotizacion['estado_comercial'] ?? 'pendiente';
        if ($estCom !== 'pendiente') {
            $_SESSION['flash_error'] = 'No es posible generar una orden de compra para una cotización ' . $estCom . '.';
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

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
        
        $bancoNombre        = mb_substr(sanitizar_entrada($_POST['banco_nombre'] ?? ''), 0, 100);
        $bancoCuenta        = mb_substr(sanitizar_entrada($_POST['banco_cuenta'] ?? ''), 0, 100);
        $bancoTipoCuenta    = mb_substr(sanitizar_entrada($_POST['banco_tipo_cuenta'] ?? ''), 0, 100);

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
            $nota, $retencion, $fecha,
            $bancoNombre, $bancoCuenta, $bancoTipoCuenta
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

        // Manejo de pestaña activa (pendientes o completadas)
        $tabActual = sanitizar_entrada($_GET['tab'] ?? ($_SESSION['orden_tab'] ?? 'pendientes'));
        if (!in_array($tabActual, ['pendientes', 'completadas'], true)) {
            $tabActual = 'pendientes';
        }
        $_SESSION['orden_tab'] = $tabActual;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filtros = [];
            if (!empty($_POST['proveedor']))          $filtros['proveedor']          = sanitizar_entrada($_POST['proveedor']);
            if (!empty($_POST['cotizacion_numero']))   $filtros['cotizacion_numero']   = sanitizar_entrada($_POST['cotizacion_numero']);
            if (!empty($_POST['fecha_inicio']))        $filtros['fecha_inicio']        = sanitizar_entrada($_POST['fecha_inicio']);
            if (!empty($_POST['fecha_fin']))           $filtros['fecha_fin']           = sanitizar_entrada($_POST['fecha_fin']);
            $_SESSION['orden_filtros'] = $filtros;
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar&tab=' . $tabActual);
            exit();
        }

        if (isset($_GET['limpiar'])) {
            unset($_SESSION['orden_filtros']);
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar&tab=' . $tabActual);
            exit();
        }

        $filtros = $_SESSION['orden_filtros'] ?? [];
        $filtros['estado'] = ($tabActual === 'completadas') ? 'completada' : 'pendiente';

        $total        = $this->model->contarConFiltros($filtros, $usuarioId, $rol);
        $totalPaginas = (int)ceil($total / $this->porPagina);
        $ordenes      = $this->model->listarConFiltros($filtros, $offset, $this->porPagina, $usuarioId, $rol);

        // Conteos para los badges de las pestañas
        $filtrosP = $filtros;
        $filtrosP['estado'] = 'pendiente';
        $conteoPendientes = $this->model->contarConFiltros($filtrosP, $usuarioId, $rol);

        $filtrosC = $filtros;
        $filtrosC['estado'] = 'completada';
        $conteoCompletadas = $this->model->contarConFiltros($filtrosC, $usuarioId, $rol);

        $busquedaProveedor   = $filtros['proveedor'] ?? '';
        $busquedaCotizacion  = $filtros['cotizacion_numero'] ?? '';
        $busquedaFechaInicio = $filtros['fecha_inicio'] ?? '';
        $busquedaFechaFin    = $filtros['fecha_fin'] ?? '';

        return compact('ordenes', 'csrf_token', 'paginaActual', 'totalPaginas', 'tabActual',
                       'conteoPendientes', 'conteoCompletadas',
                       'busquedaProveedor', 'busquedaCotizacion', 'busquedaFechaInicio', 'busquedaFechaFin');
    }

    // ── CAMBIAR ESTADO ORDEN (AJAX) ───────────────────────────────────────────

    public function cambiarEstado(): void
    {
        verificar_autenticacion();
        $rol = $_SESSION['rol'] ?? 'usuario';
        if (!in_array($rol, ['admin', 'compras'], true)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para modificar el estado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit();
        }

        verificar_rate_limit(30, 60, 'cambiar_estado_orden');

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido o expirado']);
            exit();
        }

        $id          = (int)($_POST['id'] ?? 0);
        $nuevoEstado = sanitizar_entrada($_POST['estado'] ?? '');

        if ($id <= 0 || !in_array($nuevoEstado, ['pendiente', 'completada'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit();
        }

        $ok = $this->model->actualizarEstado($id, $nuevoEstado);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Estado actualizado correctamente' : 'Error al actualizar el estado'
        ]);
        exit();
    }

    // ── EXPORTAR REPORTE PDF DE ÓRDENES SELECCIONADAS ─────────────────────────

    public function exportarPdf(): void
    {
        verificar_autenticacion();
        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol       = $_SESSION['rol'] ?? 'usuario';

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            $_SESSION['flash_error'] = 'Debe seleccionar al menos una orden para generar el reporte en PDF.';
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $ordenes = $this->model->listarPorIds($ids, $usuarioId, $rol);
        $datosPdf = $this->prepararDatosReporte($ordenes);

        while (ob_get_level() > 0) { ob_end_clean(); }
        include dirname(__DIR__, 2) . '/app/views/ordenes/reporte_pdf.php';
        exit();
    }

    // ── EXPORTAR EXCEL DE ÓRDENES SELECCIONADAS ───────────────────────────────

    public function exportarExcel(): void
    {
        verificar_autenticacion();
        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol       = $_SESSION['rol'] ?? 'usuario';

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            $_SESSION['flash_error'] = 'Debe seleccionar al menos una orden para exportar a Excel.';
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $ordenes = $this->model->listarPorIds($ids, $usuarioId, $rol);
        $datosExcel = $this->prepararDatosReporte($ordenes);

        while (ob_get_level() > 0) { ob_end_clean(); }
        include dirname(__DIR__, 2) . '/app/views/ordenes/reporte_excel.php';
        exit();
    }

    private function prepararDatosReporte(array $ordenes): array
    {
        if (empty($ordenes)) {
            return [];
        }

        $ordenIds = array_column($ordenes, 'id');
        $itemsAgrupados = $this->model->obtenerItemsPorOrdenIds($ordenIds);

        $datos = [];
        foreach ($ordenes as $ord) {
            $ordId = (int)$ord['id'];
            $items = $itemsAgrupados[$ordId] ?? [];
            $subtotal = 0;
            $totalIva = 0;
            foreach ($items as $it) {
                $pu     = (float)$it['precio_unit'];
                $qty    = (int)$it['cantidad'];
                $pct    = (float)($it['porcentaje_iva'] ?? 19);
                $aplica = strtolower($it['iva']) === 'si';
                $sub    = $pu * $qty;
                $subtotal += $sub;
                $totalIva += $aplica ? $sub * ($pct / 100) : 0;
            }
            $retencion = $subtotal * ((float)$ord['retencion'] / 100);
            $valorPagar = $subtotal + $totalIva - $retencion;

            $datos[] = [
                'proveedor'         => $ord['proveedor'],
                'numero_po'         => $ord['numero_po'],
                'banco_nombre'      => $ord['banco_nombre'] ?? '',
                'banco_cuenta'      => $ord['banco_cuenta'] ?? '',
                'banco_tipo_cuenta' => $ord['banco_tipo_cuenta'] ?? '',
                'nit'               => $ord['proveedor_nit'],
                'valor_pagar'       => $valorPagar,
                'cliente'           => $ord['cliente_nombre'] ?? '',
                'estado'            => $ord['estado'] ?? 'pendiente',
                'fecha'             => $ord['fecha'] ?? ''
            ];
        }
        return $datos;
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
        verificar_rate_limit(10, 60, 'orden_eliminar');

        $token = $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            $_SESSION['flash_error'] = 'Token de seguridad inválido o sesión expirada.';
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'Identificador de orden inválido.';
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $orden = $this->model->buscarPorId($id);
        if (!$orden) {
            $_SESSION['flash_error'] = 'La orden de compra que intenta eliminar no existe o ya fue removida.';
            header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
            exit();
        }

        $ok = $this->model->eliminar($id);
        if ($ok) {
            $_SESSION['flash_success'] = 'La orden de compra P.O. ' . (int)$orden['numero_po'] . ' fue eliminada correctamente.';
        } else {
            $_SESSION['flash_error'] = 'No se pudo eliminar la orden de compra en la base de datos.';
        }

        header('Location: ' . BASE_URL . '?module=ordenes&action=consultar');
        exit();
    }
}

