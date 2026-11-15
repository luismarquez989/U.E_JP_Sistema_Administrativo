<?php
// Vista del panel principal
?>
<div class="p-4 p-md-5 mb-4 card card-custom">
    <div class="container-fluid py-2">
        <h1 class="display-5 fw-bold text-dark">Panel Principal de Gestión</h1>
        <p class="col-md-9 fs-5 text-secondary">U.E. Josefina Pimentel — Sistema Integral de Expedientes de Personal, Actas de Graduación y Control Administrativo.</p>
        <hr class="my-4">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card card-custom h-100 p-3 border">
                    <div class="card-body">
                        <h3 class="card-title text-primary"><i class="bi bi-mortarboard-fill me-2"></i>Actas de Graduación</h3>
                        <p class="card-text text-muted">Registro, consulta y digitalización de actas de graduación por año escolar, con buscador inteligente de graduados y respaldo fotográfico del libro físico.</p>
                        <a href="<?= BASE_URL ?>/actas" class="btn btn-outline-primary">Ir a Actas de Graduación</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-custom h-100 p-3 border">
                    <div class="card-body">
                        <h3 class="card-title text-success"><i class="bi bi-folder2-open me-2"></i>Expedientes de Personal</h3>
                        <p class="card-text text-muted">Perfiles del personal (Docente, Obrero, Administrativo y Directivo), datos de salud y expedientes con justificativos.</p>
                        <a href="<?= BASE_URL ?>/personal" class="btn btn-outline-success">Ir a Expedientes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
