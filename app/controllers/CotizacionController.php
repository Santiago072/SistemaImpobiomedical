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
    private ItemCotizacionService $itemService;
    private FinalizarCotizacionService $finalizarService;
    private int $porPagina = 10;

    public function __construct(\PDO $conexion)
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

        if (isset($_GET['nueva']) && $_GET['nueva'] === '1') {
            if (isset($_SESSION['cotizacion_id'])) {
                $cot = $this->model->buscarPorId((int)$_SESSION['cotizacion_id']);
                if ($cot && $cot['estado'] === 'borrador') {
                    $this->model->eliminar((int)$_SESSION['cotizacion_id']);
                }
                unset($_SESSION['cotizacion_id'], $_SESSION['cotizacion_revision_de']);
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

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
        $usuarioId     = (int)$_SESSION['usuario_id'];
        $usuarioCodigo = $_SESSION['usuario_codigo'] ?? 'COT';
        $asesorNombre  = $_SESSION['usuario_nombre'] ?? '';
        $asesorCargo   = $_SESSION['usuario_cargo'] ?? '';

        if (!isset($_SESSION['cotizacion_id'])) {
            $id = $this->model->buscarBorradorConItems($usuarioId);

            if ($id === null) {
                $id = $this->model->buscarCabeceraVacia($usuarioId);
            }

            if ($id === null) {
                $id = $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
            }

            $_SESSION['cotizacion_id'] = $id;
        } else {
            // Verificar que la cotización en sesión todavía existe y es válida
            $cotizacionExistente = $this->model->buscarPorId((int)$_SESSION['cotizacion_id']);

            if (!$cotizacionExistente || $cotizacionExistente['estado'] !== 'borrador') {
                // Si no existe o no es borrador, buscar uno nuevo (sin clones de modificación)
                $id = $this->model->buscarBorradorConItems($usuarioId)
                   ?? $this->model->buscarCabeceraVacia($usuarioId)
                   ?? $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);
                $_SESSION['cotizacion_id'] = $id;
                unset($_SESSION['cotizacion_revision_de'], $_SESSION['borrador_previo_id']);
            }
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
        $id = (int)($_GET['id'] ?? 0);
        $cotizacion = null;
        if ($id > 0) {
            $cotizacion = $this->model->buscarPorId($id);
        } else {
            $numero = $_GET['numero'] ?? '';
            if (!empty($numero)) {
                $cotizacion = $this->model->buscarPorNumero($numero);
            }
        }

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
        verificar_rate_limit(20, 60, 'cotizacion_eliminar_item');

        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Token puede venir por header AJAX o por POST
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Token de seguridad inválido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $rawId = $_POST['id'] ?? $_GET['id'] ?? '';
        if (!validar_numero($rawId)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $cotizacionId = $_SESSION['cotizacion_id'] ?? 0;
        if ((int)$cotizacionId <= 0) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'No hay cotización activa en la sesión']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
            exit();
        }

        $exito = $this->model->eliminarItem((int)$rawId, (int)$cotizacionId);

        if ($esAjax) {
            header('Content-Type: application/json');
            if ($exito) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar el ítem o no pertenece a la cotización activa']);
            }
            exit();
        }
        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
        exit();
    }

    // ── ELIMINAR COTIZACIÓN (Solo Admin) ─────────────────────────────────────────
    public function eliminar(): void
    {
        verificar_admin();
        verificar_rate_limit(10, 60, 'cotizacion_eliminar');

        $token = $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=csrf');
            exit();
        }
        $id = $_POST['id'] ?? $_GET['id'] ?? '';
        if (!validar_numero($id)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        $this->model->eliminar((int)$id);
        header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&deleted=1');
        exit();
    }

    // ── MODIFICAR COTIZACIÓN (Clonar y nueva versión) ─────────────────────────────
    public function modificar(): void
    {
        verificar_autenticacion();
        $id = (int)($_GET['id'] ?? 0);
        $cotizacionOriginal = null;
        if ($id > 0) {
            $cotizacionOriginal = $this->model->buscarPorId($id);
        } else {
            $numero = $_GET['numero'] ?? '';
            if (!empty($numero)) {
                $cotizacionOriginal = $this->model->buscarPorNumero($numero);
            }
        }

        if (!$cotizacionOriginal) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
            exit();
        }

        // Prevenir modificar una revisión (solo se puede modificar la original)
        if (strpos($cotizacionOriginal['numero_cotizacion'], '_') !== false) {
            $original = explode('_', $cotizacionOriginal['numero_cotizacion'])[0];
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=ya_modificada&original=' . urlencode($original));
            exit();
        }

        // Guardar el borrador actual del usuario antes de sobreescribir la sesión
        if (isset($_SESSION['cotizacion_id'])) {
            $borradorActual = $this->model->buscarPorId((int)$_SESSION['cotizacion_id']);
            // Solo guardar como previo si es un borrador normal (no otro clon de modificación)
            if ($borradorActual && (int)($borradorActual['es_revision'] ?? 0) === 0) {
                $_SESSION['borrador_previo_id'] = (int)$_SESSION['cotizacion_id'];
            }
        }

        // Crear una nueva cabecera clonando la original pero en estado borrador
        $usuarioId     = (int)$_SESSION['usuario_id'];
        $usuarioCodigo = $_SESSION['usuario_codigo'] ?? '';
        $asesorNombre  = $cotizacionOriginal['asesor_nombre'];
        $asesorCargo   = $cotizacionOriginal['asesor_cargo'];

        $nuevoCotizacionId = $this->model->crearCabecera($usuarioId, $usuarioCodigo, $asesorNombre, $asesorCargo);

        // Marcar el clon como revisión temporal para no confundirlo con borradores normales
        $this->model->marcarComoRevision($nuevoCotizacionId);

        // Copiar los datos del cliente de la original a la nueva
        $this->model->clonarDatosCabecera($cotizacionOriginal['id'], $nuevoCotizacionId);

        // Clonar los ítems
        $this->model->clonarItems($cotizacionOriginal['id'], $nuevoCotizacionId);

        // Establecer la nueva cotización como activa en la sesión
        $_SESSION['cotizacion_id'] = $nuevoCotizacionId;

        // Guardar la referencia de que es una revisión
        $partes = explode('_', $cotizacionOriginal['numero_cotizacion']);
        $_SESSION['cotizacion_revision_de'] = trim($partes[0]);

        // Flag para que recuperarOCrearBorrador() sepa que venimos de modificar() ahora mismo
        $_SESSION['_modificar_recien_activado'] = true;

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
        exit();
    }

    // ── DESCARTAR BORRADOR ─────────────────────────────────────────
    public function limpiarBorrador(): void
    {
        verificar_autenticacion();
        $usuarioId = (int)$_SESSION['usuario_id'];

        // Eliminar TODOS los borradores pendientes (y sus ítems) de este usuario
        $this->model->eliminarTodosBorradoresDelUsuario($usuarioId);

        // Limpiar todas las variables de sesión relacionadas con borradores y revisiones
        unset(
            $_SESSION['cotizacion_id'],
            $_SESSION['cotizacion_revision_de'],
            $_SESSION['borrador_previo_id'],
            $_SESSION['_modificar_recien_activado']
        );

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear');
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
        $cotizacion    = $this->model->buscarPorId($cotizacion_id);

        if (empty($items)) {
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=crear&error=no_items');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('csrf_token', 'mensajeError', 'items', 'cotizacion_id', 'cotizacion');
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
            $revisionDe = $_SESSION['cotizacion_revision_de'] ?? null;
            $numeroCotizacion = $this->finalizarService->procesarFinalizacion($cotizacion_id, $_POST, $_SESSION, $revisionDe);
            unset($_SESSION['cotizacion_id'], $_SESSION['cotizacion_revision_de']);

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
        if (isset($_GET['error']) && $_GET['error'] === 'ya_modificada') {
            $orig = sanitizar_entrada($_GET['original'] ?? '');
            $mensajeError = "No se puede modificar una revisión. Debe modificar la cotización original " . ($orig ? "($orig)" : "") . " para crear una nueva versión.";
        }
        $csrf_token   = generar_token_csrf();
        $cotizaciones = [];
        $totalPaginas = 0;
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;
        $busquedaFecha   = '';
        $busquedaFechaDesde = '';
        $busquedaFechaHasta = '';
        $busquedaCliente = '';
        $busquedaNumero  = '';

        $usuarioId = (int)$_SESSION['usuario_id'];
        $rol       = $_SESSION['rol'] ?? 'usuario';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filtros = [];
            if (!empty($_POST['fecha_desde']))       $filtros['fecha_desde']       = sanitizar_entrada($_POST['fecha_desde']);
            if (!empty($_POST['fecha_hasta']))       $filtros['fecha_hasta']       = sanitizar_entrada($_POST['fecha_hasta']);
            if (!empty($_POST['fecha']))             $filtros['fecha']             = sanitizar_entrada($_POST['fecha']);
            if (!empty($_POST['nombre_cliente']))    $filtros['nombre_cliente']    = sanitizar_entrada($_POST['nombre_cliente']);
            if (!empty($_POST['numero_cotizacion'])) $filtros['numero_cotizacion'] = sanitizar_entrada($_POST['numero_cotizacion']);
            if (!empty($_POST['estado_comercial']))  $filtros['estado_comercial']  = sanitizar_entrada($_POST['estado_comercial']);

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
        $busquedaFechaDesde = $filtros['fecha_desde'] ?? '';
        $busquedaFechaHasta = $filtros['fecha_hasta'] ?? '';
        $busquedaFecha   = $filtros['fecha'] ?? '';
        $busquedaCliente = $filtros['nombre_cliente'] ?? '';
        $busquedaNumero  = $filtros['numero_cotizacion'] ?? '';
        $busquedaEstado  = $filtros['estado_comercial'] ?? '';

        $total        = $this->model->contarConFiltros($filtros, $usuarioId, $rol);
        $totalPaginas = (int)ceil($total / $this->porPagina);
        $cotizaciones = $this->model->buscarConFiltros($filtros, $offset, $this->porPagina, $usuarioId, $rol);

        return compact('cotizaciones', 'csrf_token', 'mensajeError', 'busquedaFecha', 'busquedaFechaDesde',
                       'busquedaFechaHasta', 'busquedaCliente',
                       'busquedaNumero', 'busquedaEstado', 'paginaActual', 'totalPaginas', 'rol');
    }

    // ── CAMBIAR ESTADO COMERCIAL (Usuarios autenticados con CSRF y Rate Limit) ───────────
    public function cambiarEstadoComercial(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(30, 60, 'cot_cambiar_estado');

        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Token de seguridad inválido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=csrf');
            exit();
        }

        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = sanitizar_entrada($_POST['estado_comercial'] ?? '');

        if ($id <= 0 || !in_array($nuevoEstado, ['pendiente', 'concluida', 'descartada'], true)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Datos de estado no válidos']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=invalido');
            exit();
        }

        $exito = $this->model->actualizarEstadoComercial($id, $nuevoEstado);

        if ($esAjax) {
            header('Content-Type: application/json');
            if ($exito) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Estado actualizado exitosamente', 
                    'nuevo_estado' => $nuevoEstado,
                    'fecha_cambio' => $nuevoEstado === 'pendiente' ? null : date('Y-m-d H:i')
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado de la cotización']);
            }
            exit();
        }

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&updated=1');
        exit();
    }

    // ── CAMBIAR ESTADO DE ENTREGA (AJAX con CSRF y Rate Limit) ────────────────
    public function cambiarEstadoEntrega(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(30, 60, 'cot_cambiar_entrega');

        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Token de seguridad inválido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=csrf');
            exit();
        }

        $id = (int)($_POST['id'] ?? 0);
        $nuevoEstado = sanitizar_entrada($_POST['estado_entrega'] ?? '');

        if ($id <= 0 || !in_array($nuevoEstado, ['pendiente', 'en_transito', 'entregado'], true)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Estado de entrega no válido']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&error=invalido');
            exit();
        }

        $exito = $this->model->actualizarEstadoEntrega($id, $nuevoEstado);

        if ($esAjax) {
            header('Content-Type: application/json');
            if ($exito) {
                // Obtener datos frescos de la cotización para calcular días si está entregado
                $cot = $this->model->buscarPorId($id);
                $diasEntrega = null;
                if ($nuevoEstado === 'entregado' && !empty($cot['fecha_entrega'])) {
                    $fechaBase = !empty($cot['fecha_cambio_estado']) ? $cot['fecha_cambio_estado'] : $cot['fecha_creacion'];
                    $tBase = strtotime($fechaBase);
                    $tEntrega = strtotime($cot['fecha_entrega']);
                    if ($tBase && $tEntrega && $tEntrega >= $tBase) {
                        $diasEntrega = (int)round(($tEntrega - $tBase) / 86400);
                    } else {
                        $diasEntrega = 0;
                    }
                }
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Estado de entrega actualizado', 
                    'nuevo_estado' => $nuevoEstado,
                    'fecha_entrega' => $nuevoEstado === 'entregado' ? date('Y-m-d H:i') : null,
                    'dias_entrega' => $diasEntrega
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado de entrega']);
            }
            exit();
        }

        header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar&updated=1');
        exit();
    }

    // ── GENERAR PDF ───────────────────────────────────────────────────────────
    public function generarPdf(): array
    {
        verificar_autenticacion();
        verificar_rate_limit(15, 60, 'generar_pdf');

        $id = (int)($_GET['id'] ?? 0);
        $cotizacion = null;
        if ($id > 0) {
            $cotizacion = $this->model->buscarPorId($id);
        } elseif (isset($_GET['ver'])) {
            $numero = sanitizar_entrada($_GET['ver']);
            $cotizacion = $this->model->buscarPorNumero($numero);
        }

        if (!$cotizacion) {
            if (!isset($_GET['ver']) && $id <= 0) {
                header('Location: ' . BASE_URL . '?module=cotizaciones&action=consultar');
                exit();
            }
            http_response_code(404);
            die('Cotización no encontrada.');
        }

        $forzar = isset($_GET['descargar']);

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

