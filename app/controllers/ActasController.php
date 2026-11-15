<?php
// Controlador de actas de graduacion, buscador de graduados e impresion

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/Acta.php';

class ActasController extends Controller {
    private Acta $actaModel;
    private string $uploadDir;

    public function __construct() {
        $this->checkAuth();
        $this->actaModel = new Acta();
        $this->uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index(): void {
        $actaDetalle = null;
        if (!empty($_GET['id'])) {
            $actaId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($actaId) {
                $actaDetalle = $this->actaModel->getActaById($actaId) ?: null;
                if (!$actaDetalle) {
                    $_SESSION['flash_error'] = "El acta solicitada no existe.";
                    header("Location: " . BASE_URL . "/actas");
                    exit;
                }
            }
        }

        $this->view('actas', [
            'actaDetalle'    => $actaDetalle,
            'aniosEscolares' => $this->actaModel->getAniosEscolares()
        ]);
    }

    public function search(): void {
        header('Content-Type: application/json; charset=utf-8');

        $q = trim((string)($_GET['q'] ?? ''));
        $anioEscolar = trim((string)($_GET['anio_escolar'] ?? ''));
        $fechaNacRaw = trim((string)($_GET['fecha_nacimiento'] ?? ''));
        $estado = trim((string)($_GET['estado'] ?? 'activo'));

        if (!in_array($estado, ['activo', 'inactivo', 'todos'], true)) {
            $estado = 'activo';
        }

        $fechaIso = '';
        if ($fechaNacRaw !== '') {
            $fechaIso = $this->normalizarFechaEntrada($fechaNacRaw);
            if ($fechaIso === false) {
                echo json_encode([
                    'ok'      => false,
                    'message' => 'La fecha de nacimiento ingresada en el filtro no es válida.',
                    'actas'   => []
                ]);
                exit;
            }
        }

        $resultados = $this->actaModel->buscarActas([
            'q'                => $q,
            'anio_escolar'     => $anioEscolar,
            'fecha_nacimiento' => $fechaIso,
            'estado'           => $estado
        ]);

        echo json_encode([
            'ok'    => true,
            'total' => count($resultados),
            'actas' => $resultados
        ]);
        exit;
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $anioEscolar = $this->validarYFormatearAnioEscolar(trim((string)($_POST['anio_escolar'] ?? '')));
        if ($anioEscolar === false) {
            $_SESSION['flash_error'] = "El Año Escolar es obligatorio y debe tener un formato de años consecutivos válido (ejemplo: 2008-2009).";
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        $estudiantesProcesados = $this->procesarEstudiantesFormulario($_POST['estudiantes'] ?? [], $errorEst);
        if ($estudiantesProcesados === false) {
            $_SESSION['flash_error'] = $errorEst ?: "Verifique los datos de los estudiantes del acta.";
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        $imagenesSubidas = $this->handleMultipleImageUploads('imagenes', 4, $errorImg);
        if ($imagenesSubidas === false) {
            $_SESSION['flash_error'] = $errorImg ?: "Error al procesar las imágenes adjuntas.";
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $actaId = $this->actaModel->createActa($anioEscolar, $estudiantesProcesados, $imagenesSubidas, $userId);

        if ($actaId) {
            $actaCreada = $this->actaModel->getActaById($actaId);
            $numActa = (int)($actaCreada['numero_acta'] ?? 1);
            $totalEst = count($estudiantesProcesados);
            $this->logAction("Registró una nueva Acta de Graduación #{$numActa} (Año Escolar {$anioEscolar}) con {$totalEst} estudiante(s).");
            $_SESSION['flash_success'] = "Acta de Graduación #{$numActa} (Año Escolar {$anioEscolar}) registrada exitosamente.";
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        $_SESSION['flash_error'] = "No se pudo guardar el acta de graduación. Intente nuevamente.";
        header("Location: " . BASE_URL . "/actas");
        exit;
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $actaId = filter_var($_POST['acta_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$actaId) {
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        $actaActual = $this->actaModel->getActaById($actaId);
        if (!$actaActual) {
            $_SESSION['flash_error'] = "El acta solicitada no existe.";
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        if ((int)($actaActual['activo'] ?? 1) === 0) {
            $_SESSION['flash_error'] = "No se puede editar un acta desactivada. Reactívela primero si requiere modificarla.";
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        $anioEscolar = $this->validarYFormatearAnioEscolar(trim((string)($_POST['anio_escolar'] ?? '')));
        if ($anioEscolar === false) {
            $_SESSION['flash_error'] = "El Año Escolar debe tener un formato de años consecutivos válido (ejemplo: 2008-2009).";
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        $estudiantesProcesados = $this->procesarEstudiantesFormulario($_POST['estudiantes'] ?? [], $errorEst);
        if ($estudiantesProcesados === false) {
            $_SESSION['flash_error'] = $errorEst ?: "Verifique los datos de los estudiantes.";
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        $idsConservar = isset($_POST['imagenes_conservar']) && is_array($_POST['imagenes_conservar'])
            ? array_map('intval', $_POST['imagenes_conservar'])
            : [];

        $cuposDisponibles = max(0, 4 - count($idsConservar));
        $nuevasImagenes = $this->handleMultipleImageUploads('imagenes', $cuposDisponibles, $errorImg);
        if ($nuevasImagenes === false) {
            $_SESSION['flash_error'] = $errorImg ?: "Error al subir las nuevas imágenes del acta.";
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        if ($this->actaModel->updateActa($actaId, $anioEscolar, $estudiantesProcesados, $idsConservar, $nuevasImagenes)) {
            $actaActualizada = $this->actaModel->getActaById($actaId);
            $numActa = (int)($actaActualizada['numero_acta'] ?? 1);
            $this->logAction("Actualizó el Acta de Graduación #{$numActa} (Año Escolar {$anioEscolar}, " . count($estudiantesProcesados) . " estudiantes).");
            $_SESSION['flash_success'] = "Acta de Graduación #{$numActa} (Año Escolar {$anioEscolar}) actualizada correctamente.";
        } else {
            $_SESSION['flash_error'] = "Ocurrió un error al actualizar el acta.";
        }

        header("Location: " . BASE_URL . "/actas?id=" . $actaId);
        exit;
    }

    public function deshabilitar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
        $actaId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
        $redirectDetalle = ($_POST['redirect_to'] ?? '') === 'detalle';

        if ($actaId) {
            $acta = $this->actaModel->getActaById($actaId);
            if ($acta) {
                $numActa = (int)($acta['numero_acta'] ?? 1);
                $this->actaModel->setEstadoActa($actaId, 0);
                $this->logAction("Desactivó el Acta de Graduación #{$numActa} (Año Escolar {$acta['anio_escolar']}).");
                $_SESSION['flash_success'] = "El Acta #{$numActa} (Año Escolar {$acta['anio_escolar']}) ha sido desactivada.";
            }
        }

        $destino = ($redirectDetalle && $actaId) ? (BASE_URL . "/actas?id=" . $actaId) : (BASE_URL . "/actas");
        header("Location: " . $destino);
        exit;
    }

    public function habilitar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/actas");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
        $actaId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);

        if ($actaId) {
            $acta = $this->actaModel->getActaById($actaId);
            if ($acta) {
                $numActa = (int)($acta['numero_acta'] ?? 1);
                $this->actaModel->setEstadoActa($actaId, 1);
                $this->logAction("Reactivó el Acta de Graduación #{$numActa} (Año Escolar {$acta['anio_escolar']}).");
                $_SESSION['flash_success'] = "El Acta #{$numActa} (Año Escolar {$acta['anio_escolar']}) ha sido habilitada nuevamente.";
            }
            header("Location: " . BASE_URL . "/actas?id=" . $actaId);
            exit;
        }

        header("Location: " . BASE_URL . "/actas");
        exit;
    }

    public function downloadImagen(): void {
        $imgId = filter_var($_GET['img_id'] ?? 0, FILTER_VALIDATE_INT);
        $forceDownload = isset($_GET['download']) && $_GET['download'] == '1';

        if (!$imgId) {
            http_response_code(404);
            die("Imagen no especificada.");
        }

        $img = $this->actaModel->getImagenById($imgId);
        if (!$img || empty($img['archivo'])) {
            http_response_code(404);
            die("La imagen solicitada no existe.");
        }

        $filePath = $this->uploadDir . basename((string)$img['archivo']);
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("El archivo físico de la imagen no se encuentra en el servidor.");
        }

        $mime = $img['mime_type'] ?: ImageOptimizer::detectMimeType($filePath, 'image/jpeg');
        $ext = pathinfo((string)$img['archivo'], PATHINFO_EXTENSION) ?: 'jpg';
        $anioClean = (string)preg_replace('/[^0-9\-]/', '', (string)($img['anio_escolar'] ?? 'Acta'));
        $numActa = (int)($img['numero_acta'] ?? 1);
        $downloadName = "Acta_{$numActa}_{$anioClean}_Libro_Foto_{$img['orden']}.{$ext}";

        $formatParam = trim((string)($_GET['format'] ?? ''));
        $preferWebp = (!$forceDownload && $formatParam !== 'original');
        ImageOptimizer::streamFile($filePath, $mime, $downloadName, $forceDownload, $preferWebp, $this->uploadDir);
    }

    public function printActa(): void {
        $actaId = filter_var($_GET['acta_id'] ?? 0, FILTER_VALIDATE_INT);
        $imgId = filter_var($_GET['img_id'] ?? 0, FILTER_VALIDATE_INT);

        $acta = null;
        $imagenAdjunta = null;
        $modoPrint = 'acta';

        if ($imgId) {
            $imagenAdjunta = $this->actaModel->getImagenById($imgId);
            if (!$imagenAdjunta) {
                http_response_code(404);
                die("La imagen solicitada no existe.");
            }
            $acta = $this->actaModel->getActaById((int)$imagenAdjunta['acta_id']);
            $modoPrint = 'imagen';
        } elseif ($actaId) {
            $acta = $this->actaModel->getActaById($actaId);
            if (!$acta) {
                http_response_code(404);
                die("El acta solicitada no existe.");
            }
        } else {
            http_response_code(404);
            die("Parámetros de impresión no válidos.");
        }

        require_once __DIR__ . "/../views/acta_print.php";
        exit;
    }

    // Validacion de filas de estudiantes, fechas e imagenes del libro fisico
    private function procesarEstudiantesFormulario(mixed $rawEstudiantes, ?string &$errorMsg = null): array|false {
        if (!is_array($rawEstudiantes) || empty($rawEstudiantes)) {
            $errorMsg = "El acta debe contener al menos un estudiante en la tabla.";
            return false;
        }

        $procesados = [];
        $filaNum = 0;

        foreach ($rawEstudiantes as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $nombreRaw = trim((string)($fila['apellidos_nombres'] ?? ''));
            $nombre = $this->capitalizarPalabrasNombre($nombreRaw);
            $cedula = trim((string)($fila['cedula'] ?? ''));
            $dia = trim((string)($fila['dia'] ?? ''));
            $mes = trim((string)($fila['mes'] ?? ''));
            $anio = trim((string)($fila['anio'] ?? ''));
            $literal = trim((string)($fila['literal_nota'] ?? ''));
            $obs = trim((string)($fila['observacion'] ?? ''));

            if ($nombre === '' && $cedula === '' && $dia === '' && $mes === '' && $anio === '' && $literal === '' && $obs === '') {
                continue;
            }

            $filaNum++;

            if ($nombre === '') {
                $errorMsg = "En la fila #{$filaNum}, el campo 'Apellidos y Nombres' es obligatorio.";
                return false;
            }

            if ($literal === '') {
                $errorMsg = "En la fila #{$filaNum} ({$nombre}), la calificación 'Literal' es obligatoria.";
                return false;
            }

            $anioDigitos = (string)preg_replace('/[^0-9]/', '', $anio);
            $anioFormato = (strlen($anioDigitos) === 4) ? 4 : 2;

            $fechaIso = $this->construirFechaIso($dia, $mes, $anio);
            if ($fechaIso === false) {
                $errorMsg = "En la fila #{$filaNum} ({$nombre}), la Fecha de Nacimiento (Día/Mes/Año) no es válida.";
                return false;
            }

            $procesados[] = [
                'apellidos_nombres' => $nombre,
                'cedula'            => $cedula !== '' ? $cedula : null,
                'fecha_nacimiento'  => $fechaIso,
                'anio_formato'      => $anioFormato,
                'literal_nota'      => $literal,
                'observacion'       => $obs !== '' ? $obs : null
            ];
        }

        if (empty($procesados)) {
            $errorMsg = "Debe completar al menos una fila con los datos del estudiante.";
            return false;
        }

        return $procesados;
    }

    private function validarYFormatearAnioEscolar(string $anioRaw): string|false {
        $limpio = trim($anioRaw);
        if ($limpio === '') {
            return false;
        }

        if (preg_match('/^(\d{4})$/', $limpio, $m)) {
            $y1 = (int)$m[1];
            return ($y1 >= 1900 && $y1 <= 2100) ? ($y1 . '-' . ($y1 + 1)) : false;
        }

        $normalizado = (string)preg_replace('/\s*[\-\/]\s*/', '-', $limpio);
        if (!preg_match('/^(\d{4})-(\d{4})$/', $normalizado, $matches)) {
            return false;
        }

        $anioInicio = (int)$matches[1];
        $anioFin = (int)$matches[2];

        if ($anioInicio < 1900 || $anioInicio > 2100 || $anioFin !== ($anioInicio + 1)) {
            return false;
        }

        return sprintf('%04d-%04d', $anioInicio, $anioFin);
    }

    private function capitalizarPalabrasNombre(string $nombre): string {
        $limpio = trim((string)preg_replace('/\s+/', ' ', $nombre));
        if ($limpio === '') {
            return '';
        }
        return (string)preg_replace_callback('/(^|[\s,.\-\'])(\p{L})/u', function (array $m): string {
            return $m[1] . mb_strtoupper($m[2], 'UTF-8');
        }, $limpio);
    }

    private function construirFechaIso(string $diaRaw, string $mesRaw, string $anioRaw): string|false {
        $d = filter_var($diaRaw, FILTER_VALIDATE_INT);
        $m = filter_var($mesRaw, FILTER_VALIDATE_INT);
        $yStr = (string)preg_replace('/[^0-9]/', '', $anioRaw);

        if (!$d || !$m || $yStr === '') {
            return false;
        }

        $y = (int)$yStr;
        if (strlen($yStr) <= 2) {
            $anioActualCorto = (int)date('y');
            $y = ($y <= $anioActualCorto) ? (2000 + $y) : (1900 + $y);
        } elseif (strlen($yStr) !== 4) {
            return false;
        }

        if ($y < 1900 || $y > (int)date('Y') || !checkdate($m, $d, $y)) {
            return false;
        }

        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }

    private function normalizarFechaEntrada(string $fechaStr): string|false {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $fechaStr, $m)) {
            return $this->construirFechaIso($m[3], $m[2], $m[1]);
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/', $fechaStr, $m)) {
            return $this->construirFechaIso($m[1], $m[2], $m[3]);
        }
        return false;
    }

    private function handleMultipleImageUploads(string $fieldKey, int $maxFiles, ?string &$errorMsg = null): array|false {
        if (!isset($_FILES[$fieldKey]) || !is_array($_FILES[$fieldKey]['name'])) {
            return [];
        }

        $names    = $_FILES[$fieldKey]['name'];
        $tmpNames = $_FILES[$fieldKey]['tmp_name'];
        $errors   = $_FILES[$fieldKey]['error'];
        $sizes    = $_FILES[$fieldKey]['size'];

        $archivosReales = [];
        foreach ($names as $idx => $name) {
            if ($errors[$idx] === UPLOAD_ERR_NO_FILE || trim((string)$name) === '') {
                continue;
            }
            $archivosReales[] = $idx;
        }

        if (empty($archivosReales)) {
            return [];
        }

        if (count($archivosReales) > $maxFiles) {
            $errorMsg = "Solo puede adjuntar un máximo de 4 imágenes del libro físico por acta.";
            return false;
        }

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];

        $subidas = [];

        foreach ($archivosReales as $idx) {
            if ($errors[$idx] !== UPLOAD_ERR_OK) {
                $errorMsg = "Error al subir la imagen '" . htmlspecialchars((string)$names[$idx]) . "'.";
                return false;
            }

            if ($sizes[$idx] > 8 * 1024 * 1024) {
                $errorMsg = "La imagen '" . htmlspecialchars((string)$names[$idx]) . "' supera el límite de 8MB.";
                return false;
            }

            $mime = ImageOptimizer::detectMimeType($tmpNames[$idx], (string)($_FILES[$fieldKey]['type'][$idx] ?? ''));
            if (!isset($allowedMimes[$mime])) {
                $errorMsg = "Formato no permitido en '" . htmlspecialchars((string)$names[$idx]) . "'. Use JPG, PNG, WEBP o GIF.";
                return false;
            }

            $extension = $allowedMimes[$mime];
            $nombreArchivo = 'acta_' . bin2hex(random_bytes(14)) . '.' . $extension;
            $rutaDestino = $this->uploadDir . $nombreArchivo;

            if (!ImageOptimizer::processUpload($tmpNames[$idx], $rutaDestino, $mime, $this->uploadDir)) {
                $errorMsg = "No fue posible almacenar la imagen en el servidor.";
                return false;
            }

            $subidas[] = [
                'archivo'         => $nombreArchivo,
                'nombre_original' => Security::sanitize(basename((string)$names[$idx])),
                'mime_type'       => $mime
            ];
        }

        return $subidas;
    }
}
