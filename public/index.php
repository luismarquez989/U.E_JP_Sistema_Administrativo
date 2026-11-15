<?php
// Enrutador principal del sistema

date_default_timezone_set('America/Caracas');

ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_samesite', 'Strict');
session_start();

header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// Detectar HTTPS tanto en servidor directo como detras de proxy o Cloudflare (hosting online)
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

http_response_code(404);
require_once "../app/views/layouts/404.php";
