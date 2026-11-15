<?php
// Modales del modulo de actas de graduacion
$actaDetalle = $actaDetalle ?? null;
$aniosEscolares = $aniosEscolares ?? [];
$csrfToken = Security::generateCSRFToken();
?>

<!-- Modal de filtros de busqueda de actas -->
<div class="modal fade" id="modalFiltrosActas" tabindex="-1" aria-labelledby="modalFiltrosActasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalFiltrosActasLabel">
                    <i class="bi bi-funnel-fill me-2"></i>Filtrar Actas de Graduación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="filtroActaEstado" class="form-label fw-semibold text-dark small">Estado del Acta</label>
                    <select id="filtroActaEstado" class="form-select">
                        <option value="activo" selected>Actas Activas</option>
                        <option value="inactivo">Actas Desactivadas / Inactivas</option>
                        <option value="todos">Todas (Activas y desactivadas)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="filtroActaAnio" class="form-label fw-semibold text-dark small">Año Escolar</label>
                    <input type="text" id="filtroActaAnio" list="listaAniosEscolares" class="form-control" maxlength="9"
                           placeholder="Ej. 2008-2009, 2000-2001, 1996-1997..." autocomplete="off">
                    <datalist id="listaAniosEscolares">
                        <?php foreach ($aniosEscolares as $anioItem): ?>
                            <option value="<?= Security::sanitize((string)$anioItem) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-text small">Seleccione un año escolar de la lista o escriba el período deseado.</div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold text-dark small">Fecha de Nacimiento del Estudiante (Día - Mes - Año)</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="number" id="filtroFnDia" class="form-control text-center" placeholder="Día (DD)" min="1" max="31" autocomplete="off">
                        </div>
                        <div class="col-4">
                            <input type="number" id="filtroFnMes" class="form-control text-center" placeholder="Mes (MM)" min="1" max="12" autocomplete="off">
                        </div>
                        <div class="col-4">
                            <input type="number" id="filtroFnAnio" class="form-control text-center" placeholder="Año (AAAA)" min="1900" max="2100" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-text small">Busca actas que incluyan algún graduado con esa fecha de nacimiento.</div>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <button type="button" id="btnLimpiarModalFiltrosActas" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-eraser me-1"></i> Limpiar opciones
                </button>
                <button type="button" id="btnAplicarFiltrosYBuscar" class="btn btn-primary btn-sm px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle me-1"></i> Aplicar y Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal crear nueva acta de graduacion -->
<div class="modal fade" id="modalCrearActa" tabindex="-1" aria-labelledby="modalCrearActaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/actas/store" autocomplete="off" method="POST" enctype="multipart/form-data" id="formCrearActa" class="modal-content shadow border-0">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalCrearActaLabel">
                    <i class="bi bi-journal-plus me-2"></i>Registrar Nueva Acta de Graduación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-3 p-md-4 modal-body-scrollable">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3 pb-3 border-bottom">
                    <div class="acta-anio-input-box">
                        <label for="crearAnioEscolar" class="form-label fw-semibold text-dark mb-1">Año Escolar (*)</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light fw-semibold"><i class="bi bi-calendar3 text-primary me-1"></i>Año Escolar</span>
                            <input type="text" name="anio_escolar" id="crearAnioEscolar" class="form-control fw-bold" maxlength="9"
                                   placeholder="Ej. 2008-2009" required pattern="\d{4}(-\d{4})?" title="Formato requerido: AAAA-AAAA (ej. 2008-2009)" autocomplete="off">
                            <div class="invalid-feedback fw-semibold" id="feedbackCrearAnioEscolar"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnTogglePreviewCrear" class="btn btn-outline-primary fw-semibold shadow-sm">
                            <i class="bi bi-eye me-1"></i> Vista Previa del Acta
                        </button>
                    </div>
                </div>

                <div id="seccionFormularioCrearActa">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <span class="fw-semibold text-secondary small">
                            <i class="bi bi-people me-1 text-primary"></i>Graduados del Acta (La Cédula y la Observación son opcionales)
                        </span>
                        <button type="button" id="btnAgregarFilaCrear" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Estudiante
                        </button>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm align-middle mb-0 acta-form-table" id="tablaEstudiantesCrear">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle acta-col-num">Nº</th>
                                    <th rowspan="2" class="align-middle text-start acta-col-nombre">Apellidos y Nombres (*)</th>
                                    <th rowspan="2" class="align-middle acta-col-ci">C. Identidad</th>
                                    <th colspan="3" class="align-middle acta-col-fn-group">F. Nacimiento<br><span class="acta-th-sub">(D - M - A) (*)</span></th>
                                    <th rowspan="2" class="align-middle acta-col-literal">Literal (*)</th>
                                    <th rowspan="2" class="align-middle acta-col-obs">Observación</th>
                                    <th rowspan="2" class="align-middle acta-col-del"></th>
                                </tr>
                                <tr>
                                    <th class="acta-col-fn-part">Día</th>
                                    <th class="acta-col-fn-part">Mes</th>
                                    <th class="acta-col-fn-part">Año</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyEstudiantesCrear"></tbody>
                        </table>
                    </div>

                    <div class="mb-4">
                        <button type="button" id="btnAgregarFilaCrearBottom" class="btn btn-outline-primary btn-sm fw-semibold w-100 py-2">
                            <i class="bi bi-plus-circle me-1"></i> Agregar otro estudiante a la lista
                        </button>
                    </div>

                    <div class="p-3 rounded-3 border bg-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="bi bi-camera-fill text-primary me-1"></i>Imágenes del Libro Físico
                                <span class="badge bg-info-subtle text-primary border border-primary-subtle ms-1">(Recomendado)</span>
                            </label>
                            <span class="badge bg-primary rounded-pill px-2.5 py-1" id="badgeContadorImagenesCrear">0 de 4</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <label for="inputImagenesCrear" class="btn btn-outline-primary btn-sm fw-semibold mb-0 shadow-sm">
                                <i class="bi bi-plus-circle me-1"></i> Añadir fotografía(s) del libro
                            </label>
                            <span class="small text-muted" id="textoGuiaImagenesCrear">Puede seleccionar una por una o varias a la vez (máx. 4)</span>
                            <input type="file" name="imagenes[]" id="inputImagenesCrear" class="d-none"
                                   accept="image/jpeg,image/png,image/webp,image/gif" multiple autocomplete="off">
                        </div>
                        <div id="previewImagenesCrearBox" class="row g-2 mt-2"></div>
                    </div>
                </div>

                <div id="seccionVistaPreviaCrearActa" class="d-none">
                    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center justify-content-between py-2 px-3 mb-3 shadow-sm" role="alert">
                        <span class="small fw-semibold">
                            <i class="bi bi-eye-fill me-1"></i>Vista Previa del Documento Digital (Diseño de exportación e impresión)
                        </span>
                        <button type="button" class="btn p-0 ms-2 text-secondary acta-alert-close-btn" data-bs-dismiss="alert" aria-label="Cerrar">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="acta-preview-frame p-3 bg-secondary bg-opacity-10 rounded">
                        <div class="acta-sheet-view mx-auto shadow-sm">
                            <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                                <span class="acta-live-datetime"><?= date('d/m/Y, h:i a') ?></span>
                                <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                            </div>
                            <div class="acta-digital-header text-center">
                                <h4 class="acta-digital-title">Acta de Graduación</h4>
                                <h5 class="acta-digital-subtitle" id="previewCrearAnioTexto">Año Escolar ----</h5>
                            </div>
                            <div class="table-responsive acta-digital-table-wrap">
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
                                    <tbody id="tbodyPreviewCrear"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light justify-content-between">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">
                    <i class="bi bi-save me-1"></i> Guardar Acta
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($actaDetalle !== null): ?>
    <?php
    $actaId = intval($actaDetalle['id']);
    $numeroActa = intval($actaDetalle['numero_acta'] ?? 1);
    $anioEscolar = (string)($actaDetalle['anio_escolar'] ?? '');
    $estudiantes = is_array($actaDetalle['estudiantes'] ?? null) ? $actaDetalle['estudiantes'] : [];
    $imagenes = is_array($actaDetalle['imagenes'] ?? null) ? $actaDetalle['imagenes'] : [];
    ?>

    <!-- Modal editar acta existente -->
    <div class="modal fade" id="modalEditarActa" tabindex="-1" aria-labelledby="modalEditarActaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <form action="<?= BASE_URL ?>/actas/update" autocomplete="off" method="POST" enctype="multipart/form-data" id="formEditarActa" class="modal-content shadow border-0">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="acta_id" value="<?= $actaId ?>">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalEditarActaLabel">
                        <i class="bi bi-pencil-square me-2"></i>Editar Acta de Graduación #<?= $numeroActa ?> (Año Escolar <?= Security::sanitize($anioEscolar) ?>)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-3 p-md-4 modal-body-scrollable">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3 pb-3 border-bottom">
                        <div class="acta-anio-input-box">
                            <label for="editarAnioEscolar" class="form-label fw-semibold text-dark mb-1">Año Escolar (*)</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light fw-semibold"><i class="bi bi-calendar3 text-primary me-1"></i>Año Escolar</span>
                                <input type="text" name="anio_escolar" id="editarAnioEscolar" class="form-control fw-bold" maxlength="9"
                                       value="<?= Security::sanitize($anioEscolar) ?>" required pattern="\d{4}(-\d{4})?" title="Formato requerido: AAAA-AAAA (ej. 2008-2009)" autocomplete="off">
                                <div class="invalid-feedback fw-semibold" id="feedbackEditarAnioEscolar"></div>
                            </div>
                        </div>
                        <div>
                            <button type="button" id="btnTogglePreviewEditar" class="btn btn-outline-primary fw-semibold shadow-sm">
                                <i class="bi bi-eye me-1"></i> Vista Previa del Acta
                            </button>
                        </div>
                    </div>

                    <div id="seccionFormularioEditarActa">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <span class="fw-semibold text-secondary small">
                                <i class="bi bi-people me-1 text-primary"></i>Graduados Registrados en el Acta
                            </span>
                            <button type="button" id="btnAgregarFilaEditar" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                                <i class="bi bi-plus-lg me-1"></i> Agregar Estudiante
                            </button>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm align-middle mb-0 acta-form-table" id="tablaEstudiantesEditar">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle acta-col-num">Nº</th>
                                        <th rowspan="2" class="align-middle text-start acta-col-nombre">Apellidos y Nombres (*)</th>
                                        <th rowspan="2" class="align-middle acta-col-ci">C. Identidad</th>
                                        <th colspan="3" class="align-middle acta-col-fn-group">F. Nacimiento<br><span class="acta-th-sub">(D - M - A) (*)</span></th>
                                        <th rowspan="2" class="align-middle acta-col-literal">Literal (*)</th>
                                        <th rowspan="2" class="align-middle acta-col-obs">Observación</th>
                                        <th rowspan="2" class="align-middle acta-col-del"></th>
                                    </tr>
                                    <tr>
                                        <th class="acta-col-fn-part">Día</th>
                                        <th class="acta-col-fn-part">Mes</th>
                                        <th class="acta-col-fn-part">Año</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyEstudiantesEditar">
                                    <?php foreach ($estudiantes as $idx => $est): ?>
                                        <?php
                                        $fnRaw = (string)($est['fecha_nacimiento'] ?? '');
                                        $ts = $fnRaw !== '' ? strtotime($fnRaw) : false;
                                        $diaVal = $ts ? date('d', $ts) : '';
                                        $mesVal = $ts ? date('m', $ts) : '';
                                        $anioFull = $ts ? date('Y', $ts) : '';
                                        $anioFormato = intval($est['anio_formato'] ?? 2);
                                        $anioVal = '';
                                        if ($ts) {
                                            $anioVal = ($anioFormato === 4) ? $anioFull : (strlen($anioFull) >= 2 ? substr($anioFull, -2) : $anioFull);
                                        }
                                        ?>
                                        <tr class="fila-estudiante-acta">
                                            <td class="text-center fw-bold text-muted celda-num-orden"><?= sprintf('%02d', $idx + 1) ?></td>
                                            <td>
                                                <input type="text" name="estudiantes[<?= $idx ?>][apellidos_nombres]" class="form-control form-control-sm inp-nombre"
                                                       maxlength="120" value="<?= Security::sanitize($est['apellidos_nombres'] ?? '') ?>" required placeholder="Ej. Araujo R, Orlando J">
                                            </td>
                                            <td>
                                                <input type="text" name="estudiantes[<?= $idx ?>][cedula]" class="form-control form-control-sm text-center inp-cedula"
                                                       maxlength="20" value="<?= Security::sanitize($est['cedula'] ?? '') ?>" placeholder="Opcional">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="numeric" name="estudiantes[<?= $idx ?>][dia]" class="form-control form-control-sm text-center inp-dia acta-no-spin"
                                                       value="<?= $diaVal ?>" maxlength="2" pattern="\d{2}" required placeholder="DD">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="numeric" name="estudiantes[<?= $idx ?>][mes]" class="form-control form-control-sm text-center inp-mes acta-no-spin"
                                                       value="<?= $mesVal ?>" maxlength="2" pattern="\d{2}" required placeholder="MM">
                                            </td>
                                            <td>
                                                <input type="text" inputmode="numeric" name="estudiantes[<?= $idx ?>][anio]" class="form-control form-control-sm text-center inp-anio acta-no-spin"
                                                       value="<?= $anioVal ?>" maxlength="4" pattern="\d{2}|\d{4}" required placeholder="AA">
                                            </td>
                                            <td>
                                                <input type="text" name="estudiantes[<?= $idx ?>][literal_nota]" class="form-control form-control-sm text-center fw-bold inp-literal"
                                                       value="<?= Security::sanitize($est['literal_nota'] ?? '') ?>" maxlength="10" required placeholder="A / 20">
                                            </td>
                                            <td>
                                                <input type="text" name="estudiantes[<?= $idx ?>][observacion]" class="form-control form-control-sm inp-obs"
                                                       value="<?= Security::sanitize($est['observacion'] ?? '') ?>" maxlength="25" placeholder="Opcional">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 btn-quitar-fila-acta" title="Quitar estudiante" aria-label="Quitar estudiante">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <button type="button" id="btnAgregarFilaEditarBottom" class="btn btn-outline-primary btn-sm fw-semibold w-100 py-2">
                                <i class="bi bi-plus-circle me-1"></i> Agregar otro estudiante a la lista
                            </button>
                        </div>

                        <div class="p-3 rounded-3 border bg-light">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <label class="form-label fw-bold text-dark mb-0">
                                    <i class="bi bi-images text-primary me-1"></i>Imágenes del Libro Físico
                                    <span class="badge bg-info-subtle text-primary border border-primary-subtle ms-1">(Recomendado - Máx. 4 en total)</span>
                                </label>
                                <span class="badge bg-primary rounded-pill px-2.5 py-1" id="badgeContadorImagenesEditar">0 de 4</span>
                            </div>

                            <?php if (!empty($imagenes)): ?>
                                <div class="row g-2 mb-3" id="contenedorImagenesExistentesEditar">
                                    <?php foreach ($imagenes as $img): ?>
                                        <?php $imgId = intval($img['id']); ?>
                                        <div class="col-6 col-md-3 item-img-existente" data-img-id="<?= $imgId ?>">
                                            <div class="card p-2 border shadow-sm bg-white text-center">
                                                <input type="hidden" name="imagenes_conservar[]" value="<?= $imgId ?>">
                                                <img src="<?= BASE_URL ?>/actas/downloadImagen?img_id=<?= $imgId ?>" alt="Imagen" class="img-fluid rounded mb-2 acta-edit-thumb">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 py-0 btn-quitar-img-existente">
                                                    <i class="bi bi-trash me-1"></i>Quitar
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <label for="inputImagenesEditar" class="btn btn-outline-primary btn-sm fw-semibold mb-0 shadow-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Añadir fotografía(s) del libro
                                </label>
                                <span class="small text-muted" id="textoGuiaImagenesEditar">Puede seleccionar una por una o varias a la vez</span>
                                <input type="file" name="imagenes[]" id="inputImagenesEditar" class="d-none"
                                       accept="image/jpeg,image/png,image/webp,image/gif" multiple autocomplete="off">
                            </div>
                            <div id="previewImagenesEditarBox" class="row g-2 mt-2"></div>
                        </div>
                    </div>

                    <div id="seccionVistaPreviaEditarActa" class="d-none">
                        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center justify-content-between py-2 px-3 mb-3 shadow-sm" role="alert">
                            <span class="small fw-semibold">
                                <i class="bi bi-eye-fill me-1"></i>Vista Previa del Documento Digital (Diseño de exportación e impresión)
                            </span>
                            <button type="button" class="btn p-0 ms-2 text-secondary acta-alert-close-btn" data-bs-dismiss="alert" aria-label="Cerrar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="acta-preview-frame p-3 bg-secondary bg-opacity-10 rounded">
                            <div class="acta-sheet-view mx-auto shadow-sm">
                                <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                                    <span class="acta-live-datetime"><?= date('d/m/Y, h:i a') ?></span>
                                    <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                                </div>
                                <div class="acta-digital-header text-center">
                                    <h4 class="acta-digital-title">Acta de Graduación</h4>
                                    <h5 class="acta-digital-subtitle" id="previewEditarAnioTexto">Año Escolar <?= Security::sanitize($anioEscolar) ?></h5>
                                </div>
                                <div class="table-responsive acta-digital-table-wrap">
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
                                        <tbody id="tbodyPreviewEditar"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal desactivar acta -->
    <div class="modal fade" id="modalDesactivarActa" tabindex="-1" aria-labelledby="modalDesactivarActaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="modalDesactivarActaLabel">
                        <i class="bi bi-slash-circle-fill me-2"></i>Desactivar Acta de Graduación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="<?= BASE_URL ?>/actas/deshabilitar" autocomplete="off" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="id" value="<?= $actaId ?>">
                    <input type="hidden" name="redirect_to" value="detalle">

                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-exclamation-triangle-fill display-3 text-danger mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-2">¿Desea desactivar el Acta #<?= $numeroActa ?> (Año Escolar <?= Security::sanitize($anioEscolar) ?>)?</h5>
                        <div class="alert alert-secondary text-start small mb-0">
                            <i class="bi bi-info-circle-fill me-1"></i> El acta no se borrará de la base de datos, pero dejará de aparecer en las búsquedas predeterminadas y no podrá modificarse mientras permanezca desactivada.
                        </div>
                    </div>

                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnConfirmDesactivarActa" class="btn btn-danger fw-bold px-4" disabled>
                            <i class="bi bi-hourglass-split me-1"></i> Espere (5s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal habilitar acta -->
    <div class="modal fade" id="modalHabilitarActa" tabindex="-1" aria-labelledby="modalHabilitarActaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="modalHabilitarActaLabel">
                        <i class="bi bi-check-circle-fill me-2"></i>Habilitar Acta de Graduación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="<?= BASE_URL ?>/actas/habilitar" autocomplete="off" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="id" value="<?= $actaId ?>">

                    <div class="modal-body p-4 text-center">
                        <i class="bi bi-check-circle display-3 text-success mb-3 d-block"></i>
                        <h5 class="fw-bold text-dark mb-2">¿Desea habilitar nuevamente el Acta #<?= $numeroActa ?> (Año Escolar <?= Security::sanitize($anioEscolar) ?>)?</h5>
                        <p class="text-muted small mb-0">
                            El acta volverá a mostrarse normalmente en las búsquedas de actas activas y permitirá su edición.
                        </p>
                    </div>

                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="bi bi-check-lg me-1"></i> Sí, Habilitar Acta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal opciones de descarga e impresion -->
    <div class="modal fade" id="modalOpcionesDescargaActa" tabindex="-1" aria-labelledby="modalOpcionesDescargaActaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalOpcionesDescargaActaLabel">
                        <i class="bi bi-download me-2"></i>Opciones de Descarga e Impresión
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Seleccione cómo desea obtener <strong class="text-dark" id="descargaActaTituloLabel"></strong>:</p>

                    <div class="d-grid gap-3">
                        <button type="button" id="btnOpcionDescargarImagenActa" class="btn btn-outline-primary text-start p-3 rounded-3 d-flex align-items-center justify-content-between shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary me-3 d-flex align-items-center justify-content-center modal-download-icon-box">
                                    <i class="bi bi-file-earmark-image fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Descargar como Imagen</div>
                                    <div class="small text-muted">Guarda el documento en formato de imagen directo en su dispositivo</div>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>

                        <a href="#" id="btnOpcionDescargarPdfActa" target="_blank" class="btn btn-outline-danger text-start p-3 rounded-3 d-flex align-items-center justify-content-between shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger me-3 d-flex align-items-center justify-content-center modal-download-icon-box">
                                    <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Descargar como PDF</div>
                                    <div class="small text-muted">Abre otra pestaña con el documento listo para descargar</div>
                                </div>
                            </div>
                            <i class="bi bi-box-arrow-up-right text-muted"></i>
                        </a>

                        <button type="button" id="btnOpcionImprimirActa" class="btn btn-outline-success text-start p-3 rounded-3 d-flex align-items-center justify-content-between shadow-sm">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success me-3 d-flex align-items-center justify-content-center modal-download-icon-box">
                                    <i class="bi bi-printer-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Imprimir Documento</div>
                                    <div class="small text-muted">Abre directamente la opción de imprimir de su dispositivo</div>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal zoom de imagen adjunta del libro fisico -->
    <div class="modal fade" id="modalZoomImagenActa" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-0">
                <div class="modal-header border-secondary py-2">
                    <h6 class="modal-title fw-bold" id="modalZoomImagenActaTitulo">Respaldo del Libro Físico</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center p-2">
                    <img src="" id="modalZoomImagenActaSrc" class="img-fluid rounded modal-zoom-img" alt="Zoom Libro Físico">
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor oculto de impresion directa -->
    <?php
    $numeroActa = intval($actaDetalle['numero_acta'] ?? 1);
    $anioEscolar = (string)($actaDetalle['anio_escolar'] ?? '');
    $estudiantes = is_array($actaDetalle['estudiantes'] ?? null) ? $actaDetalle['estudiantes'] : [];
    $totalEstImp = count($estudiantes);
    $paginasEstImp = [];
    if ($totalEstImp <= 28) {
        $paginasEstImp[] = $estudiantes;
    } else {
        $paginasEstImp[] = array_slice($estudiantes, 0, 26);
        $offsetImp = 26;
        while ($offsetImp < $totalEstImp) {
            $paginasEstImp[] = array_slice($estudiantes, $offsetImp, 28);
            $offsetImp += 28;
        }
    }
    $totalPagsImp = count($paginasEstImp);
    ?>
    <div id="documentoImpresionExpediente" aria-hidden="true">
        <div id="printDirectTablaWrap">
            <?php foreach ($paginasEstImp as $numPagImp => $grupoEstImp): ?>
                <?php
                $pagNum = $numPagImp + 1;
                $esPrimera = ($pagNum === 1);
                ?>
                <div class="sheet-container acta-sheet-container <?= !$esPrimera ? 'acta-page-continuacion' : '' ?>">
                    <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                        <span class="printDirectFechaHora"><?= date('d/m/Y, h:i a') ?></span>
                        <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                    </div>

                    <div class="acta-digital-header text-center">
                        <h4 class="acta-digital-title">Acta de Graduación #<?= $numeroActa ?></h4>
                        <h5 class="acta-digital-subtitle">
                            Año Escolar <?= Security::sanitize($anioEscolar) ?>
                            <?php if ($totalPagsImp > 1): ?>
                                <span class="small fw-normal text-muted">(Página <?= $pagNum ?> de <?= $totalPagsImp ?>)</span>
                            <?php endif; ?>
                        </h5>
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
                                <?php foreach ($grupoEstImp as $idx => $est): ?>
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

                <?php if ($pagNum < $totalPagsImp): ?>
                    <div class="html2pdf__page-break"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div id="printDirectImagenWrap" class="d-none text-center">
            <div class="sheet-container acta-sheet-container">
                <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                    <span class="printDirectFechaHora"><?= date('d/m/Y, h:i a') ?></span>
                    <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                </div>
                <div class="acta-book-print-box text-center mt-3">
                    <p class="small mb-2 fw-semibold" id="printDirectImagenSubtitulo"></p>
                    <div class="acta-book-img-frame">
                        <img src="" id="printDirectImagenSrc" alt="Imagen Libro" class="img-fluid print-doc-main-img">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
