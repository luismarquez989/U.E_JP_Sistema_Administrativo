<?php
// vista principal de personal y consulta de expedientes
$lista = $lista ?? [];
$selected = $selected ?? null;
$expedientes = $expedientes ?? [];
$today = date('Y-m-d');
?>

<div class="container-fluid px-0">

    <?php include __DIR__ . "/partials/alerts.php"; ?>

    <?php if (!$selected): ?>
    <!-- listado general del personal -->
    <?php
    $totalActivosInicial = 0;
    foreach ($lista as $item) {
        if (($item['activo'] ?? 1) == 1) {
            $totalActivosInicial++;
        }
    }
    ?>

    <div class="search-filter-container mb-3 shadow-sm">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted ps-3 pe-2">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="liveSearchInput" class="form-control border-start-0 border-end-0 py-2 ps-1" maxlength="100" placeholder="Buscar personal por nombre, apellido o cédula..." autocomplete="off">
            <button type="button" class="btn btn-white border-start-0 border-end-0 text-muted clear-search-btn px-2" id="clearSearchBtn" title="Limpiar búsqueda" aria-label="Limpiar búsqueda">
                <i class="bi bi-x-circle"></i>
            </button>
            <button type="button" class="btn btn-filter-pill px-3 d-inline-flex align-items-center text-nowrap fw-semibold" data-bs-toggle="modal" data-bs-target="#modalFiltros" title="Filtrar por estado, cargo, sexo...">
                <i class="bi bi-funnel-fill text-primary me-1"></i>
                <span class="d-none d-sm-inline">Filtrar</span>
                <span id="filtrosActivosBadge" class="badge bg-primary ms-1 d-none">0</span>
            </button>
        </div>
    </div>

    <div id="activeFiltersNotice" class="alert alert-info py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 shadow-sm d-none">
        <span class="small"><i class="bi bi-info-circle-fill me-1"></i> Filtros aplicados a la lista. <span id="activeFiltersSummary" class="fw-semibold"></span></span>
        <button type="button" class="btn btn-sm btn-outline-info text-dark py-0" id="btnResetFilters"><i class="bi bi-arrow-counterclockwise"></i> Restablecer</button>
    </div>

    <div class="card card-custom shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center me-2 overflow-hidden">
                <h5 class="fw-bold mb-0 text-dark text-nowrap personal-list-title">Lista de personal</h5>
                <span class="badge bg-secondary rounded-pill ms-2 personal-list-badge" id="totalPersonalBadge"><?= $totalActivosInicial ?></span>
            </div>
            <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 rounded-pill shadow-sm fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCrearPersonal">
                <i class="bi bi-person-plus-fill me-1"></i><span>Añadir</span>
            </button>
        </div>

        <div class="card-body p-3">
            <div id="estadoCargandoPersonal" class="d-none text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Espere un momento, por favor...</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">Espere un momento, por favor...</h5>
                <p class="text-muted small mb-3">Consultando expedientes del personal en el sistema...</p>
                <div class="acta-loading-skeleton-box mx-auto">
                    <div class="acta-skeleton-line rounded mb-2"></div>
                    <div class="acta-skeleton-line rounded mb-2"></div>
                    <div class="acta-skeleton-line rounded"></div>
                </div>
            </div>

            <div id="personalCardsContainer" class="row g-3">
                <?php if (empty($lista)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-people display-4 d-block mb-3 text-secondary"></i>
                        <h5>No hay integrantes registrados en el personal.</h5>
                        <p class="small">Utilice el botón "Añadir" para registrar al primer integrante.</p>
                    </div>
                <?php else: ?>
                    <?php
                    $activosRenderizadosInicial = 0;
                    foreach ($lista as $p):
                        $fotoSrc = !empty($p['foto']) ? BASE_URL . "/personal/downloadFile?foto_id=" . $p['id'] : '';
                        $nombreCompleto = trim(($p['nombres'] ?? '') . ' ' . ($p['apellidos'] ?? ''));
                        $cedulaFormato = ($p['nacionalidad'] ?? 'V') . '-' . ($p['cedula'] ?? '');
                        $tieneEnfermedad = (($p['padece_enfermedad'] ?? 'No') === 'Si');
                        $isActivoItem = (($p['activo'] ?? 1) == 1);
                        $ocultarInicial = !$isActivoItem || ($activosRenderizadosInicial >= 10);
                        if ($isActivoItem) {
                            $activosRenderizadosInicial++;
                        }
                    ?>
                    <div class="col-12 personal-card-item <?= $ocultarInicial ? 'd-none' : '' ?> <?= !$isActivoItem ? 'personal-card-inactivo' : '' ?>"
                         data-nombre="<?= strtolower(htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8')) ?>"
                         data-cedula="<?= strtolower(htmlspecialchars((string)$p['cedula'], ENT_QUOTES, 'UTF-8')) ?>"
                         data-nacionalidad="<?= $p['nacionalidad'] ?? 'V' ?>"
                         data-cargo="<?= strtolower(htmlspecialchars($p['cargo'] ?? '', ENT_QUOTES, 'UTF-8')) ?>"
                         data-sexo="<?= $p['sexo'] ?? '' ?>"
                         data-estado-civil="<?= strtolower(htmlspecialchars($p['estado_civil'] ?? '', ENT_QUOTES, 'UTF-8')) ?>"
                         data-enfermedad="<?= $p['padece_enfermedad'] ?? 'No' ?>"
                         data-fecha-nac="<?= $p['fecha_nacimiento'] ?? '' ?>"
                         data-activo="<?= $isActivoItem ? '1' : '0' ?>">
                        <div class="card card-custom h-100 p-3 border shadow-sm personal-row-hover">
                            <div class="row align-items-center g-3">

                                <div class="col-12 col-lg-5">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <?php if ($fotoSrc): ?>
                                                <img src="<?= $fotoSrc ?>" alt="Foto" class="rounded-circle border avatar-list-img" width="58" height="58">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light text-primary border d-flex align-items-center justify-content-center fw-bold avatar-list-placeholder">
                                                    <?= strtoupper(substr($p['nombres'] ?? 'P', 0, 1) . substr($p['apellidos'] ?? '', 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                <h6 class="fw-bold mb-0 text-dark text-truncate" title="<?= Security::sanitize($nombreCompleto) ?>"><?= Security::sanitize($nombreCompleto) ?></h6>
                                                <?php if (!$isActivoItem): ?>
                                                    <span class="badge bg-secondary text-light py-0.5 px-2 fw-semibold small">
                                                        <i class="bi bi-person-slash me-1"></i>Inactivo
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($tieneEnfermedad): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0 px-2 fw-semibold badge-condicion-list"
                                                          data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top"
                                                          data-bs-content="<?= Security::sanitize($p['detalle_enfermedad'] ?: 'Sin detalle especificado') ?>"
                                                          title="Condición Médica">
                                                        <i class="bi bi-heart-pulse-fill me-1"></i>Cond. médica <i class="bi bi-eye-fill ms-1"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="small text-secondary">
                                                <i class="bi bi-person-vcard text-secondary me-1"></i><strong><?= Security::sanitize($cedulaFormato) ?></strong>
                                                <span class="mx-1 text-muted d-none d-sm-inline">•</span>
                                                <span class="d-block d-sm-inline mt-1 mt-sm-0 text-secondary">
                                                    <i class="bi bi-briefcase text-secondary me-1"></i>Cargo: <strong><?= Security::sanitize($p['cargo'] ?? 'No especificado') ?></strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-7 col-lg-4 small text-secondary">
                                    <div class="row g-1">
                                        <div class="col-12 col-sm-6 col-md-12 text-truncate"><i class="bi bi-telephone text-muted me-1"></i><?= Security::sanitize($p['telefono'] ?: 'Sin teléfono') ?></div>
                                        <div class="col-12 col-sm-6 col-md-12 text-truncate"><i class="bi bi-envelope text-muted me-1"></i><?= Security::sanitize($p['correo'] ?: 'Sin correo') ?></div>
                                        <div class="col-12">
                                            <span class="me-2">Sexo: <strong><?= Security::sanitize($p['sexo'] ?? 'N/A') ?></strong></span>
                                            <span>Estado Civil: <strong><?= Security::sanitize($p['estado_civil'] ?? 'N/A') ?></strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-5 col-lg-3 text-lg-end text-start">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="<?= BASE_URL ?>/personal?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm px-3 fw-semibold w-100 shadow-sm">
                                            <i class="bi bi-folder2-open me-1"></i> Ver Expediente
                                        </a>
                                        <?php if ($isActivoItem): ?>
                                            <div class="btn-group btn-group-sm w-100">
                                                <button type="button" class="btn btn-outline-secondary fw-semibold btn-editar-personal"
                                                        data-id="<?= $p['id'] ?>"
                                                        data-nombres="<?= htmlspecialchars($p['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-apellidos="<?= htmlspecialchars($p['apellidos'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-nacionalidad="<?= $p['nacionalidad'] ?? 'V' ?>"
                                                        data-cedula="<?= htmlspecialchars((string)$p['cedula'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-fecha-nac="<?= $p['fecha_nacimiento'] ?? '' ?>"
                                                        data-cargo="<?= htmlspecialchars($p['cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-telefono="<?= htmlspecialchars($p['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-correo="<?= htmlspecialchars($p['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-sexo="<?= $p['sexo'] ?? 'F' ?>"
                                                        data-estado-civil="<?= htmlspecialchars($p['estado_civil'] ?? 'Soltero/a', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-direccion="<?= htmlspecialchars($p['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-enfermedad="<?= $p['padece_enfermedad'] ?? 'No' ?>"
                                                        data-detalle-enfermedad="<?= htmlspecialchars($p['detalle_enfermedad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-foto="<?= $fotoSrc ?>"
                                                        data-origin="lista"
                                                        title="Editar datos de este integrante">
                                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                                </button>
                                                <button type="button" class="btn btn-outline-danger fw-semibold btn-deshabilitar-personal"
                                                        data-id="<?= $p['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-cedula="<?= htmlspecialchars($cedulaFormato, ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Deshabilitar este integrante">
                                                    <i class="bi bi-person-slash me-1"></i> Deshabilitar
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success btn-sm fw-semibold w-100 btn-habilitar-personal shadow-sm"
                                                    data-id="<?= $p['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-cedula="<?= htmlspecialchars($cedulaFormato, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-redirect="lista"
                                                    title="Habilitar nuevamente a este integrante">
                                                <i class="bi bi-person-check-fill me-1"></i> Habilitar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div id="spinnerScrollPersonal" class="d-none text-center py-3 col-12">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="noResultsNotice" class="text-center py-5 text-muted <?= ($totalActivosInicial === 0 && !empty($lista)) ? '' : 'd-none' ?>">
                <i class="bi bi-search display-5 d-block mb-2 text-secondary"></i>
                <h5>No se encontraron integrantes que coincidan con la búsqueda o filtros aplicados.</h5>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnResetSearch">Mostrar todos</button>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- vista del perfil y expedinete del personal seleccionado -->

    <div class="mb-3">
        <a href="<?= BASE_URL ?>/personal" class="btn btn-light border shadow-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista de personal
        </a>
    </div>

    <?php $isSelectedActivo = (($selected['activo'] ?? 1) == 1); ?>

    <?php if (!$isSelectedActivo): ?>
        <div class="alert alert-warning border-warning shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-person-slash fs-3 text-warning me-3"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Personal Deshabilitado / Inactivo</h6>
                <p class="small mb-0 text-muted">Este integrante está inactivo en el sistema. Sus expedientes y justificativos pueden consultarse, descargarse o imprimirse normalmente, pero no se permite modificar sus datos personales ni añadir nuevos registros al expediente hasta que vuelva a ser habilitado.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-3 p-md-4 mb-4 border-0 shadow-sm <?= !$isSelectedActivo ? 'bg-light' : '' ?>">
        <div class="row align-items-center">

            <div class="col-12 col-md-auto text-center mb-3 mb-md-0 me-md-2">
                <?php if (!empty($selected['foto'])): ?>
                    <img src="<?= BASE_URL ?>/personal/downloadFile?foto_id=<?= $selected['id'] ?>"
                         alt="Foto de perfil"
                         class="rounded-circle border shadow-sm profile-view-avatar-img btn-zoom-imagen"
                         width="110" height="110"
                         data-zoom-src="<?= BASE_URL ?>/personal/downloadFile?foto_id=<?= $selected['id'] ?>&format=original"
                         data-zoom-title="Foto de Perfil - <?= htmlspecialchars(($selected['nombres'] ?? '') . ' ' . ($selected['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                         title="Haga clic para ampliar foto en formato original"
                         style="cursor: pointer;">
                <?php else: ?>
                    <div class="rounded-circle bg-light text-primary border d-inline-flex align-items-center justify-content-center shadow-sm profile-view-avatar-placeholder">
                        <i class="bi bi-person"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 col-md flex-grow-1 text-center text-md-start overflow-hidden">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start flex-wrap gap-2 mb-1">
                    <h3 class="fw-bold mb-0 text-dark text-break"><?= Security::sanitize(($selected['nombres'] ?? '') . ' ' . ($selected['apellidos'] ?? '')) ?></h3>
                    <?php if (!$isSelectedActivo): ?>
                        <span class="badge bg-secondary text-light py-1 px-2 fw-semibold">
                            <i class="bi bi-person-slash me-1"></i>Inactivo
                        </span>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-center justify-content-md-start flex-wrap gap-3 mb-2 text-secondary">
                    <span><i class="bi bi-person-vcard text-muted me-1"></i>Cédula: <strong><?= Security::sanitize(($selected['nacionalidad'] ?? 'V') . '-' . ($selected['cedula'] ?? '')) ?></strong></span>
                    <span><i class="bi bi-briefcase text-muted me-1"></i>Cargo: <strong><?= Security::sanitize($selected['cargo'] ?? 'No especificado') ?></strong></span>
                </div>

                <div class="row g-2 small text-secondary text-start">
                    <div class="col-12 col-sm-6">
                        <div><i class="bi bi-gender-ambiguous me-1"></i>Sexo: <strong><?= Security::sanitize($selected['sexo'] ?? 'N/A') ?></strong></div>
                        <div><i class="bi bi-heart me-1"></i>Estado Civil: <strong><?= Security::sanitize($selected['estado_civil'] ?? 'N/A') ?></strong></div>
                        <div><i class="bi bi-calendar-event me-1"></i>Fecha Nac.: <strong><?= Security::sanitize($selected['fecha_nacimiento'] ?? 'N/A') ?></strong></div>
                        <div class="text-break"><i class="bi bi-geo-alt me-1"></i>Dirección: <strong><?= Security::sanitize($selected['direccion'] ?? 'N/A') ?></strong></div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="text-break"><i class="bi bi-telephone me-1"></i>Teléfono: <strong><?= Security::sanitize($selected['telefono'] ?: 'N/A') ?></strong></div>
                        <div class="text-break"><i class="bi bi-envelope me-1"></i>Correo: <strong><?= Security::sanitize($selected['correo'] ?: 'N/A') ?></strong></div>
                        <div>
                            <i class="bi bi-activity me-1"></i>Condición médica:
                            <?php if (($selected['padece_enfermedad'] ?? 'No') === 'Si'): ?>
                                <span class="text-danger fw-bold">Sí</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1 align-baseline btn-popover-condicion"
                                        data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top"
                                        data-bs-content="<?= Security::sanitize($selected['detalle_enfermedad'] ?: 'Sin detalle especificado') ?>"
                                        title="Condición Médica"
                                        aria-label="Ver detalle de condición médica">
                                    <i class="bi bi-eye-fill fs-6"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-success fw-bold">No</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 text-md-end text-center mt-3 mt-md-0 border-start-md d-flex flex-column justify-content-center">
                <div class="d-grid gap-2 w-100 ps-md-3">
                    <?php if ($isSelectedActivo): ?>
                        <button type="button" class="btn btn-outline-primary py-2 fw-semibold btn-editar-personal shadow-sm"
                                data-id="<?= $selected['id'] ?>"
                                data-nombres="<?= htmlspecialchars($selected['nombres'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-apellidos="<?= htmlspecialchars($selected['apellidos'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-nacionalidad="<?= $selected['nacionalidad'] ?? 'V' ?>"
                                data-cedula="<?= htmlspecialchars((string)$selected['cedula'], ENT_QUOTES, 'UTF-8') ?>"
                                data-fecha-nac="<?= $selected['fecha_nacimiento'] ?? '' ?>"
                                data-cargo="<?= htmlspecialchars($selected['cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-telefono="<?= htmlspecialchars($selected['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-correo="<?= htmlspecialchars($selected['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-sexo="<?= $selected['sexo'] ?? 'F' ?>"
                                data-estado-civil="<?= htmlspecialchars($selected['estado_civil'] ?? 'Soltero/a', ENT_QUOTES, 'UTF-8') ?>"
                                data-direccion="<?= htmlspecialchars($selected['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-enfermedad="<?= $selected['padece_enfermedad'] ?? 'No' ?>"
                                data-detalle-enfermedad="<?= htmlspecialchars($selected['detalle_enfermedad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-foto="<?= !empty($selected['foto']) ? BASE_URL . '/personal/downloadFile?foto_id=' . $selected['id'] : '' ?>"
                                data-origin="perfil"
                                title="Editar datos de este integrante">
                            <i class="bi bi-pencil-square me-1"></i> Editar Información
                        </button>
                        <button type="button" class="btn btn-outline-danger py-2 fw-semibold btn-deshabilitar-personal shadow-sm"
                                data-id="<?= $selected['id'] ?>"
                                data-nombre="<?= htmlspecialchars(($selected['nombres'] ?? '') . ' ' . ($selected['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-cedula="<?= htmlspecialchars(($selected['nacionalidad'] ?? 'V') . '-' . ($selected['cedula'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                title="Deshabilitar este integrante">
                            <i class="bi bi-person-slash me-1"></i> Deshabilitar Personal
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-success py-2 fw-semibold btn-habilitar-personal shadow-sm"
                                data-id="<?= $selected['id'] ?>"
                                data-nombre="<?= htmlspecialchars(($selected['nombres'] ?? '') . ' ' . ($selected['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-cedula="<?= htmlspecialchars(($selected['nacionalidad'] ?? 'V') . '-' . ($selected['cedula'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-redirect="perfil"
                                title="Habilitar a este integrante">
                            <i class="bi bi-person-check-fill me-1"></i> Habilitar Personal
                        </button>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <div class="card card-custom p-3 p-md-4 border-0 shadow-sm mb-5">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-folder2-open text-warning me-2"></i>Registros del Expediente</h4>
                <p class="text-muted small mb-0">Documentación, actas y justificativos asociados a este integrante.</p>
            </div>
            <?php if ($isSelectedActivo): ?>
                <button type="button" class="btn btn-warning btn-sm fw-bold text-dark shadow-sm px-3 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalAgregarExpediente">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Registro
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-sm shadow-sm px-3 flex-shrink-0" disabled title="Debe habilitar al personal para agregar nuevos registros">
                    <i class="bi bi-lock-fill me-1"></i> Registro Bloqueado (Personal Inactivo)
                </button>
            <?php endif; ?>
        </div>

        <?php if (empty($expedientes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-medical display-4 text-secondary d-block mb-3"></i>
                <h5>No hay registros en el expediente de este personal.</h5>
                <p class="small">Pulse el botón "Nuevo Registro" para anexar actas, justificativos o reposos médicos.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($expedientes as $exp): ?>
                    <?php
                    $isExpired = false;
                    $hasExpiration = !empty($exp['fecha_vencimiento']);
                    if ($hasExpiration && $exp['fecha_vencimiento'] <= $today) {
                        $isExpired = true;
                    }
                    $isPdf = (strpos(($exp['mime_type'] ?? ''), 'pdf') !== false);
                    $fileUrl = BASE_URL . "/personal/downloadFile?exp_id=" . $exp['id'];
                    ?>
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                        <div class="card card-custom h-100 border shadow-sm expediente-card-item <?= $isExpired ? 'border-danger' : '' ?>">

                            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <h6 class="fw-bold mb-0 text-dark text-truncate me-2" title="<?= Security::sanitize($exp['titulo'] ?? '') ?>">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i><?= Security::sanitize($exp['titulo'] ?? '') ?>
                                </h6>
                                <?php if ($isSelectedActivo): ?>
                                <div class="btn-group btn-group-sm flex-shrink-0">
                                    <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-editar-expediente"
                                            data-id="<?= $exp['id'] ?>"
                                            data-titulo="<?= htmlspecialchars($exp['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-descripcion="<?= htmlspecialchars($exp['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-fecha-vencimiento="<?= $exp['fecha_vencimiento'] ?? '' ?>"
                                            title="Editar este registro"
                                            aria-label="Editar registro">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger py-0 px-2 btn-eliminar-expediente"
                                            data-id="<?= $exp['id'] ?>"
                                            data-titulo="<?= htmlspecialchars($exp['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            title="Eliminar este registro"
                                            aria-label="Eliminar registro">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body p-3 d-flex flex-column">

                                <div class="mb-2 text-center bg-light rounded p-1 border position-relative exp-preview-container">
                                    <?php if (!empty($exp['archivo_adjunto'])): ?>
                                        <?php if ($isPdf): ?>
                                            <div class="w-100 text-center py-2">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-2 d-block mb-1"></i>
                                                <span class="d-block small fw-semibold mb-1 text-dark exp-pdf-label">Documento PDF</span>
                                                <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-xs btn-sm btn-outline-danger py-0 px-2 btn-xs-pdf">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir PDF
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column align-items-center justify-content-between h-100 w-100 py-1">
                                                <img src="<?= $fileUrl ?>" alt="<?= Security::sanitize($exp['titulo'] ?? '') ?>"
                                                     class="img-fluid rounded doc-preview-img exp-img-thumbnail btn-zoom-imagen"
                                                     data-zoom-src="<?= htmlspecialchars($fileUrl . '&format=original', ENT_QUOTES, 'UTF-8') ?>"
                                                     data-zoom-title="<?= htmlspecialchars($exp['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                     title="Haga clic para ampliar imagen original">
                                                <button type="button" class="btn btn-xs btn-sm btn-outline-primary py-0 px-2 btn-opciones-descarga mt-1 btn-xs-descarga"
                                                        data-id="<?= $exp['id'] ?>"
                                                        data-titulo="<?= htmlspecialchars($exp['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-file-url="<?= htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-descripcion="<?= htmlspecialchars($exp['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-fecha-registro="<?= date('d/m/Y', strtotime($exp['creado_en'] ?? 'now')) ?>"
                                                        data-fecha-vencimiento="<?= !empty($exp['fecha_vencimiento']) ? date('d/m/Y', strtotime($exp['fecha_vencimiento'])) : '' ?>">
                                                    <i class="bi bi-download me-1"></i> Descargar
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin archivo visual</span>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-2 flex-grow-1 exp-desc-container">
                                    <label class="small text-muted fw-semibold d-block mb-1 exp-desc-label">Descripción:</label>
                                    <p class="text-secondary small mb-0 lh-sm text-break exp-desc-text"><?= Security::sanitize($exp['descripcion'] ?? '') ?></p>
                                </div>

                                <div class="pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-1">
                                    <div class="text-muted exp-date-meta">
                                        <i class="bi bi-clock me-1"></i><?= date('d/m/Y', strtotime($exp['creado_en'] ?? 'now')) ?>
                                    </div>

                                    <div>
                                        <?php if ($hasExpiration): ?>
                                            <?php if ($isExpired): ?>
                                                <span class="badge bg-danger py-1 px-2 shadow-sm badge-exp-status" title="Este documento se encuentra fuera de vigencia">
                                                    <i class="bi bi-exclamation-octagon-fill me-1"></i> VENCIDO
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success py-1 px-2 shadow-sm badge-exp-status" title="Documento dentro de fecha límite de vigencia">
                                                    <i class="bi bi-calendar-check me-1"></i> Hasta <?= date('d/m/Y', strtotime($exp['fecha_vencimiento'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary py-1 px-2 badge-exp-status">Sin vencimiento</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . "/modals/modals_personal.php"; ?>
