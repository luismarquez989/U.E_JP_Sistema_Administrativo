<?php
// Vista de inicio de sesion
?>
<div class="login-wrapper">
    <div class="text-center mb-3">
        <img src="<?= BASE_URL ?>/assets/img/photo.jpg"
             alt="Escudo U.E. Josefina Pimentel"
             class="login-logo-img img-fluid">
    </div>

    <div class="card card-custom login-card p-3 p-sm-4">
        <div class="text-center mb-3 mb-sm-4">
            <h3 class="fw-bold text-dark mb-1">U.E. Josefina Pimentel</h3>
            <p class="text-muted mb-0">Portal Administrativo</p>
        </div>
        <?php include __DIR__ . "/partials/alerts.php"; ?>
        <form action="<?= BASE_URL ?>/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">Cédula de Identidad</label>
                <div class="input-group">
                    <select name="nacionalidad" class="form-select input-nacionalidad-select" aria-label="Nacionalidad" required>
                        <option value="V" selected>V</option>
                        <option value="E">E</option>
                    </select>
                    <input type="text" name="cedula" class="form-control" required maxlength="10" placeholder="Ej. 12345678" pattern="[0-9]+" inputmode="numeric" title="Solo se permiten números" autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Contraseña</label>
                <input type="password" name="password" class="form-control" required maxlength="72" placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Ingresar al Sistema</button>
        </form>
    </div>
</div>
