<?php
// Enrutador principal del sistema

date_default_timezone_set('America/Caracas');
ini_set('display_errors', '0');
ob_start();

ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Strict');
session_start();

header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Detectar HTTPS
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
    || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);

$protocol = $isHttps ? "https://" : "http://";
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$scriptDir = (string)preg_replace('#/public$#i', '', $scriptDir);
$basePath = rtrim($scriptDir, '/');
define('BASE_URL', $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);

require_once '../config/Database.php';
require_once '../app/core/Security.php';
require_once '../app/core/ImageOptimizer.php';
require_once '../app/core/Controller.php';

// Manejador global de excepciones no capturadas para mostrar vista amigable 500
set_exception_handler(function (Throwable $e): void {
    error_log("Excepcion en servidor: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    Controller::renderError(
        500,
        'Error Interno en el Servidor',
        'Ocurrió un inconveniente inesperado al procesar la operación solicitada.',
        'El sistema detuvo la operación de forma segura. Intente recargar la página o regrese al panel principal.'
    );
});

// Manejador de errores fatales de ejecucion
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("Error fatal PHP: " . $error['message'] . " en " . $error['file'] . ":" . $error['line']);
        Controller::renderError(
            500,
            'Error en el Servidor',
            'El servidor encontró un error crítico al procesar la solicitud.',
            'Por favor, recargue la página o regrese al inicio.'
        );
    }
});

// Limpiar la URL solicitada
$rawUrl = trim((string)($_GET['url'] ?? ''), '/');
$rawUrl = (string)preg_replace('#^public/?#i', '', $rawUrl);
$url = $rawUrl !== '' ? explode('/', filter_var($rawUrl, FILTER_SANITIZE_URL)) : [];

if (empty($url[0])) {
    $destino = isset($_SESSION['user_id']) ? '/panel' : '/login';
    header("Location: " . BASE_URL . $destino);
    exit;
}

$segmento = strtolower($url[0]);
$accion = $url[1] ?? '';
$params = array_slice($url, 2);

if ($segmento === '500' || $segmento === 'error') {
    Controller::renderError(500);
}

// Rutas disponibles en la aplicacion
$rutas = [
    'login'     => ['AuthController', ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'processLogin' : 'login'],
    'logout'    => ['AuthController', 'logout'],
    'auth'      => ['AuthController', $accion ?: 'login'],
    'panel'     => ['DashboardController', $accion ?: 'index'],
    'dashboard' => ['DashboardController', $accion ?: 'index'],
    'personal'  => ['PersonalController', $accion ?: 'index'],
    'actas'     => ['ActasController', $accion ?: 'index'],
    'usuarios'  => ['AdminController', $accion ?: 'index'],
    'admin'     => ['AdminController', $accion ?: 'index'],
    'auditoria' => ['LogsController', $accion ?: 'index'],
    'logs'      => ['LogsController', $accion ?: 'index']
];

if (isset($rutas[$segmento])) {
    [$controllerName, $methodName] = $rutas[$segmento];
    $controllerFile = "../app/controllers/" . $controllerName . ".php";

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();

        if (method_exists($controller, $methodName)) {
            call_user_func_array([$controller, $methodName], $params);
            exit;
        }
    }
}

Controller::renderError(404);
