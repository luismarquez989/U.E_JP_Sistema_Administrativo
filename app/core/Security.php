<?php
// Utilidades de seguridad: tokens CSRF, limpieza de entradas y control de sesion

class Security {

    public static function destroySession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        self::clearActiveUserCookie();
    }

    public static function setActiveUserCookie(int $userId): void {
        if ($userId <= 0 || headers_sent()) {
            return;
        }
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
            || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
        setcookie('uejp_active_uid', (string)$userId, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        $_COOKIE['uejp_active_uid'] = (string)$userId;
    }

    public static function getActiveUserCookie(): int {
        return isset($_COOKIE['uejp_active_uid']) && ctype_digit((string)$_COOKIE['uejp_active_uid'])
            ? (int)$_COOKIE['uejp_active_uid']
            : 0;
    }

    public static function clearActiveUserCookie(): void {
        if (!headers_sent()) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
                || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
            setcookie('uejp_active_uid', '', [
                'expires'  => time() - 42000,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        unset($_COOKIE['uejp_active_uid']);
    }

    public static function generateCSRFToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken(?string $token): bool {
        if (!isset($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            if (class_exists('Controller')) {
                Controller::renderError(
                    403,
                    'Sesión o Token de Seguridad Expirado',
                    'No se pudo validar el token de seguridad de su solicitud.',
                    'Esto suele ocurrir si la pestaña permaneció inactiva por mucho tiempo. Regrese a la página anterior, recárguela e intente nuevamente.'
                );
            }
            http_response_code(403);
            require __DIR__ . '/../views/layouts/500.php';
            exit;
        }
        return true;
    }

    public static function sanitize(mixed $data): mixed {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim((string)($data ?? '')), ENT_QUOTES, 'UTF-8');
    }

    public static function getIP(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return (string)$_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }
}
