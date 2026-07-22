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

    public function __construct(\mysqli $conexion)
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
        verificar_admin();

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
        verificar_admin();

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
        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=productos');
            exit();
        }
        $this->model->eliminar((int)$_GET['id']);
        header('Location: ' . BASE_URL . '?module=productos&deleted=1');
        exit();
    }
}
