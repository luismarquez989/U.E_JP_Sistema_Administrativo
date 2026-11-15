<?php
// Controlador de inicio y cierre de sesion

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends Controller {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login(): void {
        if (isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/panel");
            exit;
        }

        $this->view("login", []);
    }

    public function processLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $nacionalidad = strtoupper(trim((string)($_POST['nacionalidad'] ?? 'V')));
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $nacionalidad = 'V';
        }

        $cedula = trim((string)($_POST['cedula'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (preg_match('/^([VEve])[- ]?([0-9]+)$/', $cedula, $matches)) {
            $nacionalidad = strtoupper($matches[1]);
            $cedula = $matches[2];
        }

        if (empty($cedula) || empty($password)) {
            $_SESSION['flash_error'] = "Por favor, ingrese la cédula y la contraseña.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if (!ctype_digit($cedula)) {
            $_SESSION['flash_error'] = "La cédula debe contener únicamente números.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $user = $this->userModel->findByCedula($cedula, $nacionalidad);

        if (!is_array($user) || !password_verify($password, (string)($user['password'] ?? ''))) {
            $_SESSION['flash_error'] = "Cédula o contraseña incorrectas.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ((int)($user['activo'] ?? 1) === 0) {
            $_SESSION['flash_error'] = "Su cuenta se encuentra inactiva. Contacte al administrador.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        session_regenerate_id(true);
        $userId  = (int)($user['id'] ?? 0);
        $userNac = (string)($user['nacionalidad'] ?? 'V');
        $userCed = (string)($user['cedula'] ?? '');

        $_SESSION['user_id']           = $userId;
        $_SESSION['user_nacionalidad'] = $userNac;
        $_SESSION['user_cedula']       = $userCed;
        $_SESSION['user_nombre']       = (string)($user['nombres_completos'] ?? '');
        $_SESSION['user_rol']          = (string)($user['rol'] ?? 'regular');

        if ($userId > 0) {
            $this->userModel->updateLastActivity($userId);
        }

        $this->logAction("Inicio de sesión exitoso ({$userNac}-{$userCed}).");

        header("Location: " . BASE_URL . "/panel");
        exit;
    }

    public function logout(): void {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $this->userModel->clearLastActivity($userId);
            } catch (Exception) {
            }
        }

        $this->logAction("Cerró sesión en el sistema.");

        session_unset();
        session_destroy();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash_success'] = "Sesión cerrada correctamente.";
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}
