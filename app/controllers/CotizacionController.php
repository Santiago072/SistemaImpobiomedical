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
    private ProductoModel     $productoModel;
    private ClienteModel      $clienteModel;
    private FileUploadService $uploader;
    private int $porPagina = 10;

    public function __construct(\mysqli $conexion)
    {
        $this->model         = new CotizacionModel($conexion);
        $this->productoModel = new ProductoModel($conexion);
        $this->clienteModel  = new ClienteModel($conexion);
        $this->uploader      = new FileUploadService(dirname(__DIR__, 2) . '/uploads');
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

        if (!isset($_SESSION['cotizacion_id'])) {
            $id = $this->model->buscarBorradorConItems($usuarioId)
               ?? $this->model->buscarCabeceraVacia($usuarioId)
               ?? $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
            $_SESSION['cotizacion_id'] = $id;
        }
        return (int)$_SESSION['cotizacion_id'];
    }

    private function procesarGuardarItem(int $cotizacion_id): void
    {
        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&error=csrf');
            exit();
        }

        verificar_rate_limit(20, 60, 'cot_guardar_item');

        $producto_id         = validar_numero($_POST['producto_id'] ?? '') ? (int)$_POST['producto_id'] : null;
        $titulo              = mb_substr(sanitizar_entrada($_POST['titulo'] ?? ''), 0, 255);
        $descripcion         = mb_substr(sanitizar_entrada($_POST['descripcion'] ?? ''), 0, 5000);
        $cantidad            = max(1, (int)($_POST['cantidad'] ?? 1));
        $precio              = (float)($_POST['precio'] ?? 0);
        $iva                 = mb_substr(sanitizar_entrada($_POST['iva'] ?? 'si'), 0, 5);
        $porcentaje_iva      = (float)($_POST['porcentaje_iva'] ?? 19);
        $tiempo_entrega      = mb_substr(sanitizar_entrada($_POST['tiempo_entrega'] ?? ''), 0, 120);
        $categoria           = mb_substr(sanitizar_entrada($_POST['categoria'] ?? ''), 0, 100);
        $codigo_producto     = mb_substr(sanitizar_entrada($_POST['codigo_producto'] ?? ''), 0, 60);
        $precio_proveedor    = (float)($_POST['precio_proveedor'] ?? 0);
        $porcentaje_utilidad = (float)($_POST['porcentaje_utilidad'] ?? 0);
        $flete               = (float)($_POST['flete'] ?? 0);
        $calibracion         = (float)($_POST['calibracion'] ?? 0);
        $estampillas         = (float)($_POST['estampillas'] ?? 0);
        $proveedor           = mb_substr(sanitizar_entrada($_POST['proveedor'] ?? ''), 0, 100);
        $codigo_proveedor    = mb_substr(sanitizar_entrada($_POST['codigo_proveedor'] ?? ''), 0, 60);
        
        // Validar y sanitizar calc_ops (debe ser JSON válido)
        $calc_ops_raw = $_POST['calc_ops'] ?? '{}';
        $calc_ops_decoded = json_decode($calc_ops_raw, true);
        if ($calc_ops_decoded === null) {
            $calc_ops = '{}'; // Si no es JSON válido, usar vacío
        } else {
            $calc_ops = json_encode($calc_ops_decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (!in_array($iva, ['si', 'no'], true)) {
            $iva = 'si';
        }

        $foto = $this->uploader->subir($_FILES['foto'] ?? [], $_POST['foto_actual'] ?? '');

        $this->model->insertarItem(
            $cotizacion_id, $producto_id, $titulo, $foto,
            $descripcion, $cantidad, $precio, $iva, $porcentaje_iva, $tiempo_entrega,
            $categoria, $codigo_producto, $precio_proveedor, $porcentaje_utilidad,
            $flete, $calibracion, $estampillas, $proveedor, $codigo_proveedor, $calc_ops
        );

        // Si es producto nuevo (no del catálogo), guardarlo en catálogo
        if ($producto_id === null && !$this->productoModel->existePorTitulo($titulo)) {
            $this->productoModel->crear($titulo, $foto, $descripcion, $precio, $iva, $porcentaje_iva, $categoria, $codigo_producto);
        }

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
        exit();
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
                $itemId              = (int)($_POST['item_id'] ?? 0);
                $titulo              = mb_substr(sanitizar_entrada($_POST['titulo'] ?? ''), 0, 255);
                $descripcion         = mb_substr(sanitizar_entrada($_POST['descripcion'] ?? ''), 0, 5000);
                $cantidad            = max(1, (int)($_POST['cantidad'] ?? 1));
                $precio              = (float)($_POST['precio'] ?? 0);
                $iva                 = mb_substr(sanitizar_entrada($_POST['iva'] ?? 'si'), 0, 5);
                $porcentaje_iva      = (float)($_POST['porcentaje_iva'] ?? 19);
                $tiempo_entrega      = mb_substr(sanitizar_entrada($_POST['tiempo_entrega'] ?? ''), 0, 120);
                $categoria           = mb_substr(sanitizar_entrada($_POST['categoria'] ?? ''), 0, 100);
                $codigo_producto     = mb_substr(sanitizar_entrada($_POST['codigo_producto'] ?? ''), 0, 60);
                $precio_proveedor    = (float)($_POST['precio_proveedor'] ?? 0);
                $porcentaje_utilidad = (float)($_POST['porcentaje_utilidad'] ?? 0);
                $flete               = (float)($_POST['flete'] ?? 0);
                $calibracion         = (float)($_POST['calibracion'] ?? 0);
                $estampillas         = (float)($_POST['estampillas'] ?? 0);
                $proveedor           = mb_substr(sanitizar_entrada($_POST['proveedor'] ?? ''), 0, 100);
                $codigo_proveedor    = mb_substr(sanitizar_entrada($_POST['codigo_proveedor'] ?? ''), 0, 60);
                
                // Validar y sanitizar calc_ops (debe ser JSON válido)
                $calc_ops_raw = $_POST['calc_ops'] ?? '{}';
                $calc_ops_decoded = json_decode($calc_ops_raw, true);
                if ($calc_ops_decoded === null) {
                    $calc_ops = '{}'; // Si no es JSON válido, usar vacío
                } else {
                    $calc_ops = json_encode($calc_ops_decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }

                if (!in_array($iva, ['si', 'no'], true)) {
                    $mensajeError = 'IVA no válido';
                } elseif ($cantidad <= 0 || $precio < 0) {
                    $mensajeError = 'Cantidad y precio deben ser valores válidos';
                } else {
                    $foto = $this->uploader->reemplazar($_FILES['foto'] ?? [], $_POST['foto_actual'] ?? '');
                    if ($this->model->actualizarItem($itemId, $cotizacion_id, $titulo, $foto,
                            $descripcion, $cantidad, $precio, $iva, $porcentaje_iva, $tiempo_entrega,
                            $categoria, $codigo_producto, $precio_proveedor, $porcentaje_utilidad,
                            $flete, $calibracion, $estampillas, $proveedor, $codigo_proveedor, $calc_ops)) {
                        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&updated=1');
                        exit();
                    }
                    $mensajeError = 'Error al actualizar el ítem';
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

        $fechaCreacion    = mb_substr(sanitizar_entrada($_POST['fecha_creacion'] ?? date('Y-m-d')), 0, 10);
        $diasValidez      = max(1, (int)($_POST['dias_validez'] ?? 30));
        $condicionesPago  = mb_substr(sanitizar_entrada($_POST['condiciones_pago'] ?? 'CONTADO'), 0, 100);
        $observaciones    = mb_substr(sanitizar_entrada($_POST['observaciones'] ?? ''), 0, 1000);
        $clienteId        = validar_numero($_POST['cliente_id'] ?? '') ? (int)$_POST['cliente_id'] : null;
        $clienteNombre    = mb_substr(sanitizar_entrada($_POST['cliente_nombre'] ?? ''), 0, 200);
        $clienteNit       = mb_substr(sanitizar_entrada($_POST['cliente_nit'] ?? ''), 0, 30);
        $clienteDireccion = mb_substr(sanitizar_entrada($_POST['cliente_direccion'] ?? ''), 0, 200);
        $clienteTelefono  = mb_substr(sanitizar_entrada($_POST['cliente_telefono'] ?? ''), 0, 30);
        $clienteCorreo    = mb_substr(sanitizar_entrada($_POST['cliente_correo'] ?? ''), 0, 100);
        $clienteContacto  = mb_substr(sanitizar_entrada($_POST['cliente_contacto'] ?? ''), 0, 100);
        $clienteCiudad    = mb_substr(sanitizar_entrada($_POST['cliente_ciudad'] ?? ''), 0, 100);

        if (empty($clienteNombre)) {
            $mensajeError = 'El nombre del cliente es obligatorio';
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id');
        }

        if ($clienteId === null && !empty($clienteNit)) {
            $existe = $this->clienteModel->existeNit($clienteNit);
            if (!$existe) {
                $this->clienteModel->crear($clienteNombre, $clienteNit, '', $clienteCiudad, $clienteDireccion, $clienteContacto, $clienteTelefono, $clienteCorreo);
            }
        }

        $numeroCotizacion = $this->model->finalizarCotizacion(
            $cotizacion_id, $fechaCreacion, $diasValidez, $condicionesPago, $observaciones,
            $clienteNombre, $clienteNit, $clienteDireccion, $clienteTelefono,
            $clienteCorreo, $clienteContacto, $clienteCiudad, $clienteId,
            $_SESSION['usuario_nombre'] ?? '',
            $_SESSION['usuario_cargo'] ?? '',
            $_SESSION['usuario_codigo'] ?? ''
        );

        unset($_SESSION['cotizacion_id']);

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=generar_pdf&ver=' . urlencode($numeroCotizacion));
        exit();
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
