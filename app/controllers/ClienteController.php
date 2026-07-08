<?php
require_once dirname(__DIR__) . '/models/ClienteModel.php';
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * ClienteController — gestión CRUD de clientes.
 * - SRP: coordina la lógica de clientes.
 * - Disponible para admin y usuario (con permisos diferenciados).
 */
class ClienteController
{
    private ClienteModel $model;
    private int $porPagina = 10;

    public function __construct(\mysqli $conexion)
    {
        $this->model = new ClienteModel($conexion);
    }

    public function listar(): array
    {
        verificar_autenticacion();

        $busqueda     = sanitizar_entrada($_GET['busqueda'] ?? '');
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;

        $total        = $this->model->contar($busqueda);
        $clientes     = $this->model->listar($offset, $this->porPagina, $busqueda);
        $totalPaginas = (int)ceil($total / $this->porPagina);

        $mensajeExito = '';
        $mensajeError = '';
        if (isset($_GET['created'])) $mensajeExito = 'Cliente creado exitosamente';
        if (isset($_GET['updated'])) $mensajeExito = 'Cliente actualizado exitosamente';
        if (isset($_GET['deleted'])) $mensajeExito = 'Cliente eliminado exitosamente';

        $csrf_token = generar_token_csrf();

        return compact('clientes', 'busqueda', 'paginaActual', 'totalPaginas',
                       'total', 'mensajeExito', 'mensajeError', 'csrf_token');
    }

    public function crear(): array
    {
        verificar_autenticacion();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('mensajeError', 'csrf_token');
        }

        verificar_rate_limit(10, 60, 'cliente_crear');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('mensajeError', 'csrf_token');
        }

        $nombre          = mb_substr(sanitizar_entrada($_POST['nombre'] ?? ''), 0, 100);
        $nit             = mb_substr(sanitizar_entrada($_POST['nit'] ?? ''), 0, 25);
        $departamento    = mb_substr(sanitizar_entrada($_POST['departamento'] ?? ''), 0, 60);
        $municipio       = mb_substr(sanitizar_entrada($_POST['municipio'] ?? ''), 0, 60);
        $direccion       = mb_substr(sanitizar_entrada($_POST['direccion'] ?? ''), 0, 100);
        $nombre_contacto = mb_substr(sanitizar_entrada($_POST['nombre_contacto'] ?? ''), 0, 60);
        $telefono        = mb_substr(sanitizar_entrada($_POST['telefono'] ?? ''), 0, 20);
        $correo          = mb_substr(sanitizar_entrada($_POST['correo'] ?? ''), 0, 100) ?: null;

        $mensajeError = $this->validarCampos($nombre, $nit, $departamento, $municipio, $direccion, $nombre_contacto, $telefono);

        if ($mensajeError === '' && $correo && !validar_email($correo)) {
            $mensajeError = 'El correo electrónico no es válido';
        }

        if ($mensajeError === '' && !empty($nit) && $this->model->existeNit($nit)) {
            $mensajeError = 'El NIT/CC ya está registrado';
        }

        if ($mensajeError !== '') {
            return compact('mensajeError', 'csrf_token');
        }

        if ($this->model->crear($nombre, $nit, $departamento, $municipio, $direccion, $nombre_contacto, $telefono, $correo)) {
            header('Location: ' . BASE_URL . '?module=clientes&created=1');
            exit();
        }

        $mensajeError = 'Error al crear el cliente';
        return compact('mensajeError', 'csrf_token');
    }

    public function editar(): array
    {
        verificar_autenticacion();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=clientes');
            exit();
        }

        $id      = (int)$_GET['id'];
        $cliente = $this->model->buscarPorId($id);
        if (!$cliente) {
            header('Location: ' . BASE_URL . '?module=clientes');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('cliente', 'mensajeError', 'csrf_token');
        }

        verificar_rate_limit(15, 60, 'cliente_editar');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('cliente', 'mensajeError', 'csrf_token');
        }

        $nombre          = mb_substr(sanitizar_entrada($_POST['nombre'] ?? ''), 0, 100);
        $nit             = mb_substr(sanitizar_entrada($_POST['nit'] ?? ''), 0, 25);
        $departamento    = mb_substr(sanitizar_entrada($_POST['departamento'] ?? ''), 0, 60);
        $municipio       = mb_substr(sanitizar_entrada($_POST['municipio'] ?? ''), 0, 60);
        $direccion       = mb_substr(sanitizar_entrada($_POST['direccion'] ?? ''), 0, 100);
        $nombre_contacto = mb_substr(sanitizar_entrada($_POST['nombre_contacto'] ?? ''), 0, 60);
        $telefono        = mb_substr(sanitizar_entrada($_POST['telefono'] ?? ''), 0, 20);
        $correo          = mb_substr(sanitizar_entrada($_POST['correo'] ?? ''), 0, 100) ?: null;
        $estado          = mb_substr(sanitizar_entrada($_POST['estado'] ?? 'activo'), 0, 10);
        if (empty($estado)) $estado = 'activo';

        $mensajeError = $this->validarCampos($nombre, $nit, $departamento, $municipio, $direccion, $nombre_contacto, $telefono);

        if ($mensajeError === '' && $correo && !validar_email($correo)) {
            $mensajeError = 'El correo electrónico no es válido';
        }
        if ($mensajeError === '' && !empty($nit) && $this->model->existeNit($nit, $id)) {
            $mensajeError = 'El NIT/CC ya está registrado en otro cliente';
        }

        if ($mensajeError !== '') {
            $cliente = array_merge($cliente, compact('nombre', 'nit', 'departamento', 'municipio', 'direccion', 'nombre_contacto', 'telefono', 'correo', 'estado'));
            return compact('cliente', 'mensajeError', 'csrf_token');
        }

        if ($this->model->actualizar($id, $nombre, $nit, $departamento, $municipio, $direccion, $nombre_contacto, $telefono, $correo, $estado)) {
            header('Location: ' . BASE_URL . '?module=clientes&updated=1');
            exit();
        }

        $mensajeError = 'Error al actualizar';
        return compact('cliente', 'mensajeError', 'csrf_token');
    }

    public function eliminar(): void
    {
        verificar_admin();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=clientes');
            exit();
        }

        $this->model->eliminar((int)$_GET['id']);
        header('Location: ' . BASE_URL . '?module=clientes&deleted=1');
        exit();
    }

    // ── AJAX ──────────────────────────────────────────────────────────────────
    public function ajaxBuscar(): void
    {
        verificar_autenticacion();
        verificar_rate_limit(20, 60, 'ajax_clientes');
        header('Content-Type: application/json');
        $busqueda = sanitizar_entrada($_GET['q'] ?? '');
        $clientes = $this->model->buscarParaSelect($busqueda);
        echo json_encode(['status' => 'success', 'data' => $clientes]);
        exit();
    }

    public function ajaxGet(): void
    {
        verificar_autenticacion();
        header('Content-Type: application/json');
        if (!validar_numero($_GET['id'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            exit();
        }
        $cliente = $this->model->buscarPorId((int)$_GET['id']);
        echo json_encode($cliente
            ? ['status' => 'success', 'data' => $cliente]
            : ['status' => 'error', 'message' => 'Cliente no encontrado']);
        exit();
    }

    private function validarCampos(string $nombre, string $nit, string $departamento,
                                   string $municipio, string $direccion,
                                   string $nombre_contacto, string $telefono): string
    {
        if (!$nombre) {
            return 'El Nombre / Entidad es obligatorio';
        }
        if (mb_strlen($nombre) < 2) {
            return 'El nombre debe tener al menos 2 caracteres';
        }
        return '';
    }
}
