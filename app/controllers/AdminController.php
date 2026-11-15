<?php
/* Controlador de administracion de usuarios y permisos */

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/UserModel.php';

class AdminController extends Controller {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /* Verifica que el usuario logueado tenga rol de administrador */
    private function verificarAccesoAdmin(): string {
        $this->checkAuth();
        $rol = (string)($_SESSION['user_rol'] ?? '');
        if (!in_array($rol, ['admin', 'admin_mayor'], true)) {
            header("Location: " . BASE_URL . "/panel");
            exit;
        }
        return $rol;
    }

    public function index(): void {
        $currentRol = $this->verificarAccesoAdmin();
        $usuarios = $this->userModel->getAll();

        $this->view("admin", [
            'usuarios'      => $usuarios,
            'currentRol'    => $currentRol,
            'currentUserId' => (int)($_SESSION['user_id'] ?? 0)
        ]);
    }

    public function storeUser(): void {
        $currentRol = $this->verificarAccesoAdmin();
        Security::verifyCSRFToken((string)($_POST['csrf_token'] ?? ''));

        $nacionalidad = strtoupper(trim((string)($_POST['nacionalidad'] ?? 'V')));
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $nacionalidad = 'V';
        }
        $cedula = trim((string)($_POST['cedula'] ?? ''));

        if (preg_match('/^([VEve])[- ]?([0-9]+)$/', $cedula, $matches)) {
            $nacionalidad = strtoupper($matches[1]);
            $cedula = $matches[2];
        }

        $nombres = mb_substr(trim((string)($_POST['nombres'] ?? '')), 0, 120, 'UTF-8');
        $password = (string)($_POST['password'] ?? '');
        $solicitadoRol = trim((string)($_POST['rol'] ?? 'regular'));

        if (empty($cedula) || empty($nombres) || empty($password)) {
            $_SESSION['flash_error'] = 'Todos los campos obligatorios deben ser completados.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if (!ctype_digit($cedula) || strlen($cedula) > 10) {
            $_SESSION['flash_error'] = 'La cédula debe contener exclusivamente números (máximo 10 dígitos).';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if (strlen($password) < 8 || strlen($password) > 72 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $_SESSION['flash_error'] = 'La contraseña es insegura: debe tener al menos 8 caracteres, letras y números.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        /* Validar que un admin normal no pueda crear otros administradores */
        if ($currentRol === 'admin') {
            if ($solicitadoRol !== 'regular') {
                $_SESSION['flash_error'] = 'Los administradores solo pueden registrar usuarios con rol Regular.';
                header("Location: " . BASE_URL . "/usuarios");
                exit;
            }
            $rol = 'regular';
        } else {
            if ($solicitadoRol === 'admin_mayor') {
                $_SESSION['flash_error'] = 'No se puede crear otro Admin Mayor en el sistema.';
                header("Location: " . BASE_URL . "/usuarios");
                exit;
            }
            $rol = in_array($solicitadoRol, ['admin', 'regular'], true) ? $solicitadoRol : 'regular';
        }

        if ($this->userModel->findByCedula($cedula, $nacionalidad) !== false) {
            $_SESSION['flash_error'] = "La cédula {$nacionalidad}-{$cedula} ya se encuentra registrada en el sistema.";
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        try {
            $this->userModel->create([
                'nacionalidad'       => $nacionalidad,
                'cedula'             => $cedula,
                'nombres_completos'  => $nombres,
                'password'           => password_hash($password, PASSWORD_DEFAULT),
                'rol'                => $rol,
                'activo'             => 1,
                'debe_cambiar_clave' => 1
            ]);

            $this->logAction("Creó un nuevo usuario: {$nombres} (Cédula: {$nacionalidad}-{$cedula}, Rol: {$rol}).");
            $_SESSION['flash_success'] = 'Usuario registrado exitosamente.';
        } catch (Exception) {
            $_SESSION['flash_error'] = 'Error al registrar el usuario en la base de datos.';
        }

        header("Location: " . BASE_URL . "/usuarios");
        exit;
    }

    public function updateUser(): void {
        $currentRol = $this->verificarAccesoAdmin();
        Security::verifyCSRFToken((string)($_POST['csrf_token'] ?? ''));

        $userId = (int)($_POST['user_id'] ?? 0);
        $targetUser = $userId > 0 ? $this->userModel->findById($userId) : false;

        if (!is_array($targetUser)) {
            $_SESSION['flash_error'] = 'El usuario solicitado no existe.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        $targetRol = (string)($targetUser['rol'] ?? 'regular');

        if ($currentRol === 'admin' && in_array($targetRol, ['admin', 'admin_mayor'], true)) {
            $_SESSION['flash_error'] = 'Los administradores no tienen permisos para modificar a otros administradores.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        $nacionalidad = strtoupper(trim((string)($_POST['nacionalidad'] ?? 'V')));
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $nacionalidad = 'V';
        }
        $cedula = trim((string)($_POST['cedula'] ?? ''));

        if (preg_match('/^([VEve])[- ]?([0-9]+)$/', $cedula, $matches)) {
            $nacionalidad = strtoupper($matches[1]);
            $cedula = $matches[2];
        }

        $nombres = mb_substr(trim((string)($_POST['nombres'] ?? '')), 0, 120, 'UTF-8');
        $solicitadoRol = trim((string)($_POST['rol'] ?? $targetRol));
        $password = (string)($_POST['password'] ?? '');

        if (empty($cedula) || empty($nombres)) {
            $_SESSION['flash_error'] = 'La cédula y el nombre son campos obligatorios.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if (!ctype_digit($cedula) || strlen($cedula) > 10) {
            $_SESSION['flash_error'] = 'La cédula debe contener exclusivamente números (máximo 10 dígitos).';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        /* Comprobar que la cedula no pertenezca a otro usaurio */
        $existente = $this->userModel->findByCedula($cedula, $nacionalidad);
        if (is_array($existente) && (int)($existente['id'] ?? 0) !== $userId) {
            $_SESSION['flash_error'] = "La cédula {$nacionalidad}-{$cedula} ya pertenece a otro usuario registrado.";
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if ($targetRol === 'admin_mayor') {
            $nuevoRol = 'admin_mayor';
        } elseif ($currentRol === 'admin') {
            $nuevoRol = 'regular';
        } else {
            if ($solicitadoRol === 'admin_mayor') {
                $_SESSION['flash_error'] = 'No se puede asignar el rol de Admin Mayor.';
                header("Location: " . BASE_URL . "/usuarios");
                exit;
            }
            $nuevoRol = in_array($solicitadoRol, ['admin', 'regular'], true) ? $solicitadoRol : 'regular';
        }

        $dataUpdate = [
            'nacionalidad'      => $nacionalidad,
            'cedula'            => $cedula,
            'nombres_completos' => $nombres,
            'rol'               => $nuevoRol
        ];

        if (!empty($password)) {
            if (strlen($password) < 8 || strlen($password) > 72 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $_SESSION['flash_error'] = 'La nueva contraseña debe contener al menos 8 caracteres combinando letras y números.';
                header("Location: " . BASE_URL . "/usuarios");
                exit;
            }
            $dataUpdate['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $this->userModel->update($userId, $dataUpdate);

            if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
                $_SESSION['user_nombre'] = $nombres;
                $_SESSION['user_cedula'] = $cedula;
                $_SESSION['user_nacionalidad'] = $nacionalidad;
            }

            $this->logAction("Actualizó los datos del usuario ID #{$userId}: {$nombres} (Rol: {$nuevoRol}).");
            $_SESSION['flash_success'] = 'Usuario actualizado exitosamente.';
        } catch (Exception) {
            $_SESSION['flash_error'] = 'Error al actualizar el usuario en la base de datos.';
        }

        header("Location: " . BASE_URL . "/usuarios");
        exit;
    }

    public function deleteUser(): void {
        $currentRol = $this->verificarAccesoAdmin();
        Security::verifyCSRFToken((string)($_POST['csrf_token'] ?? ''));

        $userId = (int)($_POST['user_id'] ?? 0);
        $targetUser = $userId > 0 ? $this->userModel->findById($userId) : false;

        if (!is_array($targetUser)) {
            $_SESSION['flash_error'] = 'El usuario solicitado para eliminar no existe.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['flash_error'] = 'No puede eliminar su propia cuenta de usuario.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        $targetRol = (string)($targetUser['rol'] ?? 'regular');

        if ($targetRol === 'admin_mayor') {
            $_SESSION['flash_error'] = 'No se puede eliminar al Admin Mayor del sistema.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        if ($currentRol === 'admin' && $targetRol !== 'regular') {
            $_SESSION['flash_error'] = 'Los administradores solo pueden eliminar cuentas con rol Regular.';
            header("Location: " . BASE_URL . "/usuarios");
            exit;
        }

        try {
            $nombreBorrado = (string)($targetUser['nombres_completos'] ?? '');
            $cedBorrado = ((string)($targetUser['nacionalidad'] ?? 'V')) . '-' . ((string)($targetUser['cedula'] ?? ''));

            $this->userModel->delete($userId);
            $this->logAction("Eliminó al usuario ID #{$userId}: {$nombreBorrado} (Cédula: {$cedBorrado}, Rol: {$targetRol}).");
            $_SESSION['flash_success'] = 'Usuario eliminado exitosamente.';
        } catch (Exception) {
            $_SESSION['flash_error'] = 'Error al eliminar el usuario en la base de datos.';
        }

        header("Location: " . BASE_URL . "/usuarios");
        exit;
    }
}
