<?php
// Vista de exportacion a PDF de acta de graduacion o foto del libro
$acta = $acta ?? [];
$imagenAdjunta = $imagenAdjunta ?? null;
$modoPrint = $modoPrint ?? 'acta';

$actaId = intval($acta['id'] ?? 0);
$numeroActa = intval($acta['numero_acta'] ?? 1);
$anioEscolar = (string)($acta['anio_escolar'] ?? '');
$estudiantes = is_array($acta['estudiantes'] ?? null) ? $acta['estudiantes'] : [];

if ($modoPrint === 'imagen' && !empty($imagenAdjunta)) {
    $ordenImg = intval($imagenAdjunta['orden'] ?? 1);
    $rawName = pathinfo((string)($imagenAdjunta['nombre_original'] ?? ''), PATHINFO_FILENAME);
    $cleanFilename = (string)preg_replace('/[^\p{L}\p{N}_\-\. ]/u', ' ', $rawName);
    $cleanFilename = trim((string)preg_replace('/\s+/', ' ', $cleanFilename));
    if ($cleanFilename === '') {
        $cleanFilename = "Acta_{$numeroActa}_{$anioEscolar}_Foto_{$ordenImg}";
    }
    $pageTitle = "Foto del Libro #{$ordenImg} - Año Escolar {$anioEscolar} (Acta #{$numeroActa})";

    $imgBase64 = '';
    if (!empty($imagenAdjunta['archivo'])) {
        $diskPath = dirname(__DIR__, 2) . '/public/uploads/' . basename((string)$imagenAdjunta['archivo']);
        if (file_exists($diskPath)) {
            $mime = ImageOptimizer::detectMimeType($diskPath);
            $imgBase64 = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($diskPath));
        }
    }
    if ($imgBase64 === '') {
        $imgBase64 = BASE_URL . '/actas/downloadImagen?img_id=' . intval($imagenAdjunta['id'] ?? 0);
    }
} else {
    $cleanFilename = 'Acta_Graduacion_' . preg_replace('/[^0-9\-]/', '', $anioEscolar) . '_Acta' . $numeroActa;
    $pageTitle = "Acta de Graduación #{$numeroActa} - Año Escolar {$anioEscolar}";
    $imgBase64 = '';
}

$totalEst = count($estudiantes);
$paginasEstudiantes = [];
if ($totalEst <= 28) {
    $paginasEstudiantes[] = $estudiantes;
} else {
    $paginasEstudiantes[] = array_slice($estudiantes, 0, 26);
    $offset = 26;
    while ($offset < $totalEst) {
        $paginasEstudiantes[] = array_slice($estudiantes, $offset, 28);
        $offset += 28;
    }
}
$totalPaginasDoc = count($paginasEstudiantes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($pageTitle) ?> - U.E. Josefina Pimentel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body class="expediente-print-body" data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Barra superior de acciones -->
    <div class="toolbar-top no-print d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span class="fw-bold me-2"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Vista de Documento PDF</span>
            <span id="pdfStatusMsg" class="badge bg-dark text-white"><i class="bi bi-info-circle me-1"></i> Listo para descargar</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="btnDescargarPdfManual" class="btn btn-dark btn-sm fw-semibold shadow-sm px-3">
                <i class="bi bi-download me-1"></i> Descargar PDF
            </button>
            <button type="button" id="btnCerrarPestanaPdf" class="btn btn-outline-light btn-sm">
                <i class="bi bi-x-lg me-1"></i> Cerrar Pestaña
            </button>
        </div>
    </div>

    <!-- Contenedor principal de hojas para PDF -->
    <div class="sheet-container-wrapper" id="sheetContainerPdf"
         data-pdf-type="acta"
         data-pdf-filename="<?= htmlspecialchars($cleanFilename, ENT_QUOTES, 'UTF-8') ?>.pdf">

        <?php if ($modoPrint === 'imagen' && !empty($imagenAdjunta)): ?>
            <div class="sheet-container acta-sheet-container">
                <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                    <span class="fechaHoraImpresion"><?= date('d/m/Y, h:i a') ?></span>
                    <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                </div>
                <div class="acta-book-print-box text-center mt-3">
                    <p class="small mb-2 fw-semibold">Respaldo Fotográfico del Libro Físico (Imagen #<?= intval($imagenAdjunta['orden'] ?? 1) ?>)</p>
                    <div class="acta-book-img-frame">
                        <img src="<?= $imgBase64 ?>" alt="Imagen del Libro Físico" class="img-fluid print-doc-main-img">
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($paginasEstudiantes as $numPag => $grupoEst): ?>
                <?php
                $paginaNum = $numPag + 1;
                $esPrimera = ($paginaNum === 1);
                ?>
                <div class="sheet-container acta-sheet-container <?= !$esPrimera ? 'acta-page-continuacion' : '' ?>">
                    <div class="acta-print-top-bar d-flex justify-content-between align-items-center">
                        <span class="fechaHoraImpresion"><?= date('d/m/Y, h:i a') ?></span>
                        <span class="fw-bold">U.E. Josefina Pimentel - Sistema Administrativo</span>
                    </div>

                    <div class="acta-digital-header text-center">
                        <h4 class="acta-digital-title">Acta de Graduación #<?= $numeroActa ?></h4>
                        <h5 class="acta-digital-subtitle">
                            Año Escolar <?= Security::sanitize($anioEscolar) ?>
                            <?php if ($totalPaginasDoc > 1): ?>
                                <span class="small fw-normal text-muted">(Página <?= $paginaNum ?> de <?= $totalPaginasDoc ?>)</span>
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
                                <?php foreach ($grupoEst as $idx => $est): ?>
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

                <?php if ($paginaNum < $totalPaginasDoc): ?>
                    <div class="html2pdf__page-break"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <script src="<?= BASE_URL ?>/assets/js/html2pdf.bundle.min.js" defer></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js?v=<?= time() ?>" defer></script>

</body>
</html>
