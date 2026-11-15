<?php
// modales para registro de personal y carga de expedientes
$selected = $selected ?? null;
$csrfToken = Security::generateCSRFToken();
?>

<!-- modal para filtrar la lista -->
<div class="modal fade" id="modalFiltros" tabindex="-1" aria-labelledby="modalFiltrosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalFiltrosLabel"><i class="bi bi-funnel me-2"></i>Filtrar Lista de Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formFiltrosAvanzados" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado del Personal</label>
                        <select id="filtroEstadoPersonal" class="form-select">
                            <option value="activo" selected>Personal Activo</option>
                            <option value="deshabilitado">Personal deshabilitado / inactivo</option>
                            <option value="todos">Todos (Activos y deshabilitados)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cargo</label>
                        <select id="filtroCargo" class="form-select">
                            <option value="">Todos los cargos</option>
                            <option value="docente">Docente</option>
                            <option value="obrero">Obrero</option>
                            <option value="administrativo">Administrativo</option>
                            <option value="directivo">Directivo</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sexo</label>
                            <select id="filtroSexo" class="form-select">
                                <option value="">Todos</option>
                                <option value="F">Femenino (F)</option>
                                <option value="M">Masculino (M)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Estado Civil</label>
                            <select id="filtroEstadoCivil" class="form-select">
                                <option value="">Todos</option>
                                <option value="soltero/a">Soltero/a</option>
                                <option value="casado/a">Casado/a</option>
                                <option value="viudo/a">Viudo/a</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">¿Padece Enfermedad?</label>
                        <select id="filtroEnfermedad" class="form-select">
                            <option value="">Todos</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Año de Nacimiento (Opcional)</label>
                        <input type="number" id="filtroAnioNac" class="form-control" placeholder="Ej. 1985" min="1930" max="<?= date('Y') ?>" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" id="btnModalResetFiltros">Limpiar Filtros</button>
                <button type="button" class="btn btn-primary fw-bold" id="btnModalAplicarFiltros" data-bs-dismiss="modal">Aplicar Filtros</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal registrar integrante del personal -->
<div class="modal fade" id="modalCrearPersonal" tabindex="-1" aria-labelledby="modalCrearPersonalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/personal/store" autocomplete="off" method="POST" enctype="multipart/form-data" class="modal-content shadow">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalCrearPersonalLabel"><i class="bi bi-person-plus-fill me-2"></i>Registrar Integrante del Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 modal-body-scrollable">

                <div class="text-center mb-4">
                    <div class="d-inline-block position-relative">
                        <img id="previewFotoCrear" src="" alt="Vista previa" class="rounded-circle border shadow-sm d-none modal-avatar-preview" width="110" height="110">
                        <div id="placeholderFotoCrear" class="rounded-circle bg-light text-muted border d-inline-flex align-items-center justify-content-center shadow-sm modal-avatar-placeholder">
                            <i class="bi bi-camera display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label for="inputFotoCrear" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-upload me-1"></i> Seleccionar Foto de Perfil
                        </label>
                        <input type="file" id="inputFotoCrear" name="foto" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif" autocomplete="off">
                        <div class="small text-muted mt-1">Formatos: JPG, PNG, WEBP, GIF (Máx. 8MB)</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombres (*)</label>
                        <input type="text" name="nombres" class="form-control" required maxlength="80" placeholder="Ej. Ana María" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Apellidos (*)</label>
                        <input type="text" name="apellidos" class="form-control" required maxlength="80" placeholder="Ej. García Rodríguez" autocomplete="off">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nacionalidad y Cédula (*)</label>
                        <div class="input-group">
                            <select name="nacionalidad" class="form-select input-nacionalidad-select" aria-label="Nacionalidad" required>
                                <option value="V" selected>V</option>
                                <option value="E">E</option>
                            </select>
                            <input type="text" name="cedula" class="form-control" required placeholder="Ej. 12345678" pattern="[0-9]+" inputmode="numeric" maxlength="10" title="Solo se permiten números" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha Nacimiento (*)</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cargo (*)</label>
                        <select name="cargo" class="form-select" required>
                            <option value="Docente">Docente</option>
                            <option value="Obrero">Obrero</option>
                            <option value="Administrativo">Administrativo</option>
                            <option value="Directivo">Directivo</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="20" placeholder="Ej. 04141234567" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" class="form-control" maxlength="120" placeholder="ejemplo@correo.com" autocomplete="off">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sexo (*)</label>
                        <select name="sexo" class="form-select" required>
                            <option value="F">Femenino (F)</option>
                            <option value="M">Masculino (M)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado Civil (*)</label>
                        <select name="estado_civil" class="form-select" required>
                            <option value="Soltero/a">Soltero/a</option>
                            <option value="Casado/a">Casado/a</option>
                            <option value="Viudo/a">Viudo/a</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dirección de Domicilio (*)</label>
                    <textarea name="direccion" class="form-control" rows="2" required maxlength="250" placeholder="Indique la dirección residencial completa..."></textarea>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">¿Padece enfermedad? (*)</label>
                        <select name="padece_enfermedad" id="padeceSelectCrear" class="form-select" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Detalle de la Enfermedad</label>
                        <input type="text" name="detalle_enfermedad" id="detalleInputCrear" class="form-control" maxlength="200" placeholder="Especifique la condición médica..." disabled autocomplete="off">
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success fw-bold px-4">Guardar Personal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal editar informacion del personal -->
<div class="modal fade" id="modalEditarPersonal" tabindex="-1" aria-labelledby="modalEditarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/personal/update" autocomplete="off" method="POST" enctype="multipart/form-data" class="modal-content shadow">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="id" id="editPersonalId" value="">
            <input type="hidden" name="redirect_to" id="editPersonalRedirectTo" value="perfil">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalEditarPersonalLabel"><i class="bi bi-pencil-square me-2"></i>Editar Datos del Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 modal-body-scrollable">

                <div class="text-center mb-4">
                    <div class="d-inline-block position-relative">
                        <img id="previewFotoEditar" src="" alt="Foto" class="rounded-circle border shadow-sm d-none modal-avatar-preview" width="110" height="110">
                        <div id="placeholderFotoEditar" class="rounded-circle bg-light text-muted border d-inline-flex align-items-center justify-content-center shadow-sm modal-avatar-placeholder">
                            <i class="bi bi-person display-6"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label for="inputFotoEditar" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera me-1"></i> Cambiar Foto de Perfil
                        </label>
                        <input type="file" id="inputFotoEditar" name="foto" class="d-none" accept="image/jpeg,image/png,image/webp,image/gif" autocomplete="off">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombres (*)</label>
                        <input type="text" name="nombres" id="editPersonalNombres" class="form-control" required maxlength="80" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Apellidos (*)</label>
                        <input type="text" name="apellidos" id="editPersonalApellidos" class="form-control" required maxlength="80" autocomplete="off">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nacionalidad y Cédula (*)</label>
                        <div class="input-group">
                            <select name="nacionalidad" id="editPersonalNacionalidad" class="form-select input-nacionalidad-select" aria-label="Nacionalidad" required>
                                <option value="V">V</option>
                                <option value="E">E</option>
                            </select>
                            <input type="text" name="cedula" id="editPersonalCedula" class="form-control" required pattern="[0-9]+" inputmode="numeric" maxlength="10" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha Nacimiento (*)</label>
                        <input type="date" name="fecha_nacimiento" id="editPersonalFechaNac" class="form-control" required autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cargo (*)</label>
                        <select name="cargo" id="editPersonalCargo" class="form-select" required>
                            <?php foreach (['Docente', 'Obrero', 'Administrativo', 'Directivo'] as $c): ?>
                                <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" id="editPersonalTelefono" class="form-control" maxlength="20" autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" name="correo" id="editPersonalCorreo" class="form-control" maxlength="120" autocomplete="off">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sexo (*)</label>
                        <select name="sexo" id="editPersonalSexo" class="form-select" required>
                            <option value="F">Femenino (F)</option>
                            <option value="M">Masculino (M)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Estado Civil (*)</label>
                        <select name="estado_civil" id="editPersonalEstadoCivil" class="form-select" required>
                            <?php foreach (['Soltero/a', 'Casado/a', 'Viudo/a'] as $ec): ?>
                                <option value="<?= $ec ?>"><?= $ec ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Dirección de Domicilio (*)</label>
                    <textarea name="direccion" id="editPersonalDireccion" class="form-control" rows="2" required maxlength="250"></textarea>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">¿Padece enfermedad? (*)</label>
                        <select name="padece_enfermedad" id="padeceSelectEditar" class="form-select" required>
                            <option value="No">No</option>
                            <option value="Si">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Detalle de la Enfermedad</label>
                        <input type="text" name="detalle_enfermedad" id="detalleInputEditar" class="form-control" maxlength="200" disabled autocomplete="off">
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal deshabilitar personal -->
<div class="modal fade" id="modalDeshabilitarPersonal" tabindex="-1" aria-labelledby="modalDeshabilitarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="modalDeshabilitarPersonalLabel"><i class="bi bi-person-slash me-2"></i>Deshabilitar Integrante del Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/personal/deshabilitar" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="deshabilitarPersonalId" value="">
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-person-slash display-3 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">¿Está seguro de que desea deshabilitar a este integrante?</h5>
                    <p class="text-danger fw-semibold mb-3 fs-5 text-break" id="deshabilitarPersonalNombreText"></p>
                    <div class="alert alert-secondary text-start small mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Nota:</strong> El integrante no se borrará de la base de datos ni se perderán sus documentos, pero dejará de mostrarse en la lista activa predeterminada y no podrá recibir nuevos registros hasta ser rehabilitado.
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">No, Cancelar</button>
                    <button type="submit" id="btnConfirmDeshabilitarPersonal" class="btn btn-danger fw-bold px-4" disabled>
                        <i class="bi bi-hourglass-split me-1"></i> Espere (5s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal habilitar personal -->
<div class="modal fade" id="modalHabilitarPersonal" tabindex="-1" aria-labelledby="modalHabilitarPersonalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalHabilitarPersonalLabel"><i class="bi bi-person-check-fill me-2"></i>Habilitar Integrante del Personal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/personal/habilitar" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="habilitarPersonalId" value="">
                <input type="hidden" name="redirect_to" id="habilitarRedirectTo" value="lista">
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-person-check display-3 text-success mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">¿Desea volver a habilitar a este integrante?</h5>
                    <p class="text-success fw-semibold mb-3 fs-5 text-break" id="habilitarPersonalNombreText"></p>
                    <div class="alert alert-info text-start small mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Información:</strong> El integrante volverá a mostrarse en la lista activa de personal, permitiendo la edición de sus datos y la carga de registros a su expediente.
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-check-lg me-1"></i> Sí, Habilitar Integrante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($selected): ?>
<!-- Modal agregar registro al expediente -->
<div class="modal fade" id="modalAgregarExpediente" tabindex="-1" aria-labelledby="modalAgregarExpedienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/personal/storeExpediente" autocomplete="off" method="POST" enctype="multipart/form-data" class="modal-content shadow">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="personal_id" value="<?= $selected['id'] ?>">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="modalAgregarExpedienteLabel"><i class="bi bi-folder-plus me-2"></i>Añadir Registro al Expediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 modal-body-scrollable">

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0">1. Título del Documento / Justificativo (*)</label>
                        <span class="small text-muted"><span id="charCountTituloCrear" class="fw-bold text-primary">0</span> / 30 caracteres</span>
                    </div>
                    <input type="text" name="titulo" id="inputExpTituloCrear" class="form-control" maxlength="30" required placeholder="Ej. Reposo Médico 15 Días" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">2. Documento o Imagen Adjunta (*) (Obligatorio)</label>
                    <input type="file" id="inputArchivoExpCrear" name="archivo_adjunto" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" required autocomplete="off">
                    <div class="form-text">Admite imágenes (JPG, PNG, WEBP, GIF) o documentos PDF (Máx. 8MB).</div>

                    <div id="previewContainerExpCrear" class="mt-2 text-center border rounded p-2 bg-light d-none">
                        <span class="small text-muted d-block mb-1">Vista previa del archivo a adjuntar:</span>
                        <img id="previewImgExpCrear" src="" alt="Vista previa" class="img-fluid rounded d-none modal-preview-doc-img">
                        <div id="previewPdfExpCrear" class="d-none text-danger py-2">
                            <i class="bi bi-file-earmark-pdf-fill fs-2 d-block"></i>
                            <span class="fw-semibold small">Archivo PDF seleccionado</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0">3. Descripción Detallada (*) (Máx. 100 caracteres)</label>
                        <span class="small text-muted"><span id="charCountExpCrear" class="fw-bold text-primary">0</span> / 100 caracteres</span>
                    </div>
                    <textarea name="descripcion" id="inputExpDescripcionCrear" class="form-control" rows="2" maxlength="100" required placeholder="Observación breve o diagnóstico (máximo 100 caracteres)..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="bi bi-calendar-check text-primary me-1"></i> Fecha de Vencimiento de este Documento (Opcional)
                    </label>
                    <input type="date" name="fecha_vencimiento" class="form-control" autocomplete="off">
                    <div class="form-text">Si indica una fecha, el sistema marcará automáticamente como <strong class="text-danger">VENCIDO</strong> este registro una vez alcanzado el día.</div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark px-4">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal editar registro del expediente -->
<div class="modal fade" id="modalEditarExpediente" tabindex="-1" aria-labelledby="modalEditarExpedienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= BASE_URL ?>/personal/updateExpediente" autocomplete="off" method="POST" enctype="multipart/form-data" class="modal-content shadow">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="personal_id" value="<?= $selected['id'] ?>">
            <input type="hidden" name="exp_id" id="editExpId" value="">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalEditarExpedienteLabel"><i class="bi bi-pencil-square me-2"></i>Editar Registro del Expediente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 modal-body-scrollable">

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0">Título del Documento / Justificativo (*)</label>
                        <span class="small text-muted"><span id="charCountTituloEditar" class="fw-bold text-primary">0</span> / 30 caracteres</span>
                    </div>
                    <input type="text" name="titulo" id="editExpTitulo" class="form-control" maxlength="30" required autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Reemplazar Archivo Adjunto (Opcional)</label>
                    <input type="file" id="inputArchivoExpEditar" name="archivo_adjunto" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" autocomplete="off">
                    <div class="form-text">Deje este campo vacío si desea conservar el archivo adjunto actual.</div>

                    <div id="previewContainerExpEditar" class="mt-2 text-center border rounded p-2 bg-light d-none">
                        <span class="small text-muted d-block mb-1">Nuevo archivo seleccionado:</span>
                        <img id="previewImgExpEditar" src="" alt="Vista previa" class="img-fluid rounded d-none modal-preview-doc-img">
                        <div id="previewPdfExpEditar" class="d-none text-danger py-2">
                            <i class="bi bi-file-earmark-pdf-fill fs-2 d-block"></i>
                            <span class="fw-semibold small">Nuevo archivo PDF seleccionado</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0">Descripción (*) (Máx. 100 caracteres)</label>
                        <span class="small text-muted"><span id="charCountExpEditar" class="fw-bold text-primary">0</span> / 100 caracteres</span>
                    </div>
                    <textarea name="descripcion" id="editExpDescripcion" class="form-control" rows="2" maxlength="100" required placeholder="Máximo 100 caracteres..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark">
                        <i class="bi bi-calendar-check text-primary me-1"></i> Fecha de Vencimiento
                    </label>
                    <input type="date" name="fecha_vencimiento" id="editExpFechaVencimiento" class="form-control" autocomplete="off">
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary fw-bold px-4">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal eliminar registro del expediente -->
<div class="modal fade" id="modalEliminarExpediente" tabindex="-1" aria-labelledby="modalEliminarExpedienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="modalEliminarExpedienteLabel"><i class="bi bi-trash3-fill me-2"></i>Eliminar Registro del Expediente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="<?= BASE_URL ?>/personal/deleteExpediente" autocomplete="off" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="personal_id" value="<?= $selected['id'] ?>">
                <input type="hidden" name="exp_id" id="deleteExpId" value="">
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-file-earmark-x display-3 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold mb-2">¿Desea eliminar este registro del expediente?</h5>
                    <p class="text-danger fw-semibold mb-3 fs-5 text-break" id="deleteExpTituloText"></p>
                    <p class="text-muted small mb-0">El archivo adjunto se borrará físicamente del servidor de forma irreversible.</p>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnConfirmDeleteExp" class="btn btn-danger fw-bold px-4" disabled>
                        <i class="bi bi-hourglass-split me-1"></i> Espere (5s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal zoom para ver imagen en pantalla completa -->
<div class="modal fade" id="modalZoomImg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-white" id="modalZoomTitulo"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="modalZoomSrc" src="" alt="Visualizador" class="img-fluid rounded modal-zoom-img">
            </div>
        </div>
    </div>
</div>

<!-- modal opciones de descarga e imprecion -->
<div class="modal fade" id="modalOpcionesDescarga" tabindex="-1" aria-labelledby="modalOpcionesDescargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalOpcionesDescargaLabel">
                    <i class="bi bi-download me-2"></i>Opciones de Descarga e Impresión
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-secondary small mb-3">
                    Seleccione una opción para el documento: <strong id="descargaTituloDoc" class="text-dark"></strong>
                </p>

                <div class="list-group gap-2">
                    <a href="#" id="btnDescargaOriginal" class="list-group-item list-group-item-action border rounded p-2.5 p-sm-3 d-flex align-items-center shadow-xs">
                        <div class="rounded-circle bg-light p-2 text-primary me-2.5 me-sm-3 d-flex align-items-center justify-content-center modal-download-icon-box flex-shrink-0">
                            <i class="bi bi-file-earmark-image fs-4"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 fw-bold text-dark">Descargar como Imagen</h6>
                            <small class="text-muted d-block">Descarga directa del archivo original adjunto con el título del documento.</small>
                        </div>
                        <i class="bi bi-download text-primary ms-2 fs-5 flex-shrink-0"></i>
                    </a>

                    <a href="#" id="btnDescargaPdf" target="_blank" class="list-group-item list-group-item-action border rounded p-2.5 p-sm-3 d-flex align-items-center shadow-xs">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger me-2.5 me-sm-3 d-flex align-items-center justify-content-center modal-download-icon-box flex-shrink-0">
                            <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 fw-bold text-dark">Descargar como PDF</h6>
                            <small class="text-muted d-block">Abre el formato oficial estructurado en una nueva pestaña para visualizar o guardar como PDF.</small>
                        </div>
                        <i class="bi bi-box-arrow-up-right text-danger ms-2 fs-5 flex-shrink-0"></i>
                    </a>

                    <button type="button" id="btnImprimirDoc" class="list-group-item list-group-item-action border rounded p-2.5 p-sm-3 d-flex align-items-center shadow-xs text-start">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success me-2.5 me-sm-3 d-flex align-items-center justify-content-center modal-download-icon-box flex-shrink-0">
                            <i class="bi bi-printer-fill fs-4"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 fw-bold text-dark">Imprimir</h6>
                            <small class="text-muted d-block">Abre directamente el cuadro de impresión de su dispositivo sin salir de esta página.</small>
                        </div>
                        <i class="bi bi-printer text-success ms-2 fs-5 flex-shrink-0"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Contenedor exclusivo para impresion directa del expediente -->
<div id="documentoImpresionExpediente" class="sheet-container print-sheet-expediente">
    <div class="header-print d-flex justify-content-between align-items-center">
        <span id="printHeaderTimestamp"><?= date('d/m/Y, h:i a') ?></span>
        <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
    </div>

    <div class="profile-box">
        <div class="row align-items-center g-3">
            <div class="col-auto text-center">
                <?php if (!empty($selected['foto'])): ?>
                    <img src="<?= BASE_URL ?>/personal/downloadFile?foto_id=<?= $selected['id'] ?>" alt="Foto" class="rounded-circle border print-doc-profile-avatar-img" width="58" height="58">
                <?php else: ?>
                    <div class="rounded-circle bg-white text-dark border d-flex align-items-center justify-content-center fw-bold print-doc-profile-avatar-placeholder">
                        <?= strtoupper(substr($selected['nombres'] ?? 'P', 0, 1) . substr($selected['apellidos'] ?? '', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col">
                <h5 class="fw-bold text-dark mb-1"><?= Security::sanitize(($selected['nombres'] ?? '') . ' ' . ($selected['apellidos'] ?? '')) ?></h5>
                <div class="row g-2 small text-dark">
                    <div class="col-12 col-md-6 print-col-6">
                        <div><i class="bi bi-person-vcard me-1"></i>Cédula: <strong><?= Security::sanitize(($selected['nacionalidad'] ?? 'V') . '-' . ($selected['cedula'] ?? '')) ?></strong></div>
                        <div><i class="bi bi-briefcase me-1"></i>Cargo: <strong><?= Security::sanitize($selected['cargo'] ?? 'No especificado') ?></strong></div>
                        <div><i class="bi bi-gender-ambiguous me-1"></i>Sexo: <strong><?= Security::sanitize($selected['sexo'] ?? 'N/A') ?></strong></div>
                        <div><i class="bi bi-heart me-1"></i>Estado Civil: <strong><?= Security::sanitize($selected['estado_civil'] ?? 'N/A') ?></strong></div>
                    </div>
                    <div class="col-12 col-md-6 print-col-6">
                        <div><i class="bi bi-telephone me-1"></i>Teléfono: <strong><?= Security::sanitize($selected['telefono'] ?: 'N/A') ?></strong></div>
                        <div><i class="bi bi-envelope me-1"></i>Correo: <strong><?= Security::sanitize($selected['correo'] ?: 'N/A') ?></strong></div>
                        <div><i class="bi bi-geo-alt me-1"></i>Dirección: <strong><?= Security::sanitize($selected['direccion'] ?? 'N/A') ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 id="printDocTitulo" class="doc-title text-center"></h4>

    <div class="doc-img-container">
        <img id="printDocImg" src="" alt="Documento" class="img-fluid print-doc-main-img">
    </div>

    <div class="doc-desc-box">
        <span class="small fw-bold text-dark d-block mb-1"><i class="bi bi-chat-left-text me-1"></i>Descripción del Registro:</span>
        <p id="printDocDesc" class="mb-0 text-dark print-doc-desc-text"></p>
    </div>

    <div class="footer-vencimiento text-dark">
        <div>
            <i class="bi bi-calendar-event me-1"></i>Fecha de Registro en Sistema:
            <strong id="printDocFecha"></strong>
        </div>
        <div id="printDocVencimientoContainer" class="mt-1 d-none">
            <i class="bi bi-hourglass-split me-1"></i>Fecha de Vencimiento:
            <strong id="printDocVencimiento"></strong>
        </div>
    </div>
</div>
<?php endif; ?>
