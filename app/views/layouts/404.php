<?php
// Pagina de error 404
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="text-center px-3">
        <h1 class="display-1 fw-bold text-danger">404</h1>
        <p class="fs-3">¡Oops! Página no encontrada.</p>
        <a href="<?= BASE_URL ?>/panel" class="btn btn-primary">Volver al Inicio</a>
    </div>
</body>
</html>
