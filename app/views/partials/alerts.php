<?php
// Mensajes flash de exito o error en sesion
$msgError = $_SESSION['flash_error'] ?? null;
$msgSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>

<?php if (!empty($msgError)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= Security::sanitize((string)$msgError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!empty($msgSuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= Security::sanitize((string)$msgSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>
