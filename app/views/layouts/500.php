<?php
// Vista de contingencia para errores del servidor (500 y excepciones controladas)
if (!defined('BASE_URL')) {
    $isHttpsErr = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
    $protoErr = $isHttpsErr ? "https://" : "http://";
    $scriptDirErr = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDirErr = (string)preg_replace('#/public$#i', '', $scriptDirErr);
    $basePathErr = rtrim($scriptDirErr, '/');
    define('BASE_URL', $protoErr . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePathErr);
}

$errorCode = (int)($errorCode ?? (http_response_code() ?: 500));
if ($errorCode < 400) {
    $errorCode = 500;
}
http_response_code($errorCode);

$errorTitle   = !empty($errorTitle) ? (string)$errorTitle : 'Error en el Servidor';
$errorMessage = !empty($errorMessage) ? (string)$errorMessage : 'Ocurrió un inconveniente inesperado al procesar su solicitud en el servidor.';
$errorHint    = !empty($errorHint) ? (string)$errorHint : 'Por favor, intente recargar la página en unos segundos o regrese al inicio. Si el inconveniente persiste, verifique la conexión del servidor.';
$inicioUrl    = !empty($_SESSION['user_id']) ? (BASE_URL . '/panel') : (BASE_URL . '/login');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Administrativo para la gestión y consulta de Actas de Graduación y Expedientes de Personal de la U.E. Josefina Pimentel.">
    <meta name="theme-color" content="#1e293b">
    <title><?= $errorCode ?> - <?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?> | U.E. Josefina Pimentel</title>
    <link rel="icon" type="image/jpeg" href="<?= BASE_URL ?>/assets/img/photo.jpg">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/photo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<?php if (!empty($_SESSION['user_id']) && file_exists(__DIR__ . '/../partials/navbar.php')): ?>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
<?php else: ?>
    <nav class="navbar navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold mb-0" href="<?= htmlspecialchars($inicioUrl, ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-mortarboard-fill me-2"></i>U.E. Josefina Pimentel
            </a>
        </div>
    </nav>
<?php endif; ?>

<main class="container my-auto py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 p-sm-5 text-center">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger" style="width: 76px; height: 76px;">
                        <i class="bi bi-hdd-network-fill fs-1"></i>
                    </span>
                </div>

                <div class="mb-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-1 rounded-pill fw-semibold">
                        Código de estado: <?= $errorCode ?>
                    </span>
                </div>

                <h2 class="fw-bold text-dark mb-2"><?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="text-secondary mb-3"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>

                <div class="alert alert-light border text-muted small text-start mb-4 py-2 px-3">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                    <?= htmlspecialchars($errorHint, ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" onclick="window.location.reload();" class="btn btn-outline-secondary fw-semibold px-3">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reintentar
                    </button>
                    <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '<?= htmlspecialchars($inicioUrl, ENT_QUOTES, 'UTF-8') ?>'; }" class="btn btn-outline-primary fw-semibold px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver atrás
                    </button>
                    <a href="<?= htmlspecialchars($inicioUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-house-door-fill me-1"></i> Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if (file_exists(__DIR__ . '/../partials/footer.php')): ?>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
