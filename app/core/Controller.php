<?php
// Controlador base para carga de vistas, verificacion de sesion y registro en bitacora

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/ImageOptimizer.php';

class Controller {

    public function view(string $viewName, array $data = []): void {
        extract($data);
        $childView = __DIR__ . "/../views/" . $viewName . ".php";

        if (file_exists($childView)) {
            require_once __DIR__ . "/../views/layouts/main.php";
        } else {
            die("Error: La vista '{$viewName}' no existe.");
        }
    }

    protected function checkAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        // Actualizar actividad y verificar estado del usuario cada 15 segundos
        $now = time();
        if (!isset($_SESSION['last_heartbeat']) || ($now - $_SESSION['last_heartbeat']) >= 15) {
            $_SESSION['last_heartbeat'] = $now;
            try {
                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();
                $userId = (int)$_SESSION['user_id'];
                $userModel->updateLastActivity($userId);

                $freshUser = $userModel->findById($userId);
                if (!$freshUser) {
                    Security::destroySession();
                    header("Location: " . BASE_URL . "/login");
                    exit;
                }
                $_SESSION['user_rol'] = $freshUser['rol'];
                $_SESSION['user_nombre'] = $freshUser['nombres_completos'];
            } catch (Exception) {
            }
        }
    }

    protected function logAction(string $action): void {
        try {
            require_once __DIR__ . '/../models/AuditLog.php';
            $audit = new AuditLog();
            $audit->insert([
                'usuario_id'     => $_SESSION['user_id'] ?? null,
                'usuario_nombre' => $_SESSION['user_nombre'] ?? 'Sistema',
                'accion'         => $action,
                'ip'             => Security::getIP()
            ]);
        } catch (Exception $e) {
            error_log("Error al registrar auditoria: " . $e->getMessage());
        }
    }
}
