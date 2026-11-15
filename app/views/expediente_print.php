<?php
// vista para generar el pdf del documento del expediente
$exp = $exp ?? [];
$personal = $personal ?? [];

$isPdf = (strpos(($exp['mime_type'] ?? ''), 'pdf') !== false);
$fileUrl = BASE_URL . "/personal/downloadFile?exp_id=" . ($exp['id'] ?? 0);

// convertimos las imagenes a base64 para que html2pdf no falle al capturar
$fotoBase64 = '';
if (!empty($personal['foto'])) {
    $fotoDiskPath = dirname(__DIR__, 2) . '/public/uploads/' . basename((string)$personal['foto']);
    if (file_exists($fotoDiskPath)) {
        $fotoMime = ImageOptimizer::detectMimeType($fotoDiskPath);
        $fotoBase64 = 'data:' . $fotoMime . ';base64,' . base64_encode((string)file_get_contents($fotoDiskPath));
    }
}

$docImgBase64 = $fileUrl;
if (!$isPdf && !empty($exp['archivo_adjunto'])) {
    $docDiskPath = dirname(__DIR__, 2) . '/public/uploads/' . basename((string)$exp['archivo_adjunto']);
    if (file_exists($docDiskPath)) {
        $docMime = $exp['mime_type'] ?: ImageOptimizer::detectMimeType($docDiskPath);
        $docImgBase64 = 'data:' . $docMime . ';base64,' . base64_encode((string)file_get_contents($docDiskPath));
    }
}

$nombreCompleto = trim(($personal['nombres'] ?? '') . ' ' . ($personal['apellidos'] ?? ''));
$cedulaFormato = ($personal['nacionalidad'] ?? 'V') . '-' . ($personal['cedula'] ?? '');
$hasExpiration = !empty($exp['fecha_vencimiento']);

$rawTitle = trim((string)($exp['titulo'] ?? 'Documento'));
$cleanTitle = (string)preg_replace('/[^\p{L}\p{N}_\-\. ]/u', '', $rawTitle);
$cleanTitle = trim((string)preg_replace('/\s+/', ' ', $cleanTitle));
if ($cleanTitle === '') {
    $cleanTitle = 'Documento_' . ($exp['id'] ?? 'Expediente');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($cleanTitle) ?> - <?= Security::sanitize($nombreCompleto) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body class="expediente-print-body" data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">

    <!-- barra superior -->
    <div class="toolbar-top no-print d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="fw-bold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Vista de Documento PDF</span>
            <span id="pdfStatusMsg" class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i> Listo para guardar</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnDescargarPdfManual" class="btn btn-danger btn-sm fw-semibold shadow-sm px-3">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Guardar archivo PDF
            </button>
            <button type="button" id="btnCerrarPestanaPdf" class="btn btn-outline-light btn-sm">
                <i class="bi bi-x-lg me-1"></i> Cerrar Pestaña
            </button>
        </div>
    </div>

    <!-- hoja de impresion -->
    <div class="sheet-container" id="sheetContainerPdf"
         data-pdf-type="expediente"
         data-pdf-filename="<?= htmlspecialchars($cleanTitle, ENT_QUOTES, 'UTF-8') ?>.pdf">

        <div class="header-print d-flex justify-content-between align-items-center">
            <span id="fechaHoraActual"><?= date('d/m/Y, h:i a') ?></span>
            <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
        </div>

        <div class="profile-box">
            <div class="row align-items-center g-3">
                <div class="col-auto text-center">
                    <?php if ($fotoBase64): ?>
                        <img src="<?= $fotoBase64 ?>" alt="Foto" class="rounded-circle border print-profile-avatar-img" width="58" height="58">
                    <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold print-profile-avatar-placeholder">
                            <?= strtoupper(substr($personal['nombres'] ?? 'P', 0, 1) . substr($personal['apellidos'] ?? '', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <h5 class="fw-bold text-dark mb-1"><?= Security::sanitize($nombreCompleto) ?></h5>
                    <div class="row g-2 small text-dark">
                        <div class="col-12 col-md-6 print-col-6">
                            <div><i class="bi bi-person-vcard me-1"></i>Cédula: <strong><?= Security::sanitize($cedulaFormato) ?></strong></div>
                            <div><i class="bi bi-briefcase me-1"></i>Cargo: <strong><?= Security::sanitize($personal['cargo'] ?? 'No especificado') ?></strong></div>
                            <div><i class="bi bi-gender-ambiguous me-1"></i>Sexo: <strong><?= Security::sanitize($personal['sexo'] ?? 'N/A') ?></strong></div>
                            <div><i class="bi bi-heart me-1"></i>Estado Civil: <strong><?= Security::sanitize($personal['estado_civil'] ?? 'N/A') ?></strong></div>
                        </div>
                        <div class="col-12 col-md-6 print-col-6">
                            <div><i class="bi bi-telephone me-1"></i>Teléfono: <strong><?= Security::sanitize($personal['telefono'] ?: 'N/A') ?></strong></div>
                            <div><i class="bi bi-envelope me-1"></i>Correo: <strong><?= Security::sanitize($personal['correo'] ?: 'N/A') ?></strong></div>
                            <div><i class="bi bi-geo-alt me-1"></i>Dirección: <strong><?= Security::sanitize($personal['direccion'] ?? 'N/A') ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="doc-title"><?= Security::sanitize($exp['titulo'] ?? '') ?></h2>

        <div class="doc-img-container">
            <?php if (!empty($exp['archivo_adjunto'])): ?>
                <?php if ($isPdf): ?>
                    <div class="py-4">
                        <i class="bi bi-file-earmark-pdf-fill text-dark display-3 d-block mb-2"></i>
                        <p class="fw-semibold text-dark mb-2">Este registro contiene un documento PDF anexo.</p>
                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-dark btn-sm no-print">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Ver PDF Original
                        </a>
                    </div>
                <?php else: ?>
                    <img src="<?= $docImgBase64 ?>" alt="<?= Security::sanitize($exp['titulo'] ?? '') ?>">
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted my-3">Sin archivo visual adjunto</p>
            <?php endif; ?>
        </div>

        <div class="doc-desc-box">
            <span class="small fw-bold text-dark d-block mb-1"><i class="bi bi-chat-left-text me-1"></i>Descripción del Registro:</span>
            <p class="mb-0 text-dark doc-desc-text-print"><?= Security::sanitize($exp['descripcion'] ?? '') ?></p>
        </div>

        <div class="footer-vencimiento text-dark">
            <div>
                <i class="bi bi-calendar-event me-1"></i>Fecha de Registro en Sistema:
                <strong><?= date('d/m/Y', strtotime($exp['creado_en'] ?? 'now')) ?></strong>
            </div>
            <?php if ($hasExpiration): ?>
                <div class="mt-1">
                    <i class="bi bi-hourglass-split me-1"></i>Fecha de Vencimiento:
                    <strong><?= date('d/m/Y', strtotime($exp['fecha_vencimiento'])) ?></strong>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="<?= BASE_URL ?>/assets/js/html2pdf.bundle.min.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= time() ?>" defer></script>

</body>
</html>
