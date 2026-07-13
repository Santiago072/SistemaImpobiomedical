<?php
ob_start();
/**
 * index.php — Front Controller / Router principal del Sistema Impobiomedical.
 *
 * Único punto de entrada del sistema MVC. Lee el parámetro ?module= de la URL,
 * valida, instancia el controlador correspondiente y renderiza la vista.
 *
 * Principios aplicados:
 *   - OCP: el mapa de rutas ($rutasMap) permite añadir módulos sin modificar la lógica del router.
 *   - SRP: la carga del .env está delegada a EnvLoader.
 *   - DRY: eliminada lógica duplicada de carga del .env.
 *
 * Rutas disponibles:
 *   (sin params)                                   → Login
 *   ?action=logout                                 → Cerrar sesión
 *   ?module=panel                                  → Dashboard
 *   ?module=usuarios&action=lista|crear|editar|eliminar|reset_password
 *   ?module=clientes&action=lista|crear|editar|eliminar
 *   ?module=productos&action=lista|crear|editar|eliminar
 *   ?module=cotizaciones&action=crear|consultar|editar_item|eliminar_item|generar_pdf|ajax_*|finalizar
 */

// ── Producción: errores al log, nunca al usuario ──────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// ── Manejador Global de Excepciones ──────────────────────────────────────────
set_exception_handler(function (Throwable $e) {
    error_log((string)$e);
    http_response_code(500);
    $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor.']);
    } else {
        echo "<h1>500 - Error Interno</h1><p>Ocurrió un error inesperado.</p>";
    }
    exit();
});

// ── Carga del .env ────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/EnvLoader.php';
EnvLoader::load(__DIR__ . '/config/.env');

// ── URL base dinámica ─────────────────────────────────────────────────────────
if (!defined('BASE_URL')) {
    $appBase = $_ENV['APP_BASE'] ?? getenv('APP_BASE') ?: null;
    if ($appBase) {
        define('BASE_URL', rtrim($appBase, '/') . '/');
    } else {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        define('BASE_URL', rtrim($scriptDir, '/') . '/');
    }
}

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/seguridad.php';

iniciar_sesion_segura();

$module = sanitizar_entrada($_GET['module'] ?? '');
$action = sanitizar_entrada($_GET['action'] ?? '');

// ── Logout ────────────────────────────────────────────────────────────────────
if ($action === 'logout') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    (new AuthController(conexion()))->logout();
    exit();
}

// ── Login (sin módulo) ────────────────────────────────────────────────────────
if ($module === '') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $data = (new AuthController(conexion()))->login();
    extract($data);
    include __DIR__ . '/app/views/auth/login.php';
    exit();
}

// ── Mapa de módulos (OCP) ─────────────────────────────────────────────────────
$rutasMap = [
    'panel'        => __DIR__ . '/app/controllers/PanelController.php',
    'usuarios'     => __DIR__ . '/app/controllers/UsuarioController.php',
    'clientes'     => __DIR__ . '/app/controllers/ClienteController.php',
    'productos'    => __DIR__ . '/app/controllers/ProductoController.php',
    'cotizaciones' => __DIR__ . '/app/controllers/CotizacionController.php',
];

if (!array_key_exists($module, $rutasMap)) {
    header('Location: ' . BASE_URL);
    exit();
}

require_once $rutasMap[$module];
$db = conexion();

// ── Dispatch por módulo ───────────────────────────────────────────────────────

if ($module === 'panel') {
    $ctrl = new PanelController($db);
    $data = $ctrl->index();
    extract($data);
    include __DIR__ . '/app/views/panel/index.php';
    exit();
}

if ($module === 'usuarios') {
    $ctrl = new UsuarioController($db);
    switch ($action) {
        case 'eliminar':
            $ctrl->eliminar();
            break;
        case 'reset_password':
            $ctrl->resetPassword();
            break;
        case 'crear':
        case 'editar':
            $dataForm = $action === 'crear' ? $ctrl->crear() : $ctrl->editar();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($dataForm['mensajeError'])) {
                $dataList = $ctrl->listar();
                $data = array_merge($dataList, $dataForm);
                $urlBase = BASE_URL . '?module=usuarios&action=lista';
                extract($data);
                include __DIR__ . '/app/views/usuarios/lista.php';
            } else {
                header('Location: ' . BASE_URL . '?module=usuarios');
            }
            break;
        default:
            $data    = $ctrl->listar();
            $urlBase = BASE_URL . '?module=usuarios&action=lista'
                     . (!empty($data['busqueda']) ? '&busqueda=' . urlencode($data['busqueda']) : '');
            extract($data);
            include __DIR__ . '/app/views/usuarios/lista.php';
    }
    exit();
}

if ($module === 'clientes') {
    $ctrl = new ClienteController($db);
    switch ($action) {
        case 'eliminar':
            $ctrl->eliminar();
            break;
        case 'ajax_buscar':
            $ctrl->ajaxBuscar();
            break;
        case 'ajax_get':
            $ctrl->ajaxGet();
            break;
        case 'crear':
        case 'editar':
            $dataForm = $action === 'crear' ? $ctrl->crear() : $ctrl->editar();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($dataForm['mensajeError'])) {
                $dataList = $ctrl->listar();
                $data = array_merge($dataList, $dataForm);
                $urlBase = BASE_URL . '?module=clientes';
                extract($data);
                include __DIR__ . '/app/views/clientes/lista.php';
            } else {
                header('Location: ' . BASE_URL . '?module=clientes');
            }
            break;
        default:
            $data    = $ctrl->listar();
            $urlBase = BASE_URL . '?module=clientes'
                     . (!empty($data['busqueda']) ? '&busqueda=' . urlencode($data['busqueda']) : '');
            extract($data);
            include __DIR__ . '/app/views/clientes/lista.php';
    }
    exit();
}

if ($module === 'productos') {
    $ctrl = new ProductoController($db);
    switch ($action) {
        case 'eliminar':
            $ctrl->eliminar();
            break;
        case 'crear':
        case 'editar':
            $dataForm = $action === 'crear' ? $ctrl->crear() : $ctrl->editar();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($dataForm['mensajeError'])) {
                $dataList = $ctrl->listar();
                $data = array_merge($dataList, $dataForm);
                $urlBase = BASE_URL . '?module=productos';
                extract($data);
                include __DIR__ . '/app/views/productos/lista.php';
            } else {
                header('Location: ' . BASE_URL . '?module=productos');
            }
            break;
        default:
            $data    = $ctrl->listar();
            $urlBase = BASE_URL . '?module=productos'
                     . (!empty($data['busqueda']) ? '&busqueda=' . urlencode($data['busqueda']) : '');
            extract($data);
            include __DIR__ . '/app/views/productos/lista.php';
    }
    exit();
}

if ($module === 'cotizaciones') {
    $ctrl = new CotizacionController($db);
    switch ($action) {
        case 'consultar':
            $data    = $ctrl->consultar();
            $urlBase = BASE_URL . '?module=cotizaciones&action=consultar&buscando=1';
            extract($data);
            include __DIR__ . '/app/views/cotizaciones/consultar.php';
            break;
        case 'finalizar':
            $data = $ctrl->finalizar();
            extract($data);
            include __DIR__ . '/app/views/cotizaciones/finalizar.php';
            break;
        case 'editar_item':
            $data = $ctrl->editarItem();
            extract($data);
            include __DIR__ . '/app/views/cotizaciones/editar_item.php';
            break;
        case 'eliminar_item':
            $ctrl->eliminarItem();
            break;
        case 'generar_pdf':
            include __DIR__ . '/app/views/cotizaciones/generar_pdf.php';
            break;
        case 'ver_respaldo':
            $data = $ctrl->verRespaldo();
            extract($data);
            include __DIR__ . '/app/views/cotizaciones/respaldo.php';
            break;
        case 'ajax_buscar_productos':
            $ctrl->ajaxBuscarProductos();
            break;
        case 'ajax_get_producto':
            $ctrl->ajaxGetProducto();
            break;
        case 'ajax_buscar_clientes':
            $ctrl->ajaxBuscarClientes();
            break;
        case 'ajax_get_cliente':
            $ctrl->ajaxGetCliente();
            break;
        default:
            $data = $ctrl->crear();
            extract($data);
            include __DIR__ . '/app/views/cotizaciones/crear.php';
    }
    exit();
}
