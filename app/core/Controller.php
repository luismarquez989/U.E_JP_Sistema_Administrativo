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
            self::renderError(500, 'Vista no disponible', "No fue posible cargar la interfaz solicitada ({$viewName}).");
        }
    }

    public static function renderError(int $code = 500, string $title = '', string $message = '', string $hint = ''): never {
        $isAjax = isset($_GET['ajax'])
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isAjax) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'    => false,
                'error' => $message ?: ($title ?: 'Error interno en el servidor.')
            ]);
            exit;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($code);
        $errorCode    = $code;
        $errorTitle   = $title;
        $errorMessage = $message;
        $errorHint    = $hint;

        if ($code === 404 && file_exists(__DIR__ . "/../views/layouts/404.php")) {
            require __DIR__ . "/../views/layouts/404.php";
            exit;
        }

        require __DIR__ . "/../views/layouts/500.php";
        exit;
    }

    protected function checkAuth(bool $skipHeartbeat = false): void {
        if (!isset($_SESSION['user_id'])) {
            $expiredUid = Security::getActiveUserCookie();
            if ($expiredUid > 0) {
                try {
                    require_once __DIR__ . '/../models/UserModel.php';
                    $userModel = new UserModel();
                    $userModel->clearLastActivity($expiredUid);
                } catch (Exception) {
                }
                Security::clearActiveUserCookie();
            }
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        if ($skipHeartbeat) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        if (Security::getActiveUserCookie() !== $userId) {
            Security::setActiveUserCookie($userId);
        }

        // Actualizar actividad y verificar estado del usuario cada 10 segundos
        $now = time();
        if (!isset($_SESSION['last_heartbeat']) || ($now - $_SESSION['last_heartbeat']) >= 10) {
            $_SESSION['last_heartbeat'] = $now;
            try {
                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();
                $userModel->updateLastActivity($userId);

                $freshUser = $userModel->findById($userId);
                if (!$freshUser) {
                    $userModel->clearLastActivity($userId);
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
