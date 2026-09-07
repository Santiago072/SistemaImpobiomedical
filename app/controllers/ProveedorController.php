<?php
/**
 * ProveedorController — Controlador del módulo de Gestión de Proveedores.
 */
require_once __DIR__ . '/../../config/seguridad.php';
require_once __DIR__ . '/../models/ProveedorModel.php';

class ProveedorController
{
    private ProveedorModel $model;
    private \PDO $db;

    public function __construct(\PDO $conexion)
    {
        $this->db    = $conexion;
        $this->model = new ProveedorModel($conexion);
    }

    /**
     * Lista paginada de proveedores con formulario modal.
     */
    public function listar(): array
    {
        verificar_autenticacion();

        $pagina   = max(1, (int)($_GET['pagina'] ?? 1));
        $busqueda = sanitizar_entrada($_GET['busqueda'] ?? '');
        $estado   = sanitizar_entrada($_GET['estado'] ?? '');

        $resultado = $this->model->listar($pagina, 15, $busqueda, $estado);

        return array_merge($resultado, [
            'busqueda'      => $busqueda,
            'filtroEstado'  => $estado,
            'mensajeExito'  => $_SESSION['flash_exito'] ?? '',
            'mensajeError'  => $_SESSION['flash_error'] ?? '',
            'csrf_token'    => generar_token_csrf(),
        ]);
    }

    /**
     * Crear un nuevo proveedor (vía POST o Modal).
     */
    public function crear(): array
    {
        verificar_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['mensajeError' => 'Método no permitido.'];
        }

        verificar_rate_limit(15, 60, 'proveedor_crear');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die('Error 403: Solicitud no válida o token CSRF expirado.');
        }

        $nitRaw    = sanitizar_entrada($_POST['nit'] ?? '');
        $nit       = function_exists('normalizar_nit') ? normalizar_nit($nitRaw) : str_replace(['.', ' '], '', $nitRaw);
        $nombre    = mb_substr(sanitizar_entrada($_POST['nombre_proveedor'] ?? ''), 0, 200);
        $tipoContr = mb_substr(sanitizar_entrada($_POST['tipo_contribuyente'] ?? 'PERSONA JURÍDICA'), 0, 100);
        $banco     = mb_substr(sanitizar_entrada($_POST['nombre_banco'] ?? ''), 0, 100);
        $cuenta    = mb_substr(sanitizar_entrada($_POST['numero_cuenta'] ?? ''), 0, 100);
        $tipoCta   = mb_substr(sanitizar_entrada($_POST['tipo_cuenta'] ?? ''), 0, 50);
        $estado    = in_array($_POST['estado'] ?? '', ['activo', 'inactivo'], true) ? $_POST['estado'] : 'activo';

        if ($nit === '' || $nombre === '') {
            $_SESSION['flash_error'] = 'El NIT y el Nombre del Proveedor son obligatorios.';
            return ['mensajeError' => $_SESSION['flash_error']];
        }

        if ($this->model->nitExiste($nit)) {
            $_SESSION['flash_error'] = "El NIT {$nit} ya se encuentra registrado para otro proveedor.";
            return ['mensajeError' => $_SESSION['flash_error']];
        }

        try {
            $this->model->crear([
                'nit'                => $nit,
                'nombre_proveedor'   => $nombre,
                'tipo_contribuyente' => $tipoContr,
                'nombre_banco'       => $banco,
                'numero_cuenta'      => $cuenta,
                'tipo_cuenta'        => $tipoCta,
                'estado'             => $estado,
            ]);

            $_SESSION['flash_exito'] = "Proveedor '{$nombre}' registrado exitosamente.";
            header('Location: ' . BASE_URL . '?module=proveedores');
            exit();
        } catch (\Exception $e) {
            error_log('Error creando proveedor: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Ocurrió un error al registrar el proveedor.';
            return ['mensajeError' => $_SESSION['flash_error']];
        }
    }

    /**
     * Editar un proveedor existente.
     */
    public function editar(): array
    {
        verificar_autenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['mensajeError' => 'Método no permitido.'];
        }

        verificar_rate_limit(20, 60, 'proveedor_editar');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die('Error 403: Solicitud no válida o token CSRF expirado.');
        }

        $id        = (int)($_POST['id'] ?? 0);
        $nitRaw    = sanitizar_entrada($_POST['nit'] ?? '');
        $nit       = function_exists('normalizar_nit') ? normalizar_nit($nitRaw) : str_replace(['.', ' '], '', $nitRaw);
        $nombre    = mb_substr(sanitizar_entrada($_POST['nombre_proveedor'] ?? ''), 0, 200);
        $tipoContr = mb_substr(sanitizar_entrada($_POST['tipo_contribuyente'] ?? 'PERSONA JURÍDICA'), 0, 100);
        $banco     = mb_substr(sanitizar_entrada($_POST['nombre_banco'] ?? ''), 0, 100);
        $cuenta    = mb_substr(sanitizar_entrada($_POST['numero_cuenta'] ?? ''), 0, 100);
        $tipoCta   = mb_substr(sanitizar_entrada($_POST['tipo_cuenta'] ?? ''), 0, 50);
        $estado    = in_array($_POST['estado'] ?? '', ['activo', 'inactivo'], true) ? $_POST['estado'] : 'activo';

        if ($id <= 0 || $nit === '' || $nombre === '') {
            $_SESSION['flash_error'] = 'Datos insuficientes para actualizar el proveedor.';
            header('Location: ' . BASE_URL . '?module=proveedores');
            exit();
        }

        if ($this->model->nitExiste($nit, $id)) {
            $_SESSION['flash_error'] = "El NIT {$nit} ya pertenece a otro proveedor registrado.";
            header('Location: ' . BASE_URL . '?module=proveedores');
            exit();
        }

        try {
            $this->model->editar($id, [
                'nit'                => $nit,
                'nombre_proveedor'   => $nombre,
                'tipo_contribuyente' => $tipoContr,
                'nombre_banco'       => $banco,
                'numero_cuenta'      => $cuenta,
                'tipo_cuenta'        => $tipoCta,
                'estado'             => $estado,
            ]);

            $_SESSION['flash_exito'] = "Proveedor '{$nombre}' actualizado correctamente.";
            header('Location: ' . BASE_URL . '?module=proveedores');
            exit();
        } catch (\Exception $e) {
            error_log('Error editando proveedor: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error al actualizar el proveedor.';
            header('Location: ' . BASE_URL . '?module=proveedores');
            exit();
        }
    }

    /**
     * Desactiva / Elimina un proveedor.
     */
    public function eliminar(): void
    {
        verificar_admin();
        verificar_rate_limit(10, 60, 'proveedor_eliminar');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('Error 403: Solicitud no válida o token CSRF expirado.');
            }
            $id = (int)($_POST['id'] ?? 0);
        } else {
            $id = (int)($_GET['id'] ?? 0);
        }

        if ($id > 0) {
            $this->model->eliminar($id);
            $_SESSION['flash_exito'] = 'Proveedor desactivado correctamente.';
        }

        header('Location: ' . BASE_URL . '?module=proveedores');
        exit();
    }

    /**
     * Endpoint AJAX: Búsqueda predictiva en tiempo real (por NIT o Nombre)
     * Utilizado en Nueva Cotización y Nueva Orden de Compra.
     */
    public function ajaxBuscar(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(60, 60, 'proveedor_buscar');
        header('Content-Type: application/json; charset=utf-8');

        $term = sanitizar_entrada($_GET['term'] ?? '');
        $res  = $this->model->buscarLive($term, 12);
        echo json_encode($res);
        exit();
    }

    /**
     * Endpoint AJAX: Obtener datos de un proveedor específico por ID.
     */
    public function ajaxGet(): void
    {
        verificar_autenticacion();
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        $prov = $this->model->buscarPorId($id);

        if (!$prov) {
            http_response_code(404);
            echo json_encode(['error' => 'Proveedor no encontrado']);
            exit();
        }

        echo json_encode($prov);
        exit();
    }

}
