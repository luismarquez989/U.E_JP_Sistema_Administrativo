<?php
// Plantilla principal del sistema
$childView = $childView ?? '';
$esVistaActas = str_ends_with((string)$childView, 'actas.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema Administrativo de Control de Actas de Graduación y Expedientes de Personal de la U.E. Josefina Pimentel.">
    <meta name="theme-color" content="#1e293b">
    <title>U.E. Josefina Pimentel - Sistema Administrativo</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">

<?php if (isset($_SESSION['user_id'])): ?>
    <?php include __DIR__ . "/../partials/navbar.php"; ?>
<?php endif; ?>

<main class="container main-content mb-5">
    <?php
    if (!empty($childView) && file_exists($childView)) {
        include $childView;
    }
    ?>
</main>

<?php include __DIR__ . "/../partials/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<?php if ($esVistaActas): ?>
    <script src="<?= BASE_URL ?>/assets/js/html2pdf.bundle.min.js" defer></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= time() ?>" defer></script>
</body>
</html>
