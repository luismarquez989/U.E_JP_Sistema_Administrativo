<?php
// Vista principal de actas de graduacion
$actaDetalle = $actaDetalle ?? null;
$aniosEscolares = $aniosEscolares ?? [];
$csrfToken = Security::generateCSRFToken();
?>

<div class="container-fluid px-0">

    <?php include __DIR__ . "/partials/alerts.php"; ?>

    <?php if ($actaDetalle !== null): ?>
        <?php
        $actaId = intval($actaDetalle['id']);
        $numeroActa = intval($actaDetalle['numero_acta'] ?? 1);
        $anioEscolar = (string)($actaDetalle['anio_escolar'] ?? '');
        $isActiva = intval($actaDetalle['activo'] ?? 1) === 1;
        $estudiantes = is_array($actaDetalle['estudiantes'] ?? null) ? $actaDetalle['estudiantes'] : [];
        $imagenes = is_array($actaDetalle['imagenes'] ?? null) ? $actaDetalle['imagenes'] : [];
        ?>

        <!-- Vista de detalle del acta seleccionada -->
        <div class="mb-3 no-print">
            <a href="<?= BASE_URL ?>/actas" class="btn btn-light border shadow-sm fw-semibold text-dark d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i>Volver a la lista de actas
            </a>
        </div>

        <?php if (!$isActiva): ?>
            <div class="alert alert-secondary border-secondary d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4 shadow-sm no-print">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-slash-circle-fill text-secondary me-2"></i>Acta Desactivada / Inactiva
                    </h6>
                    <p class="small mb-0 text-muted">
                        Esta acta se encuentra deshabilitada: no aparece en las búsquedas predeterminadas y no puede modificarse. Puede consultar su información, descargarla o volver a habilitarla.
                    </p>
                </div>
                <button type="button" class="btn btn-success btn-sm fw-bold px-3 flex-shrink-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHabilitarActa">
                    <i class="bi bi-check-circle-fill me-1"></i> Habilitar Acta
                </button>
            </div>
        <?php endif; ?>

        <div class="card card-custom p-3 p-md-4 mb-4 border-0 shadow-sm no-print <?= !$isActiva ? 'personal-card-inactivo' : '' ?>">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-auto text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary border d-inline-flex align-items-center justify-content-center shadow-sm profile-view-avatar-placeholder">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                </div>

                <div class="col-12 col-md flex-grow-1 text-center text-md-start">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start flex-wrap gap-2 mb-1">
                        <h3 class="fw-bold mb-0 text-dark">Año Escolar <?= Security::sanitize($anioEscolar) ?> — Acta #<?= $numeroActa ?></h3>
                        <?php if (!$isActiva): ?>
                            <span class="badge bg-secondary text-light py-1 px-2 fw-semibold">
                                <i class="bi bi-slash-circle me-1"></i>Inactiva
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-center justify-content-md-start flex-wrap gap-3 mt-2 text-secondary small">
                        <span><i class="bi bi-mortarboard text-primary me-1"></i>Graduados registrados: <strong><?= count($estudiantes) ?></strong></span>
                        <?php if (!empty($actaDetalle['creado_en'])): ?>
                            <span><i class="bi bi-calendar-check text-muted me-1"></i>Registrada: <strong><?= date('d/m/Y', strtotime((string)$actaDetalle['creado_en'])) ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-3 text-md-end text-center mt-3 mt-md-0 border-start-md d-flex flex-column justify-content-center">
                    <div class="d-grid gap-2 w-100 ps-md-3">
                        <button type="button" class="btn btn-primary py-2 fw-semibold shadow-sm btn-abrir-descarga-acta"
                                data-tipo="acta"
                                data-acta-id="<?= $actaId ?>"
                                data-titulo="Acta de Graduación - Año Escolar <?= htmlspecialchars($anioEscolar, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-download me-1"></i> Descargar / Imprimir Acta
                        </button>

                        <?php if ($isActiva): ?>
                            <button type="button" class="btn btn-outline-primary py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditarActa">
                                <i class="bi bi-pencil-square me-1"></i> Editar Acta
                            </button>
                            <button type="button" class="btn btn-outline-danger py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDesactivarActa">
                                <i class="bi bi-slash-circle me-1"></i> Desactivar Acta
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-success py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHabilitarActa">
                                <i class="bi bi-check-circle-fill me-1"></i> Habilitar Acta
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom p-3 p-md-4 mb-4 border-0 shadow-sm no-print">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-people-fill text-primary me-2"></i>Nómina de Graduados — Año Escolar <?= Security::sanitize($anioEscolar) ?>
                    </h4>
                    <p class="text-muted small mb-0">Listado de estudiantes asentados en esta acta de graduación.</p>
                </div>
                <span class="badge bg-secondary rounded-pill"><?= count($estudiantes) ?> estudiante(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle mb-0 acta-nomina-table">
                    <thead class="table-dark text-center">
                        <tr>
                            <th rowspan="2" class="align-middle acta-col-num">Nº</th>
                            <th rowspan="2" class="align-middle text-start acta-col-nombre">Apellidos y Nombres</th>
                            <th rowspan="2" class="align-middle acta-col-ci">C. Identidad</th>
                            <th colspan="3" class="align-middle acta-col-fn-group">F. Nacimiento</th>
                            <th rowspan="2" class="align-middle acta-col-literal">Literal</th>
                            <th rowspan="2" class="align-middle acta-col-obs">Observación</th>
                        </tr>
                        <tr>
                            <th class="acta-col-fn-part acta-col-fn-d">D</th>
                            <th class="acta-col-fn-part acta-col-fn-m">M</th>
                            <th class="acta-col-fn-part acta-col-fn-a">A</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $idx => $est): ?>
                            <?php
                            $num = sprintf('%02d', intval($est['orden'] ?? ($idx + 1)));
                            $fnRaw = (string)($est['fecha_nacimiento'] ?? '');
                            $ts = $fnRaw !== '' ? strtotime($fnRaw) : false;
                            $dia = $ts ? date('d', $ts) : '';
                            $mes = $ts ? date('m', $ts) : '';
                            $anioFormato = intval($est['anio_formato'] ?? 2);
                            $anioFmt = '';
                            if ($ts) {
                                $anioFmt = ($anioFormato === 4) ? date('Y', $ts) : date('y', $ts);
                            }
                            $cedVal = trim((string)($est['cedula'] ?? ''));
                            $obsVal = trim((string)($est['observacion'] ?? ''));
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $num ?></td>
                                <td class="fw-semibold text-dark text-break"><?= Security::sanitize($est['apellidos_nombres'] ?? '') ?></td>
                                <td class="text-center text-nowrap">
                                    <?php if ($cedVal !== ''): ?>
                                        <strong><?= Security::sanitize($cedVal) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center acta-col-fn-d"><?= $dia ?></td>
                                <td class="text-center acta-col-fn-m"><?= $mes ?></td>
                                <td class="text-center acta-col-fn-a text-nowrap"><?= $anioFmt ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-bold fs-6">
                                        <?= Security::sanitize($est['literal_nota'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($obsVal !== ''): ?>
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1 text-break">
                                            <?= Security::sanitize($obsVal) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-custom p-3 p-md-4 border-0 shadow-sm mb-5 no-print">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 border-bottom pb-3">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-images text-warning me-2"></i>Imágenes Adjuntas del Libro Físico
                        <span class="badge bg-secondary rounded-pill ms-2 fs-6"><?= count($imagenes) ?>/4</span>
                    </h4>
                    <p class="text-muted small mb-0">Fotografías de las páginas del libro donde reposa esta acta en físico para verificación directa.</p>
                </div>
            </div>

            <?php if (empty($imagenes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-camera display-4 text-secondary d-block mb-3"></i>
                    <h5>No se adjuntaron fotografías del libro físico para esta acta.</h5>
                    <?php if ($isActiva): ?>
                        <p class="small mb-0">Puede añadir hasta 4 imágenes desde el botón <strong>"Editar Acta"</strong>.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($imagenes as $idxImg => $img): ?>
                        <?php
                        $imgId = intval($img['id']);
                        $imgOrden = intval($img['orden'] ?? ($idxImg + 1));
                        $imgUrlInline = BASE_URL . "/actas/downloadImagen?img_id=" . $imgId;
                        $imgUrlDownload = BASE_URL . "/actas/downloadImagen?img_id=" . $imgId . "&download=1";
                        $imgUrlPrintPdf = BASE_URL . "/actas/printActa?img_id=" . $imgId;
                        $imgTitle = "Acta #{$numeroActa} ({$anioEscolar}) - Foto del Libro #{$imgOrden}";
                        ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-12">
                            <div class="card card-custom h-100 border shadow-sm expediente-card-item">
                                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                    <h6 class="fw-bold mb-0 text-dark text-truncate" title="<?= Security::sanitize($img['nombre_original'] ?? $imgTitle) ?>">
                                        <i class="bi bi-image text-primary me-1"></i>Foto del Libro #<?= $imgOrden ?>
                                    </h6>
                                    <span class="badge bg-light text-secondary border small">#<?= $imgOrden ?></span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column">
                                    <div class="text-center bg-light rounded p-2 border position-relative exp-preview-container flex-grow-1">
                                        <div class="d-flex flex-column align-items-center justify-content-between h-100 w-100 py-1">
                                            <img src="<?= $imgUrlInline ?>"
                                                 alt="<?= Security::sanitize($imgTitle) ?>"
                                                 class="img-fluid rounded doc-preview-img exp-img-thumbnail btn-zoom-imagen-acta"
                                                 data-zoom-src="<?= htmlspecialchars($imgUrlInline . '&format=original', ENT_QUOTES, 'UTF-8') ?>"
                                                 data-zoom-title="<?= htmlspecialchars($imgTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                 title="Haga clic para ampliar y verificar datos">
                                            <button type="button"
                                                    class="btn btn-xs btn-sm btn-outline-primary py-0 px-2 mt-2 btn-xs-descarga btn-abrir-descarga-acta"
                                                    data-tipo="imagen"
                                                    data-img-id="<?= $imgId ?>"
                                                    data-titulo="<?= htmlspecialchars($imgTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-url-inline="<?= htmlspecialchars($imgUrlInline, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-url-download="<?= htmlspecialchars($imgUrlDownload, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-url-pdf="<?= htmlspecialchars($imgUrlPrintPdf, ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-download me-1"></i> Descargar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2 small text-muted text-truncate text-center" title="<?= Security::sanitize($img['nombre_original'] ?? '') ?>">
                                        <?= Security::sanitize($img['nombre_original'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Area de captura fuera de pantalla para exportar acta digital como imagen -->
        <div class="acta-offscreen-capture-wrapper no-print" aria-hidden="true">
            <div class="acta-sheet-view" id="actaDigitalCaptureArea">
                <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                    <span class="acta-live-datetime"><?= date('d/m/Y, h:i a') ?></span>
                    <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                </div>
                <div class="acta-digital-header text-center">
                    <h4 class="acta-digital-title">Acta de Graduación #<?= $numeroActa ?></h4>
                    <h5 class="acta-digital-subtitle">Año Escolar <?= Security::sanitize($anioEscolar) ?></h5>
                </div>
                <div class="acta-digital-table-wrap">
                    <table class="acta-digital-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="acta-col-num">Nº</th>
                                <th rowspan="2" class="acta-col-nombre">Apellidos y Nombres</th>
                                <th rowspan="2" class="acta-col-ci">C. Identidad</th>
                                <th colspan="3" class="acta-col-fn-group">F. Nacimiento</th>
                                <th rowspan="2" class="acta-col-literal">Literal</th>
                                <th rowspan="2" class="acta-col-obs">Observación</th>
                            </tr>
                            <tr>
                                <th class="acta-col-fn-part acta-col-fn-d">D</th>
                                <th class="acta-col-fn-part acta-col-fn-m">M</th>
                                <th class="acta-col-fn-part acta-col-fn-a">A</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $idx => $est): ?>
                                <?php
                                $num = sprintf('%02d', intval($est['orden'] ?? ($idx + 1)));
                                $fnRaw = (string)($est['fecha_nacimiento'] ?? '');
                                $ts = $fnRaw !== '' ? strtotime($fnRaw) : false;
                                $dia = $ts ? date('d', $ts) : '';
                                $mes = $ts ? date('m', $ts) : '';
                                $anioFormato = intval($est['anio_formato'] ?? 2);
                                $anioFmt = '';
                                if ($ts) {
                                    $anioFmt = ($anioFormato === 4) ? date('Y', $ts) : date('y', $ts);
                                }
                                ?>
                                <tr>
                                    <td class="text-center"><?= $num ?></td>
                                    <td class="acta-cell-nombre"><?= Security::sanitize($est['apellidos_nombres'] ?? '') ?></td>
                                    <td class="text-center"><?= Security::sanitize($est['cedula'] ?? '') ?></td>
                                    <td class="text-center acta-col-fn-d"><?= $dia ?></td>
                                    <td class="text-center acta-col-fn-m"><?= $mes ?></td>
                                    <td class="text-center acta-col-fn-a text-nowrap"><?= $anioFmt ?></td>
                                    <td class="text-center fw-bold"><?= Security::sanitize($est['literal_nota'] ?? '') ?></td>
                                    <td class="text-center"><?= Security::sanitize($est['observacion'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>

        <!-- Vista principal de busqueda y lista de actas -->
        <form id="formBuscarActas" class="search-filter-container mb-3 shadow-sm" autocomplete="off">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted ps-3 pe-2">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="inputBusquedaActa" class="form-control border-start-0 border-end-0 py-2 ps-1"
                       maxlength="100" placeholder="Buscar graduado por nombre (en cualquier orden) o cédula..." autocomplete="off">
                <button type="button" class="btn btn-white border-start-0 border-end-0 text-muted clear-search-btn px-2" id="btnLimpiarTextoActa" title="Limpiar texto" aria-label="Limpiar texto">
                    <i class="bi bi-x-circle"></i>
                </button>
                <button type="button" class="btn btn-filter-pill px-2 px-sm-3 d-inline-flex align-items-center text-nowrap fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#modalFiltrosActas" title="Filtrar por Año Escolar, Fecha de Nacimiento o Estado">
                    <i class="bi bi-funnel-fill text-primary me-sm-1"></i>
                    <span class="d-none d-sm-inline">Filtrar</span>
                    <span id="badgeFiltrosActasCount" class="badge bg-primary ms-1 d-none">0</span>
                </button>
                <button type="submit" id="btnEjecutarBusquedaActa" class="btn btn-primary px-3 px-sm-4 fw-bold d-inline-flex align-items-center text-nowrap">
                    <i class="bi bi-search me-sm-1"></i>
                    <span class="d-none d-sm-inline">Buscar</span>
                </button>
            </div>
        </form>

        <div id="activeActaFiltersBar" class="alert alert-info py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 shadow-sm d-none">
            <div class="d-flex flex-wrap align-items-center gap-1">
                <span class="small fw-semibold me-1"><i class="bi bi-info-circle-fill me-1"></i>Filtros aplicados:</span>
                <div id="activeActaFilterChips" class="d-flex flex-wrap gap-1"></div>
            </div>
            <button type="button" id="btnResetAllActaFilters" class="btn btn-sm btn-outline-info text-dark py-0">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Restablecer
            </button>
        </div>

        <div class="card card-custom shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 px-3 px-md-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center me-2 overflow-hidden">
                    <h5 class="fw-bold mb-0 text-dark text-nowrap personal-list-title">Lista de actas</h5>
                    <span class="badge bg-secondary rounded-pill ms-2 personal-list-badge" id="contadorResultadosActas">0</span>
                </div>
                <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 rounded-pill shadow-sm fw-semibold text-nowrap flex-shrink-0 d-inline-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#modalCrearActa">
                    <i class="bi bi-journal-plus me-1"></i><span>Nueva Acta</span>
                </button>
            </div>

            <div class="card-body p-3">
                <div id="estadoInicialActas" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-bookmark display-4 d-block mb-3 text-primary opacity-75"></i>
                    <h5 class="fw-bold text-dark">Búsqueda de Actas de Graduación</h5>
                    <p class="small mb-0 mx-auto acta-empty-hint">
                        Ingrese el nombre (sin importar el orden ni comas) o la cédula de un graduado, o utilice <strong>Filtrar</strong> para elegir un <strong>Año Escolar</strong> o <strong>Fecha de Nacimiento</strong> y pulse <strong>Buscar</strong>.
                    </p>
                </div>

                <div id="estadoCargandoActas" class="d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Espere un momento, por favor...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Espere un momento, por favor...</h5>
                    <p class="text-muted small mb-3">Consultando las actas de graduación en el sistema...</p>
                    <div class="acta-loading-skeleton-box mx-auto">
                        <div class="acta-skeleton-line rounded mb-2"></div>
                        <div class="acta-skeleton-line rounded mb-2"></div>
                        <div class="acta-skeleton-line rounded"></div>
                    </div>
                </div>

                <div id="estadoSinResultadosActas" class="d-none text-center py-5 text-muted">
                    <i class="bi bi-search display-4 text-secondary d-block mb-3"></i>
                    <h5 class="fw-bold text-dark" id="tituloSinResultadosActas">No se encontraron coincidencias</h5>
                    <p class="small mb-0" id="mensajeSinResultadosActas">
                        No existen actas registradas con un estudiante que coincida con los datos ingresados.
                    </p>
                </div>

                <div id="contenedorTablaResultadosActas" class="d-none">
                    <div id="listaTarjetasActas" class="row g-3"></div>

                    <div id="spinnerScrollActas" class="d-none text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . "/modals/modals_actas.php"; ?>
