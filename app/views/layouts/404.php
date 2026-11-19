<?php
// Pagina de error 404 (Recurso o ruta no encontrada)
if (!defined('BASE_URL')) {
    $isHttps404 = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https')
        || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
    $proto404 = $isHttps404 ? "https://" : "http://";
    $scriptDir404 = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir404 = (string)preg_replace('#/public$#i', '', $scriptDir404);
    $basePath404 = rtrim($scriptDir404, '/');
    define('BASE_URL', $proto404 . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath404);
}

http_response_code(404);
$errorTitle   = !empty($errorTitle) ? (string)$errorTitle : 'Página o Recurso no Encontrado';
$errorMessage = !empty($errorMessage) ? (string)$errorMessage : 'La dirección o el documento que intenta consultar no existe o fue movido.';
$inicioUrl    = !empty($_SESSION['user_id']) ? (BASE_URL . '/panel') : (BASE_URL . '/login');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Administrativo para la gestión y consulta de Actas de Graduación y Expedientes de Personal de la U.E. Josefina Pimentel.">
    <meta name="theme-color" content="#1e293b">
    <title>404 - <?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?> | U.E. Josefina Pimentel</title>
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
                <div class="mb-2">
                    <span class="display-3 fw-bold text-danger">404</span>
                </div>
                <h3 class="fw-bold text-dark mb-2"><?= htmlspecialchars($errorTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-secondary mb-4"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <button type="button" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '<?= htmlspecialchars($inicioUrl, ENT_QUOTES, 'UTF-8') ?>'; }" class="btn btn-outline-secondary fw-semibold px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver atrás
                    </button>
                    <a href="<?= htmlspecialchars($inicioUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-house-door-fill me-1"></i> Volver al Inicio
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
