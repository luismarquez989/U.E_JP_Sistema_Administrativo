<?php
// Conexion a la base de datos con PDO

date_default_timezone_set('America/Caracas');

class Database {
    private static ?PDO $conn = null;

    public static function getConnection(): PDO {
        if (self::$conn === null) {
            $httpHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $isLocal = str_contains($httpHost, 'localhost')
                || str_starts_with($httpHost, '127.0.0.1')
                || $httpHost === '[::1]';

            $host = getenv('DB_HOST') ?: ($isLocal ? 'localhost' : 'sql312.infinityfree.com');
            $db   = getenv('DB_NAME') ?: ($isLocal ? 'uejosefinapimentel' : 'if0_43450560_uejp');
            $user = getenv('DB_USER') ?: ($isLocal ? 'root' : 'if0_43450560');
            $pass = getenv('DB_PASS') ?: ($isLocal ? '' : 'euadmin123');

            try {
                $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
                self::$conn = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                try {
                    self::$conn->exec("SET time_zone = '-04:00'");
                } catch (PDOException) {
                }
            } catch (PDOException) {
                if (PHP_SAPI === 'cli') {
                    die("Error de conexión con la base de datos.\n");
                }
                $isAjax = isset($_GET['ajax'])
                    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
                if ($isAjax) {
                    http_response_code(500);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'error' => 'Error de conexión con la base de datos.']);
                    exit;
                }
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $errorCode    = 500;
                $errorTitle   = 'Error de Conexión con el Servidor de Datos';
                $errorMessage = 'No fue posible establecer comunicación con la base de datos en este momento.';
                $errorHint    = 'Verifique que el servicio MySQL esté activo o intente recargar la página en unos segundos.';
                $errorView    = __DIR__ . '/../app/views/layouts/500.php';
                if (file_exists($errorView)) {
                    require $errorView;
                    exit;
                }
                http_response_code(500);
                die("Error de conexión con la base de datos.");
            }
        }
        return self::$conn;
    }
}
