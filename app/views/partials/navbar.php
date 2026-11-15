<?php
// Barra de navegacion superior
if (!isset($_SESSION['user_id'])) {
    return;
}
$userNombreClean = Security::sanitize((string)($_SESSION['user_nombre'] ?? 'Usuario'));
$userRolActual = (string)($_SESSION['user_rol'] ?? '');
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4" data-bs-theme="dark">
    <div class="container-fluid">
        <div class="navbar-left-group d-flex flex-column flex-lg-row align-items-start align-items-lg-center me-auto">
            <a class="navbar-brand fw-bold mb-0 me-lg-3 text-nowrap navbar-brand-fixed" href="<?= BASE_URL ?>/panel">
                <i class="bi bi-mortarboard-fill me-2"></i>U.E. Josefina Pimentel
            </a>
            <div class="navbar-user-box d-flex align-items-center">
                <span class="navbar-user-divider d-none d-lg-inline-block"></span>
                <span class="navbar-user-chip" title="<?= $userNombreClean ?>">
                    <?= $userNombreClean ?>
                </span>
            </div>
        </div>

        <button class="navbar-toggler ms-2 flex-shrink-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-lg-center mt-3 mt-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/panel"><i class="bi bi-house-door me-1"></i> Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/actas"><i class="bi bi-journal-bookmark-fill me-1"></i> Actas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/personal"><i class="bi bi-folder2-open me-1"></i> Expedientes</a></li>
                <?php if (in_array($userRolActual, ['admin', 'admin_mayor'], true)): ?>
                    <li class="nav-item"><a class="nav-link text-warning" href="<?= BASE_URL ?>/usuarios"><i class="bi bi-shield-lock me-1"></i> Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link text-info" href="<?= BASE_URL ?>/auditoria"><i class="bi bi-journal-text me-1"></i> Auditoría</a></li>
                <?php endif; ?>

                <li class="nav-item d-none d-lg-block ms-lg-3">
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-light px-3">
                        <i class="bi bi-box-arrow-right me-1"></i> Salir
                    </a>
                </li>

                <li class="nav-item d-lg-none mt-3 pt-2 border-top w-100 text-center">
                    <a href="<?= BASE_URL ?>/logout" class="btn btn-sm btn-outline-danger text-light w-100 py-2">
                        <i class="bi bi-box-arrow-right me-1"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
