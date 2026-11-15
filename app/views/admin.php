<?php
/* Vista de control de usuarios y roles del sistema */
$usuarios = $usuarios ?? [];
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentRol = $currentRol ?? ($_SESSION['user_rol'] ?? 'regular');
?>

<div class="container-fluid px-0" id="moduloUsuariosContainer" data-current-rol="<?= htmlspecialchars($currentRol, ENT_QUOTES, 'UTF-8') ?>">

    <?php include __DIR__ . "/partials/alerts.php"; ?>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center me-2 overflow-hidden">
                <h5 class="fw-bold mb-0 text-dark text-nowrap personal-list-title">
                    <i class="bi bi-people-fill text-primary me-2"></i>Lista de Usuarios
                </h5>
                <span class="badge bg-secondary rounded-pill ms-2 personal-list-badge"><?= count($usuarios) ?></span>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 rounded-pill shadow-sm fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="bi bi-person-plus-fill me-1"></i><span>Nuevo Usuario</span>
            </button>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 user-table-list">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center user-col-num">#</th>
                            <th class="user-col-cedula">Cédula</th>
                            <th>Nombre Completo</th>
                            <th class="text-center user-col-rol">Rol</th>
                            <th class="text-center user-col-conexion">Estado</th>
                            <th class="text-end user-col-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-5 d-block mb-2 text-secondary"></i>
                                    No hay usuarios registrados en el sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $index => $u):
                                $uId = (int)$u['id'];
                                $uRol = $u['rol'] ?? 'regular';
                                $uNacionalidad = $u['nacionalidad'] ?? 'V';
                                $uCedula = $u['cedula'] ?? '';
                                $uNombre = $u['nombres_completos'] ?? '';
                                $isSelf = ($uId === $currentUserId);
                                $segundosInactivo = isset($u['segundos_inactivo']) && $u['segundos_inactivo'] !== null ? (int)$u['segundos_inactivo'] : null;
                                $isOnline = ($segundosInactivo !== null && $segundosInactivo >= 0 && $segundosInactivo <= 300) || $isSelf;

                                /* Determinar permisos de edicion y borrado segun jerarquia */
                                $canEdit = false;
                                $canDelete = false;

                                if ($currentRol === 'admin_mayor') {
                                    $canEdit = ($isSelf || in_array($uRol, ['admin', 'regular'], true));
                                    $canDelete = (!$isSelf && in_array($uRol, ['admin', 'regular'], true));
                                } elseif ($currentRol === 'admin') {
                                    $canEdit = ($uRol === 'regular');
                                    $canDelete = ($uRol === 'regular');
                                }
                            ?>
                            <tr class="<?= $isSelf ? 'table-primary bg-opacity-10' : '' ?>">
                                <td class="text-center text-muted fw-bold"><?= $index + 1 ?></td>
                                <td class="fw-bold text-dark text-nowrap">
                                    <?= Security::sanitize($uNacionalidad . '-' . $uCedula) ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold text-dark user-table-nombre" title="<?= Security::sanitize($uNombre) ?>">
                                            <?= Security::sanitize($uNombre) ?>
                                        </span>
                                        <?php if ($isSelf): ?>
                                            <span class="badge bg-primary rounded-pill badge-tu">Tú</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($uRol === 'admin_mayor'): ?>
                                        <span class="badge badge-role-pill badge-role-admin-mayor">
                                            <i class="bi bi-star-fill me-1"></i>Admin Mayor
                                        </span>
                                    <?php elseif ($uRol === 'admin'): ?>
                                        <span class="badge badge-role-pill badge-role-admin">
                                            <i class="bi bi-shield-lock-fill me-1"></i>Administrador
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-role-pill badge-role-regular">
                                            <i class="bi bi-person-fill me-1"></i>Regular
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isOnline): ?>
                                        <span class="badge badge-status-pill badge-status-online">
                                            <i class="bi bi-circle-fill me-1 text-success"></i>Conectado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-status-pill badge-status-offline">
                                            <i class="bi bi-circle me-1 text-muted"></i>Desconectado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end user-actions-cell align-middle">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-outline-secondary fw-semibold btn-editar-usuario"
                                                    data-id="<?= $uId ?>"
                                                    data-nacionalidad="<?= htmlspecialchars($uNacionalidad, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-cedula="<?= htmlspecialchars((string)$uCedula, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-nombres="<?= htmlspecialchars($uNombre, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-rol="<?= htmlspecialchars($uRol, ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Editar este usuario">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($canDelete): ?>
                                            <button type="button" class="btn btn-outline-danger fw-semibold btn-eliminar-usuario"
                                                    data-id="<?= $uId ?>"
                                                    data-cedula="<?= htmlspecialchars($uNacionalidad . '-' . $uCedula, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-nombre="<?= htmlspecialchars($uNombre, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-rol="<?= htmlspecialchars($uRol, ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Eliminar este usuario">
                                                <i class="bi bi-trash3 me-1"></i> Eliminar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/modals/modals_usuarios.php"; ?>
