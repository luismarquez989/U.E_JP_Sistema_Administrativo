<?php
/* Modales para crear, editar y eliminar usuarios */
$currentRol = $currentRol ?? ($_SESSION['user_rol'] ?? 'regular');
$csrfToken = Security::generateCSRFToken();
?>

<!-- Modal crear usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalCrearUsuarioLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/usuarios/storeUser" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cédula de Identidad (*)</label>
                        <div class="input-group">
                            <select name="nacionalidad" class="form-select input-nacionalidad-select" aria-label="Nacionalidad" required>
                                <option value="V" selected>V</option>
                                <option value="E">E</option>
                            </select>
                            <input type="text" name="cedula" class="form-control" required placeholder="Ej. 12345678" pattern="[0-9]+" inputmode="numeric" maxlength="10" title="Solo se permiten números" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombres y Apellidos (*)</label>
                        <input type="text" name="nombres" class="form-control" required maxlength="100" placeholder="Ej. Juan Pérez" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rol del Sistema (*)</label>
                        <?php if ($currentRol === 'admin_mayor'): ?>
                            <select name="rol" class="form-select" required>
                                <option value="regular" selected>Regular</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <div class="form-text text-muted small">
                                <i class="bi bi-info-circle me-1"></i>Como Admin Mayor, puede crear usuarios con rol Regular o Administrador.
                            </div>
                        <?php else: ?>
                            <select name="rol_display" class="form-select" disabled>
                                <option value="regular" selected>Regular</option>
                            </select>
                            <input type="hidden" name="rol" value="regular">
                            <div class="form-text text-muted small">
                                <i class="bi bi-info-circle me-1"></i>Los administradores solo pueden registrar usuarios con rol Regular.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Contraseña (*)</label>
                        <input type="password" name="password" class="form-control" required minlength="8" maxlength="72" placeholder="Mínimo 8 caracteres (letras y números)" autocomplete="off">
                        <div class="form-text small">Debe contener al menos 8 caracteres combinando letras y números.</div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal editar usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalEditarUsuarioLabel">
                    <i class="bi bi-pencil-square me-2"></i>Editar Datos del Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/usuarios/updateUser" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="user_id" id="editUserId" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cédula de Identidad (*)</label>
                        <div class="input-group">
                            <select name="nacionalidad" id="editNacionalidad" class="form-select input-nacionalidad-select" aria-label="Nacionalidad" required>
                                <option value="V">V</option>
                                <option value="E">E</option>
                            </select>
                            <input type="text" name="cedula" id="editCedula" class="form-control" required pattern="[0-9]+" inputmode="numeric" maxlength="10" title="Solo se permiten números" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombres y Apellidos (*)</label>
                        <input type="text" name="nombres" id="editNombres" class="form-control" required maxlength="100" autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rol del Sistema (*)</label>
                        <select name="rol" id="editRolSelect" class="form-select">
                            <option value="regular">Regular</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <input type="text" id="editRolFixedDisplay" class="form-control d-none" value="" disabled autocomplete="off">
                        <div id="editRolHelpText" class="form-text text-muted small"></div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Nueva Contraseña (Opcional)</label>
                        <input type="password" name="password" class="form-control" maxlength="72" placeholder="Dejar en blanco para conservar la actual" autocomplete="off">
                        <div class="form-text small">Complete este campo únicamente si desea cambiar la contraseña del usuario (mínimo 8 caracteres, letras y números).</div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark fw-bold px-4">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal eliminar usuario -->
<div class="modal fade" id="modalEliminarUsuario" tabindex="-1" aria-labelledby="modalEliminarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="modalEliminarUsuarioLabel">
                    <i class="bi bi-trash3-fill me-2"></i>Eliminar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/usuarios/deleteUser" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="user_id" id="deleteUserId" value="">

                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-triangle-fill display-3 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">¿Está seguro de que desea eliminar a este usuario?</h5>
                    <p class="text-danger fw-bold fs-5 mb-3 text-break" id="deleteUserNombreText"></p>
                    <div class="alert alert-secondary text-start small mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> Esta acción no se puede deshacer. Se revocarán de inmediato todos los accesos de esta persona al sistema.
                    </div>
                </div>

                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnConfirmDeleteUser" class="btn btn-danger fw-bold px-4" disabled>
                        <i class="bi bi-hourglass-split me-1"></i> Espere (5s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
