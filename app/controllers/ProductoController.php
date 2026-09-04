<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * ProductoController — CRUD de catálogo de productos.
 * Solo admin puede crear/editar/eliminar.
 */
class ProductoController
{
    private ProductoModel     $model;
    private FileUploadService $uploader;
    private int $porPagina = 12;

    public function __construct(\PDO $conexion)
    {
        $this->model    = new ProductoModel($conexion);
        $this->uploader = new FileUploadService(dirname(__DIR__, 2) . '/uploads');
    }

    public function listar(): array
    {
        verificar_autenticacion();

        $busqueda     = sanitizar_entrada($_GET['busqueda'] ?? '');
        $categoriaSel = sanitizar_entrada($_GET['categoria'] ?? '');
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;

        $total        = $this->model->contar($busqueda, $categoriaSel);
        $productos    = $this->model->listar($offset, $this->porPagina, $busqueda, $categoriaSel);
        $totalPaginas = (int)ceil($total / $this->porPagina);
        $rol          = $_SESSION['rol'] ?? 'usuario';
        
        $categoriasCount = $this->model->obtenerConteosPorCategoria();

        $mensajeExito = '';
        $mensajeError = '';
        if (isset($_GET['created'])) $mensajeExito = 'Producto creado exitosamente';
        if (isset($_GET['updated'])) $mensajeExito = 'Producto actualizado exitosamente';
        if (isset($_GET['deleted'])) $mensajeExito = 'Producto eliminado exitosamente';

        $csrf_token = generar_token_csrf();

        return compact('productos', 'busqueda', 'categoriaSel', 'categoriasCount', 'paginaActual', 'totalPaginas',
                       'total', 'mensajeExito', 'mensajeError', 'rol', 'csrf_token');
    }

    public function crear(): array
    {
        verificar_autenticacion();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('mensajeError', 'csrf_token');
        }

        verificar_rate_limit(10, 60, 'producto_crear');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('mensajeError', 'csrf_token');
        }

        $titulo         = mb_substr(sanitizar_entrada($_POST['titulo'] ?? ''), 0, 255);
        $descripcion    = mb_substr(sanitizar_entrada($_POST['descripcion'] ?? ''), 0, 5000);
        $iva            = mb_substr(sanitizar_entrada($_POST['iva'] ?? ''), 0, 5);
        $porcentaje_iva = (float)($_POST['porcentaje_iva'] ?? 19);

        $categoria      = mb_substr(sanitizar_entrada($_POST['categoria'] ?? ''), 0, 100);
        $codigo_producto= mb_substr(sanitizar_entrada($_POST['codigo_producto'] ?? ''), 0, 60);

        if (!$titulo || !$descripcion) {
            $mensajeError = 'Todos los campos son obligatorios';
            return compact('mensajeError', 'csrf_token');
        }

        if (!in_array($iva, ['si', 'no'], true)) {
            $mensajeError = 'IVA no válido';
            return compact('mensajeError', 'csrf_token');
        }

        $foto = $this->uploader->subir($_FILES['foto'] ?? [], '');

        if ($this->model->crear($titulo, $foto, $descripcion, $iva, $porcentaje_iva, $categoria, $codigo_producto)) {
            header('Location: ' . BASE_URL . '?module=productos&created=1');
            exit();
        }

        $mensajeError = 'Error al crear el producto';
        return compact('mensajeError', 'csrf_token');
    }

    public function editar(): array
    {
        verificar_autenticacion();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=productos');
            exit();
        }

        $id      = (int)$_GET['id'];
        $producto = $this->model->buscarPorId($id);
        if (!$producto) {
            header('Location: ' . BASE_URL . '?module=productos');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('producto', 'mensajeError', 'csrf_token');
        }

        verificar_rate_limit(15, 60, 'producto_editar');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('producto', 'mensajeError', 'csrf_token');
        }

        $titulo         = mb_substr(sanitizar_entrada($_POST['titulo'] ?? ''), 0, 255);
        $descripcion    = mb_substr(sanitizar_entrada($_POST['descripcion'] ?? ''), 0, 5000);
        $iva            = mb_substr(sanitizar_entrada($_POST['iva'] ?? ''), 0, 5);
        $porcentaje_iva = (float)($_POST['porcentaje_iva'] ?? 19);
        $estado         = mb_substr(sanitizar_entrada($_POST['estado'] ?? 'activo'), 0, 10);
        if (empty($estado)) $estado = 'activo';

        $categoria      = mb_substr(sanitizar_entrada($_POST['categoria'] ?? ''), 0, 100);
        $codigo_producto= mb_substr(sanitizar_entrada($_POST['codigo_producto'] ?? ''), 0, 60);

        if (!$titulo || !$descripcion) {
            $mensajeError = 'Todos los campos son obligatorios';
            return compact('producto', 'mensajeError', 'csrf_token');
        }

        $foto = $this->uploader->reemplazar($_FILES['foto'] ?? [], $producto['foto'] ?? '');

        if ($this->model->actualizar($id, $titulo, $foto, $descripcion, $iva, $porcentaje_iva, $estado, $categoria, $codigo_producto)) {
            header('Location: ' . BASE_URL . '?module=productos&updated=1');
            exit();
        }

        $mensajeError = 'Error al actualizar';
        return compact('producto', 'mensajeError', 'csrf_token');
    }

    public function eliminar(): void
    {
        verificar_admin();
        verificar_rate_limit(10, 60, 'producto_eliminar');

        $token = $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($token)) {
            header('Location: ' . BASE_URL . '?module=productos&error=csrf');
            exit();
        }
        $id = $_POST['id'] ?? $_GET['id'] ?? '';
        if (!validar_numero($id)) {
            header('Location: ' . BASE_URL . '?module=productos');
            exit();
        }
        $this->model->eliminar((int)$id);
        header('Location: ' . BASE_URL . '?module=productos&deleted=1');
        exit();
    }


    public function exportarPdf(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(15, 60, 'exportar_pdf_catalogo');

        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $modo = sanitizar_entrada($_POST['modo'] ?? $_GET['modo'] ?? '');
        $idsRaw = $_POST['ids'] ?? $_GET['ids'] ?? '';
        $ids = [];
        if (is_array($idsRaw)) {
            $ids = array_filter(array_map('intval', $idsRaw));
        } elseif (is_string($idsRaw) && trim($idsRaw) !== '') {
            $ids = array_filter(array_map('intval', explode(',', $idsRaw)));
        }

        if (empty($ids) && $modo !== 'completo') {
            $_SESSION['flash_error'] = 'Debe seleccionar al menos un producto para exportar el catálogo en PDF.';
            header('Location: ' . BASE_URL . '?module=productos&action=lista');
            exit();
        }

        $busqueda = sanitizar_entrada($_REQUEST['busqueda'] ?? '');
        $categoriaSel = sanitizar_entrada($_REQUEST['categoria'] ?? '');

        $productos = $this->model->listarParaExportar($busqueda, $categoriaSel, $ids);

        while (ob_get_level() > 0) { ob_end_clean(); }
        include dirname(__DIR__, 2) . '/app/views/productos/pdf.php';
        exit();
    }

    public function ajaxBuscar(): void
    {
        verificar_autenticacion();

        $term = sanitizar_entrada($_GET['term'] ?? $_GET['busqueda'] ?? '');
        $categoria = sanitizar_entrada($_GET['categoria'] ?? '');

        $productos = $this->model->listar(0, 100, $term, $categoria);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'    => 'ok',
            'productos' => $productos,
            'total'     => count($productos),
            'isAdmin'   => (($_SESSION['rol'] ?? '') === 'admin'),
        ]);
        exit();
    }
}

