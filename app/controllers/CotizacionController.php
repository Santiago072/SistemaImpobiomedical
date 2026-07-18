<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * CotizacionController — lógica principal del módulo de cotizaciones.
 *
 * Principios:
 *   - SRP: archivos → FileUploadService, datos → modelos.
 *   - Número de cotización: código usuario + consecutivo mensual (EB01, EB02...).
 *   - El usuario puede buscar cliente del catálogo O ingresar datos manualmente.
 *   - Los días de validez los ingresa el usuario.
 */
class CotizacionController
{
    private CotizacionModel   $model;
    private FileUploadService $uploader;
    private ItemCotizacionService $itemService;
    private FinalizarCotizacionService $finalizarService;
    private int $porPagina = 10;

    public function __construct(\mysqli $conexion)
    {
        $this->model            = new CotizacionModel($conexion);
        $this->productoModel    = new ProductoModel($conexion);
        $this->clienteModel     = new ClienteModel($conexion);
        $this->uploader         = new FileUploadService(dirname(__DIR__, 2) . '/uploads');
        $this->itemService      = new ItemCotizacionService($this->model, $this->productoModel, $this->uploader);
        $this->finalizarService = new FinalizarCotizacionService($this->model, $this->clienteModel);
    }

    // ── CREAR / GESTIONAR ÍTEMS ───────────────────────────────────────────────
    public function crear(): array
    {
        verificar_autenticacion();

        $csrf_token = generar_token_csrf();
        $busqueda   = sanitizar_entrada($_GET['busqueda'] ?? '');
        $productos  = $this->productoModel->listarTodos($busqueda);

        $producto = null;
        if (validar_numero($_GET['producto_id'] ?? '')) {
            $producto = $this->productoModel->buscarPorId((int)$_GET['producto_id']);
        }

        $cotizacion_id = $this->recuperarOCrearBorrador();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
            ($_POST['action'] ?? '') === 'guardar_item') {
            $this->procesarGuardarItem($cotizacion_id);
        }

        $items      = $this->model->obtenerItems($cotizacion_id);
        $totalItems = count($items);

        $mensajeExito = '';
        if (isset($_GET['updated'])) $mensajeExito = 'Ítem actualizado';

        return compact('productos', 'producto', 'busqueda', 'cotizacion_id',
                       'items', 'totalItems', 'csrf_token', 'mensajeExito');
    }

    private function recuperarOCrearBorrador(): int
    {
        $usuarioId    = (int)$_SESSION['usuario_id'];
        $usuarioCodigo = $_SESSION['usuario_codigo'] ?? 'COT';
        $asesorNombre = $_SESSION['usuario_nombre'] ?? '';
        $asesorCargo  = $_SESSION['usuario_cargo'] ?? '';

        error_log("DEBUG: recuperarOCrearBorrador - Usuario ID: $usuarioId, Session cotizacion_id: " . ($_SESSION['cotizacion_id'] ?? 'no set'));

        if (!isset($_SESSION['cotizacion_id'])) {
            error_log("DEBUG: No hay cotizacion_id en sesión, buscando borrador...");
            $id = $this->model->buscarBorradorConItems($usuarioId);
            error_log("DEBUG: buscarBorradorConItems retornó: " . ($id ?? 'null'));
            
            if ($id === null) {
                $id = $this->model->buscarCabeceraVacia($usuarioId);
                error_log("DEBUG: buscarCabeceraVacia retornó: " . ($id ?? 'null'));
            }
            
            if ($id === null) {
                $id = $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
                error_log("DEBUG: crearCabecera retornó: $id");
            }
            
            $_SESSION['cotizacion_id'] = $id;
            error_log("DEBUG: Se estableció cotizacion_id en sesión: $id");
        } else {
            // Verificar que la cotización en sesión todavía existe y es válida
            $cotizacionExistente = $this->model->buscarPorId((int)$_SESSION['cotizacion_id']);
            error_log("DEBUG: Cotización en sesión existe: " . ($cotizacionExistente ? 'si' : 'no') . ", estado: " . ($cotizacionExistente['estado'] ?? 'n/a'));
            
            if (!$cotizacionExistente || $cotizacionExistente['estado'] !== 'borrador') {
                error_log("DEBUG: Cotización inválida, buscando nueva...");
                // Si no existe o no es borrador, buscar uno nuevo
                $id = $this->model->buscarBorradorConItems($usuarioId)
                   ?? $this->model->buscarCabeceraVacia($usuarioId)
                   ?? $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
                $_SESSION['cotizacion_id'] = $id;
                error_log("DEBUG: Se estableció nuevo cotizacion_id: $id");
            }
        }
        
        error_log("DEBUG: Retornando cotizacion_id: " . $_SESSION['cotizacion_id']);
        return (int)$_SESSION['cotizacion_id'];
    }

    private function procesarGuardarItem(int $cotizacion_id): void
    {
        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&error=csrf');
            exit();
        }

        verificar_rate_limit(20, 60, 'cot_guardar_item');

        try {
            $this->itemService->guardarItem($cotizacion_id, $_POST, $_FILES);
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        } catch (\Exception $e) {
            error_log('Error en procesarGuardarItem: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&error=' . urlencode($e->getMessage()));
            exit();
        }
    }

    // ── EDITAR ÍTEM ───────────────────────────────────────────────────────────
    public function verRespaldo(): array
    {
        verificar_autenticacion();
        $numero = $_GET['numero'] ?? '';
        if (empty($numero)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $cotizacion = $this->model->buscarPorNumero($numero);
        if (!$cotizacion) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $items = $this->model->obtenerItems((int)$cotizacion['id']);
        $csrf_token = generar_token_csrf();
        return ['cotizacion' => $cotizacion, 'items' => $items, 'csrf_token' => $csrf_token];
    }

    public function editarItem(): array
    {
        verificar_autenticacion();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if (!isset($_SESSION['cotizacion_id'])) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }
        $cotizacion_id = (int)$_SESSION['cotizacion_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verificar_rate_limit(15, 60, 'cot_editar_item');
            if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
                $mensajeError = 'Token de seguridad inválido';
            } else {
                try {
                    $itemId = (int)($_POST['item_id'] ?? 0);
                    if ($this->itemService->actualizarItem($itemId, $cotizacion_id, $_POST, $_FILES)) {
                        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&updated=1');
                        exit();
                    } else {
                        $mensajeError = 'Error al actualizar el ítem';
                    }
                } catch (\InvalidArgumentException $e) {
                    $mensajeError = $e->getMessage();
                } catch (\Exception $e) {
                    $mensajeError = 'Error inesperado al actualizar el ítem';
                    error_log('Error en editarItem: ' . $e->getMessage());
                }
            }
        }

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $itemId = (int)$_GET['id'];
        $datos  = $this->model->buscarItemPorId($itemId, $cotizacion_id);
        if (!$datos) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        return compact('datos', 'mensajeError', 'csrf_token');
    }

    // ── ELIMINAR ÍTEM ─────────────────────────────────────────────────────────
    public function eliminarItem(): void
    {
        verificar_autenticacion();

        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!validar_numero($_GET['id'] ?? '')) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $this->model->eliminarItem((int)$_GET['id']);

        if ($esAjax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit();
        }
        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
        exit();
    }

    // ── ELIMINAR COTIZACIÓN (Solo Admin) ──────────────────────────────────────
    public function eliminar(): void
    {
        verificar_admin();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $this->model->eliminar((int)$_GET['id']);
        header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
        exit();
    }

    // ── FINALIZAR (Completar datos del cliente y generar número) ─────────────
    public function finalizar(): array
    {
        verificar_autenticacion();

        $csrf_token   = generar_token_csrf();
        $mensajeError = '';

        if (!isset($_SESSION['cotizacion_id'])) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $cotizacion_id = (int)$_SESSION['cotizacion_id'];
        $items         = $this->model->obtenerItems($cotizacion_id);

        if (empty($items)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&error=no_items');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id');
        }

        verificar_rate_limit(10, 60, 'cot_finalizar');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id');
        }

        $clienteNombre = mb_substr(sanitizar_entrada($_POST['cliente_nombre'] ?? ''), 0, 200);
        if (empty($clienteNombre)) {
            $mensajeError = 'El nombre del cliente es obligatorio';
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id');
        }

        try {
            $numeroCotizacion = $this->finalizarService->procesarFinalizacion($cotizacion_id, $_POST, $_SESSION);
            unset($_SESSION['cotizacion_id']);

            header('Location: ' . BASE_URL . '?module=cotizaciones&action=generar_pdf&ver=' . urlencode($numeroCotizacion));
            exit();
        } catch (\Exception $e) {
            error_log('Error al finalizar cotización: ' . $e->getMessage());
            $mensajeError = 'Error inesperado al finalizar la cotización';
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id');
        }
    }

    // ── CONSULTAR ─────────────────────────────────────────────────────────────
    public function consultar(): array
    {
        verificar_autenticacion();
        $mensajeError = '';
        $csrf_token   = generar_token_csrf();
        $cotizaciones = [];
        $totalPaginas = 0;
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;
        $busquedaFecha   = '';
        $busquedaCliente = '';
        $busquedaNumero  = '';

        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol       = $_SESSION['rol'] ?? 'usuario';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filtros = [];
            if (!empty($_POST['fecha']))             $filtros['fecha']             = sanitizar_entrada($_POST['fecha']);
            if (!empty($_POST['nombre_cliente']))    $filtros['nombre_cliente']    = sanitizar_entrada($_POST['nombre_cliente']);
            if (!empty($_POST['numero_cotizacion'])) $filtros['numero_cotizacion'] = sanitizar_entrada($_POST['numero_cotizacion']);

            $_SESSION['cotizacion_filtros'] = $filtros;
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        if (isset($_GET['limpiar'])) {
            unset($_SESSION['cotizacion_filtros']);
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $filtros = $_SESSION['cotizacion_filtros'] ?? [];
        $busquedaFecha   = $filtros['fecha'] ?? '';
        $busquedaCliente = $filtros['nombre_cliente'] ?? '';
        $busquedaNumero  = $filtros['numero_cotizacion'] ?? '';

        $total        = $this->model->contarConFiltros($filtros, $usuarioId, $rol);
        $totalPaginas = (int)ceil($total / $this->porPagina);
        $cotizaciones = $this->model->buscarConFiltros($filtros, $offset, $this->porPagina, $usuarioId, $rol);

        return compact('cotizaciones', 'csrf_token', 'mensajeError', 'busquedaFecha', 'busquedaCliente',
                       'busquedaNumero', 'paginaActual', 'totalPaginas');
    }

    // ── GENERAR PDF ───────────────────────────────────────────────────────────
    public function generarPdf(): array
    {
        verificar_autenticacion();
        verificar_rate_limit(15, 60, 'generar_pdf');

        if (!isset($_GET['ver'])) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $numero     = sanitizar_entrada($_GET['ver']);
        $forzar     = isset($_GET['descargar']);
        $cotizacion = $this->model->buscarPorNumero($numero);

        if (!$cotizacion) {
            http_response_code(404);
            die('Cotización no encontrada.');
        }

        return [
            'cotizacion' => $cotizacion,
            'items'      => $this->model->obtenerItems((int)$cotizacion['id']),
            'forzar'     => $forzar,
        ];
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────
    public function ajaxBuscarProductos(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(100, 60, 'ajax_productos');
        header('Content-Type: application/json');
        $busqueda = sanitizar_entrada($_GET['busqueda'] ?? '');
        $productos = $this->productoModel->listarTodos($busqueda);
        echo json_encode(['status' => 'success', 'data' => $productos]);
        exit();
    }

    public function ajaxGetProducto(): void
    {
        verificar_autenticacion();
        header('Content-Type: application/json');
        if (!validar_numero($_GET['id'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit();
        }
        $producto = $this->productoModel->buscarPorId((int)$_GET['id']);
        echo json_encode($producto
            ? ['status' => 'success', 'data' => $producto]
            : ['status' => 'error', 'message' => 'Producto no encontrado']);
        exit();
    }

    public function ajaxBuscarClientes(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(100, 60, 'ajax_clientes_cot');
        header('Content-Type: application/json');
        $busqueda = sanitizar_entrada($_GET['q'] ?? '');
        $clientes = $this->clienteModel->buscarParaSelect($busqueda);
        echo json_encode(['status' => 'success', 'data' => $clientes]);
        exit();
    }

    public function ajaxGetCliente(): void
    {
        verificar_autenticacion();
        header('Content-Type: application/json');
        if (!validar_numero($_GET['id'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit();
        }
        $cliente = $this->clienteModel->buscarPorId((int)$_GET['id']);
        echo json_encode($cliente
            ? ['status' => 'success', 'data' => $cliente]
            : ['status' => 'error', 'message' => 'Cliente no encontrado']);
        exit();
    }
}
