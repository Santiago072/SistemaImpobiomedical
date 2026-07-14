<?php
require_once dirname(__DIR__) . '/models/UsuarioModel.php';
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * UsuarioController — lógica de gestión de usuarios.
 *
 * Principios:
 *   - SRP: solo coordina la lógica de usuarios.
 *   - El admin asigna el código (ej: EB) al crear el usuario.
 *   - El admin resetea la contraseña (sin email).
 */
class UsuarioController
{
    private UsuarioModel $model;
    private int $porPagina = 10;

    public function __construct(\mysqli $conexion)
    {
        $this->model = new UsuarioModel($conexion);
    }

    // ── LISTAR ────────────────────────────────────────────────────────────────
    public function listar(): array
    {
        verificar_admin();

        $busqueda     = sanitizar_entrada($_GET['busqueda'] ?? '');
        $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
        $offset       = ($paginaActual - 1) * $this->porPagina;

        $total        = $this->model->contar($busqueda);
        $usuarios     = $this->model->listar($offset, $this->porPagina, $busqueda);
        $totalPaginas = (int)ceil($total / $this->porPagina);

        $mensajeExito = '';
        $mensajeError = '';
        if (isset($_GET['created']))  $mensajeExito = 'Usuario creado exitosamente';
        if (isset($_GET['updated']))  $mensajeExito = 'Usuario actualizado exitosamente';
        if (isset($_GET['deleted']))  $mensajeExito = 'Usuario eliminado exitosamente';
        if (isset($_GET['reset']))    $mensajeExito = 'Contraseña restablecida exitosamente';
        if (isset($_GET['error'])) {
            $mapa = [
                'last_admin'    => 'No se puede eliminar el último administrador',
                'self_delete'   => 'No puede eliminarse a sí mismo',
                'delete_failed' => 'Error al eliminar el usuario',
                'invalid_id'    => 'ID de usuario inválido',
            ];
            $mensajeError = $mapa[$_GET['error']] ?? 'Error al procesar la solicitud';
        }

        $csrf_token = generar_token_csrf();

        return compact('usuarios', 'busqueda', 'paginaActual', 'totalPaginas', 'total',
                       'mensajeExito', 'mensajeError', 'csrf_token');
    }

    // ── CREAR ─────────────────────────────────────────────────────────────────
    public function crear(): array
    {
        verificar_admin();

        $mensajeError = '';
        $mensajeExito = '';
        $csrf_token   = generar_token_csrf();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('mensajeError', 'mensajeExito', 'csrf_token');
        }

        verificar_rate_limit(10, 60, 'usuario_crear');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('mensajeError', 'mensajeExito', 'csrf_token');
        }

        $codigo   = mb_strtoupper(mb_substr(sanitizar_entrada($_POST['codigo'] ?? ''), 0, 10));
        $doc      = mb_substr(sanitizar_entrada($_POST['documento'] ?? ''), 0, 20);
        $nombre   = mb_substr(sanitizar_entrada($_POST['nombre'] ?? ''), 0, 100);
        $correo   = mb_substr(sanitizar_entrada($_POST['correo'] ?? ''), 0, 100);
        $telefono = mb_substr(sanitizar_entrada($_POST['telefono'] ?? ''), 0, 20);
        $cargo    = mb_substr(sanitizar_entrada($_POST['cargo'] ?? ''), 0, 50);
        $rol      = mb_substr(sanitizar_entrada($_POST['rol'] ?? ''), 0, 10);
        $password = mb_substr($_POST['password'] ?? '', 0, 255);

        $mensajeError = $this->validarCampos($codigo, $doc, $nombre, $correo, $telefono, $rol, $password, true);

        if ($mensajeError === '' && $this->model->existeCodigoOCorreo($codigo, $correo)) {
            $mensajeError = 'El código o correo ya está registrado';
        }
        if ($mensajeError === '' && $this->model->existeDocumentoOCorreo($doc, $correo)) {
            $mensajeError = 'El documento o correo ya está registrado';
        }

        if ($mensajeError !== '') {
            return compact('mensajeError', 'mensajeExito', 'csrf_token');
        }

        $hash = password_hash(!empty($password) ? $password : $doc, PASSWORD_BCRYPT);
        if ($this->model->crear($codigo, $doc, $nombre, $correo, $hash, $telefono, $cargo, $rol)) {
            header('Location: ' . BASE_URL . '?module=usuarios&created=1');
            exit();
        }

        $mensajeError = 'Error al crear el usuario';
        return compact('mensajeError', 'mensajeExito', 'csrf_token');
    }

    // ── EDITAR ────────────────────────────────────────────────────────────────
    public function editar(): array
    {
        verificar_admin();

        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=usuarios');
            exit();
        }

        $id      = (int)$_GET['id'];
        $usuario = $this->model->buscarPorId($id);
        if (!$usuario) {
            header('Location: ' . BASE_URL . '?module=usuarios');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('usuario', 'mensajeError', 'csrf_token');
        }

        verificar_rate_limit(15, 60, 'usuario_editar');

        if (!verificar_token_csrf($_POST['csrf_token'] ?? '')) {
            $mensajeError = 'Token de seguridad inválido';
            return compact('usuario', 'mensajeError', 'csrf_token');
        }

        $codigo   = mb_strtoupper(mb_substr(sanitizar_entrada($_POST['codigo'] ?? ''), 0, 10));
        $doc      = mb_substr(sanitizar_entrada($_POST['documento'] ?? ''), 0, 20);
        $nombre   = mb_substr(sanitizar_entrada($_POST['nombre'] ?? ''), 0, 100);
        $correo   = mb_substr(sanitizar_entrada($_POST['correo'] ?? ''), 0, 100);
        $telefono = mb_substr(sanitizar_entrada($_POST['telefono'] ?? ''), 0, 20);
        $cargo    = mb_substr(sanitizar_entrada($_POST['cargo'] ?? ''), 0, 50);
        $rol      = mb_substr(sanitizar_entrada($_POST['rol'] ?? ''), 0, 10);
        $estado   = mb_substr(sanitizar_entrada($_POST['estado'] ?? ''), 0, 10);

        $mensajeError = $this->validarCampos($codigo, $doc, $nombre, $correo, $telefono, $rol, '', true);

        if ($mensajeError === '' && !in_array($estado, ['activo', 'inactivo'], true)) {
            $mensajeError = 'Estado no válido';
        }
        if ($mensajeError === '' && $this->model->existeCodigoOCorreo($codigo, $correo, $id)) {
            $mensajeError = 'El código o correo ya está registrado en otro usuario';
        }

        if ($mensajeError !== '') {
            $usuario = array_merge($usuario, compact('codigo', 'nombre', 'correo', 'telefono', 'cargo', 'rol', 'estado'));
            return compact('usuario', 'mensajeError', 'csrf_token');
        }

        if ($this->model->actualizar($id, $codigo, $doc, $nombre, $correo, $telefono, $cargo, $rol, $estado)) {
            if ($id === (int)$_SESSION['usuario_id']) {
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_codigo'] = $codigo;
                $_SESSION['usuario_cargo']  = $cargo;
                $_SESSION['rol']            = $rol;
            }
            header('Location: ' . BASE_URL . '?module=usuarios&updated=1');
            exit();
        }

        $mensajeError = 'Error al actualizar';
        return compact('usuario', 'mensajeError', 'csrf_token');
    }

    // ── RESET PASSWORD (Admin restablece la contraseña manualmente) ───────────
    public function resetPassword(): void
    {
        verificar_admin();

        if (!validar_numero($_GET['id'] ?? '')) {
            header('Location: ' . BASE_URL . '?module=usuarios&error=invalid_id');
            exit();
        }

        $id      = (int)$_GET['id'];
        $usuario = $this->model->buscarPorId($id);
        if (!$usuario) {
            header('Location: ' . BASE_URL . '?module=usuarios&error=invalid_id');
            exit();
        }

        // La nueva contraseña es el documento del usuario (por defecto)
        $nuevaPass = $usuario['documento'];
        $hash = password_hash($nuevaPass, PASSWORD_BCRYPT);
        $this->model->resetPassword($id, $hash);
        header('Location: ' . BASE_URL . '?module=usuarios&reset=1');
        exit();
    }

    // ── ELIMINAR ──────────────────────────────────────────────────────────────
    public function eliminar(): void
    {
        verificar_admin();

        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $responderError = function (string $msg, string $queryParam) use ($esAjax): void {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $msg]);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=usuarios&error=' . $queryParam);
            exit();
        };

        if (!validar_numero($_GET['id'] ?? '')) {
            $responderError('ID inválido', 'invalid_id');
        }

        $id = (int)$_GET['id'];

        if ($id === (int)$_SESSION['usuario_id']) {
            $responderError('No puedes eliminar tu propia cuenta', 'self_delete');
        }

        $usuarioAEliminar = $this->model->buscarPorId($id);
        if ($usuarioAEliminar && $usuarioAEliminar['rol'] === 'admin' &&
            $this->model->contarAdmins() <= 1) {
            $responderError('No se puede eliminar al último administrador', 'last_admin');
        }

        if ($this->model->eliminar($id)) {
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success']);
                exit();
            }
            header('Location: ' . BASE_URL . '?module=usuarios&deleted=1');
            exit();
        }

        $responderError('Error al eliminar', 'delete_failed');
    }

    // ── Validación ────────────────────────────────────────────────────────────
    private function validarCampos(string $codigo, string $doc, string $nombre, string $correo,
                                   string $telefono, string $rol, string $password,
                                   bool $passwordOpcional = false): string
    {
        if (!$codigo || !$doc || !$nombre || !$correo || !$telefono || !$rol) {
            return 'Todos los campos son obligatorios';
        }
        if (!preg_match('/^[A-Z0-9\-]{1,10}$/', $codigo)) {
            return 'El código solo puede contener letras mayúsculas, números y guiones (máx. 10)';
        }
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
            return 'El nombre debe tener entre 3 y 100 caracteres';
        }
        if (!validar_email($correo)) {
            return 'El correo electrónico no es válido';
        }
        if (!in_array($rol, ['admin', 'usuario'], true)) {
            return 'Rol no válido';
        }
        if (!$passwordOpcional && empty($password)) {
            return 'La contraseña es obligatoria';
        }
        if (!empty($password) && mb_strlen($password) < 6) {
            return 'La contraseña debe tener al menos 6 caracteres';
        }
        return '';
    }
}
