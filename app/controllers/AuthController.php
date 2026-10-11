<?php
require_once dirname(__DIR__, 2) . '/config/seguridad.php';

/**
 * AuthController — lógica de autenticación (login / logout).
 * - SRP: solo maneja autenticación.
 * - Login: usa correo + contraseña con bcrypt.
 */
class AuthController
{
    private UsuarioModel $model;

    public function __construct(\PDO $conexion)
    {
        $this->model = new UsuarioModel($conexion);
    }

    public function login(): array
    {
        $mensajeError = '';
        $csrf_token   = generar_token_csrf();

        if (isset($_GET['timeout'])) {
            $mensajeError = 'Su sesión ha expirado por inactividad. Por favor inicie sesión nuevamente.';
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return compact('mensajeError', 'csrf_token');
        }

        $tokenPost = $_POST['csrf_token'] ?? '';
        if (!verificar_token_csrf($tokenPost)) {
            $mensajeError = 'Token de seguridad inválido. Por favor intente nuevamente.';
            return compact('mensajeError', 'csrf_token');
        }

        $identificador = sanitizar_entrada($_POST['documento'] ?? '');
        $contrasena    = $_POST['contrasena'] ?? '';

        if ($identificador === '' || $contrasena === '') {
            $mensajeError = 'Por favor complete todos los campos';
            return compact('mensajeError', 'csrf_token');
        }

        $usuario = $this->model->buscarPorDocumento($identificador);

        if ($usuario && password_verify($contrasena, $usuario['password'])) {
            regenerar_sesion();
            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_codigo'] = $usuario['codigo'];
            $_SESSION['usuario_cargo']  = $usuario['cargo'] ?? '';
            $_SESSION['rol']            = $usuario['rol'];
            $_SESSION['LAST_ACTIVITY']  = time();

            // Si la contraseña coincide con el número de documento (login con documento en ambos campos), sugerir modal
            if (password_verify($usuario['documento'], $usuario['password'])) {
                $_SESSION['mostrar_modal_cambio_pass'] = true;
            } else {
                unset($_SESSION['mostrar_modal_cambio_pass']);
            }

            rotar_token_csrf();

            header('Location: ' . BASE_URL . '?module=panel');
            exit();
        }

        // 🔒 Rate Limiting: solo contar intentos FALLIDOS (credenciales incorrectas)
        // Máximo 5 intentos fallidos de login con ventana de 1 minuto (60 segundos)
        verificar_rate_limit(5, 60, 'login');

        $mensajeError = 'Documento o contraseña incorrectos';
        return compact('mensajeError', 'csrf_token');
    }

    public function logout(): void
    {
        iniciar_sesion_segura();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: ' . BASE_URL);
        exit();
    }
}

