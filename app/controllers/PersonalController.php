<?php
// controlador para el modulo de personal y manejo de expedientes

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/Personal.php';

class PersonalController extends Controller {
    private Personal $personalModel;
    private string $uploadDir;

    public function __construct() {
        $this->checkAuth();
        $this->personalModel = new Personal();
        $this->uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index(): void {
        $lista = $this->personalModel->getAll();
        $selected = null;
        $expedientes = [];

        if (!empty($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id) {
                $selected = $this->personalModel->findById($id) ?: null;
                if ($selected) {
                    $expedientes = $this->personalModel->getExpedientes($id);
                }
            }
        }

        $this->view('personal', [
            'lista'       => $lista,
            'selected'    => $selected,
            'expedientes' => $expedientes
        ]);
    }

    // funcion auxiliar para subir fotos o pdfs de los justificativos
    private function handleUpload(string $fileKey, array $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf']): array|false|null {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK || $_FILES[$fileKey]['size'] > 8 * 1024 * 1024) {
            return false;
        }

        $mime = ImageOptimizer::detectMimeType($_FILES[$fileKey]['tmp_name'], (string)($_FILES[$fileKey]['type'] ?? ''));
        if (!in_array($mime, $allowedMimes, true)) {
            return false;
        }

        $extMap = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'image/gif'       => 'gif',
            'application/pdf' => 'pdf'
        ];

        $safeFilename = bin2hex(random_bytes(16)) . '.' . $extMap[$mime];
        $destination = $this->uploadDir . $safeFilename;

        if (ImageOptimizer::processUpload($_FILES[$fileKey]['tmp_name'], $destination, $mime, $this->uploadDir)) {
            return [
                'filename' => $safeFilename,
                'mime'     => $mime
            ];
        }

        return false;
    }

    // recoge y limpia los campos del formulario de personal
    private function recogerDatosPersonal(): array {
        $nacionalidad = strtoupper(trim((string)($_POST['nacionalidad'] ?? 'V')));
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $nacionalidad = 'V';
        }
        $cedula = trim((string)($_POST['cedula'] ?? ''));

        if (preg_match('/^([VEve])[- ]?([0-9]+)$/', $cedula, $matches)) {
            $nacionalidad = strtoupper($matches[1]);
            $cedula = $matches[2];
        }

        $padece = ($_POST['padece_enfermedad'] ?? 'No') === 'Si' ? 'Si' : 'No';
        $detalle = ($padece === 'Si') ? mb_substr(trim((string)($_POST['detalle_enfermedad'] ?? '')), 0, 255, 'UTF-8') : null;

        return [
            'nombres'            => mb_substr(trim((string)($_POST['nombres'] ?? '')), 0, 100, 'UTF-8'),
            'apellidos'          => mb_substr(trim((string)($_POST['apellidos'] ?? '')), 0, 100, 'UTF-8'),
            'nacionalidad'       => $nacionalidad,
            'cedula'             => $cedula,
            'direccion'          => mb_substr(trim((string)($_POST['direccion'] ?? '')), 0, 300, 'UTF-8'),
            'fecha_nacimiento'   => trim((string)($_POST['fecha_nacimiento'] ?? '')),
            'telefono'           => mb_substr(trim((string)($_POST['telefono'] ?? '')), 0, 30, 'UTF-8'),
            'correo'             => mb_substr(trim((string)($_POST['correo'] ?? '')), 0, 120, 'UTF-8'),
            'estado_civil'       => trim((string)($_POST['estado_civil'] ?? '')),
            'sexo'               => trim((string)($_POST['sexo'] ?? '')),
            'cargo'              => trim((string)($_POST['cargo'] ?? '')),
            'padece_enfermedad'  => $padece,
            'detalle_enfermedad' => $detalle
        ];
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
        $data = $this->recogerDatosPersonal();

        if (!$data['nombres'] || !$data['apellidos'] || !$data['cedula'] || !$data['direccion'] || !$data['fecha_nacimiento'] || !$data['estado_civil'] || !$data['sexo'] || !$data['cargo']) {
            $_SESSION['flash_error'] = "Todos los campos obligatorios (*) deben ser completados.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        if (!ctype_digit($data['cedula'])) {
            $_SESSION['flash_error'] = "La cédula debe contener únicamente números (sin letras, puntos o guiones).";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        // verificamos que la cedula no este repedita
        if ($this->personalModel->findByCedula($data['cedula'], $data['nacionalidad'])) {
            $_SESSION['flash_error'] = "La cédula {$data['nacionalidad']}-{$data['cedula']} ya se encuentra registrada para otro integrante del personal.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        $fotoUpload = $this->handleUpload('foto', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
        if ($fotoUpload === false) {
            $_SESSION['flash_error'] = "Formato o tamaño de foto de perfil inválido (Solo JPG, PNG, WEBP o GIF, máx. 8MB).";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }
        $data['foto'] = $fotoUpload ? $fotoUpload['filename'] : null;

        try {
            $id = $this->personalModel->create($data);
            $this->logAction("Registró al personal: {$data['nombres']} {$data['apellidos']} (Cédula: {$data['nacionalidad']}-{$data['cedula']}).");
            $_SESSION['flash_success'] = "Personal registrado exitosamente.";
            header("Location: " . BASE_URL . "/personal?id=" . $id);
            exit;
        } catch (Exception $e) {
            error_log("Error al registrar personal: " . $e->getMessage());
            $_SESSION['flash_error'] = "Error al registrar el personal.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = "Identificador de personal no válido.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        $redirectTo = ($_POST['redirect_to'] ?? 'perfil') === 'lista'
            ? BASE_URL . "/personal"
            : BASE_URL . "/personal?id=" . $id;

        $current = $this->personalModel->findById($id);
        if (!$current) {
            $_SESSION['flash_error'] = "Personal no encontrado.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        if (($current['activo'] ?? 1) == 0) {
            $_SESSION['flash_error'] = "No se puede modificar la información de un integrante deshabilitado. Debe habilitarlo primero.";
            header("Location: " . $redirectTo);
            exit;
        }

        $data = $this->recogerDatosPersonal();

        if (!$data['nombres'] || !$data['apellidos'] || !$data['cedula'] || !$data['direccion'] || !$data['fecha_nacimiento'] || !$data['estado_civil'] || !$data['sexo'] || !$data['cargo']) {
            $_SESSION['flash_error'] = "Todos los campos obligatorios (*) deben ser completados.";
            header("Location: " . $redirectTo);
            exit;
        }

        if (!ctype_digit($data['cedula'])) {
            $_SESSION['flash_error'] = "La cédula debe contener únicamente números.";
            header("Location: " . $redirectTo);
            exit;
        }

        $existing = $this->personalModel->findByCedula($data['cedula'], $data['nacionalidad']);
        if ($existing && (int)$existing['id'] !== $id) {
            $_SESSION['flash_error'] = "La cédula {$data['nacionalidad']}-{$data['cedula']} ya pertenece a otro integrante del personal.";
            header("Location: " . $redirectTo);
            exit;
        }

        $fotoUpload = $this->handleUpload('foto', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
        if ($fotoUpload === false) {
            $_SESSION['flash_error'] = "Archivo de foto inválido (Solo JPG, PNG, WEBP o GIF, máx. 8MB).";
            header("Location: " . $redirectTo);
            exit;
        }

        // si subio foto nueva borramos la anteriror del disco
        if ($fotoUpload) {
            if (!empty($current['foto'])) {
                $oldFile = $this->uploadDir . basename((string)$current['foto']);
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
                ImageOptimizer::deleteWebp($current['foto'], $this->uploadDir);
            }
            $data['foto'] = $fotoUpload['filename'];
        }

        try {
            $this->personalModel->update($id, $data);
            $this->logAction("Actualizó los datos del personal: {$data['nombres']} {$data['apellidos']} (Cédula: {$data['nacionalidad']}-{$data['cedula']}).");
            $_SESSION['flash_success'] = "Información actualizada exitosamente.";
        } catch (Exception $e) {
            error_log("Error al actualizar personal: " . $e->getMessage());
            $_SESSION['flash_error'] = "Error al actualizar la información.";
        }

        header("Location: " . $redirectTo);
        exit;
    }

    public function deshabilitar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        $personal = $id > 0 ? $this->personalModel->findById($id) : false;
        if ($personal) {
            $nombreCompleto = $personal['nombres'] . ' ' . $personal['apellidos'];
            $this->personalModel->setActivo($id, 0);
            $this->logAction("Deshabilitó al personal: {$nombreCompleto} (ID: {$id}).");
            $_SESSION['flash_success'] = "Personal deshabilitado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "El registro no existe.";
        }

        header("Location: " . BASE_URL . "/personal");
        exit;
    }

    public function habilitar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id'] ?? 0);

        $personal = $id > 0 ? $this->personalModel->findById($id) : false;
        if ($personal) {
            $nombreCompleto = $personal['nombres'] . ' ' . $personal['apellidos'];
            $this->personalModel->setActivo($id, 1);
            $this->logAction("Habilitó / Reactivó al personal: {$nombreCompleto} (ID: {$id}).");
            $_SESSION['flash_success'] = "Personal reactivado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "El registro no existe.";
        }

        $redirectTo = ($_POST['redirect_to'] ?? 'lista') === 'perfil'
            ? BASE_URL . "/personal?id=" . $id
            : BASE_URL . "/personal";
        header("Location: " . $redirectTo);
        exit;
    }

    // acciones para los registros del expediente
    public function storeExpediente(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $personalId = (int)($_POST['personal_id'] ?? 0);
        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $fechaVencimiento = !empty($_POST['fecha_vencimiento']) ? trim((string)$_POST['fecha_vencimiento']) : null;

        if (!$personalId || !$titulo || !$descripcion) {
            $_SESSION['flash_error'] = "El título y la descripción del registro son obligatorios.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        if (mb_strlen($titulo, 'UTF-8') > 30 || mb_strlen($descripcion, 'UTF-8') > 100) {
            $_SESSION['flash_error'] = "El título (máx. 30) o la descripción (máx. 100) exceden el límite permitido.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $personal = $this->personalModel->findById($personalId);
        if (!$personal) {
            $_SESSION['flash_error'] = "Personal no encontrado.";
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        if (($personal['activo'] ?? 1) == 0) {
            $_SESSION['flash_error'] = "No se pueden añadir registros al expediente de un integrante deshabilitado.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $archivoUpload = $this->handleUpload('archivo_adjunto');
        if ($archivoUpload === null) {
            $_SESSION['flash_error'] = "Es obligatorio adjuntar una imagen o documento para este registro.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }
        if ($archivoUpload === false) {
            $_SESSION['flash_error'] = "Archivo adjunto inválido. Solo se permiten imágenes (JPG, PNG, WEBP, GIF) o documentos PDF (Máx. 8MB).";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $this->personalModel->addExpediente(
            $personalId,
            $titulo,
            $descripcion,
            $archivoUpload['filename'],
            $archivoUpload['mime'],
            $fechaVencimiento
        );
        $this->logAction("Añadió un nuevo registro al expediente de {$personal['nombres']} {$personal['apellidos']}: '{$titulo}'.");
        $_SESSION['flash_success'] = "Registro agregado al expediente exitosamente.";

        header("Location: " . BASE_URL . "/personal?id=" . $personalId);
        exit;
    }

    public function updateExpediente(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $expId = (int)($_POST['exp_id'] ?? 0);
        $personalId = (int)($_POST['personal_id'] ?? 0);
        $titulo = trim((string)($_POST['titulo'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $fechaVencimiento = !empty($_POST['fecha_vencimiento']) ? trim((string)$_POST['fecha_vencimiento']) : null;

        if (!$expId || !$personalId || !$titulo || !$descripcion) {
            $_SESSION['flash_error'] = "El título y la descripción son obligatorios para actualizar el registro.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        if (mb_strlen($titulo, 'UTF-8') > 30 || mb_strlen($descripcion, 'UTF-8') > 100) {
            $_SESSION['flash_error'] = "El título (máx. 30) o la descripción (máx. 100) exceden el límite permitido.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $personal = $this->personalModel->findById($personalId);
        if ($personal && ($personal['activo'] ?? 1) == 0) {
            $_SESSION['flash_error'] = "No se puede modificar el expediente de un integrante deshabilitado.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $exp = $this->personalModel->getExpedienteById($expId);
        if (!$exp) {
            $_SESSION['flash_error'] = "Registro de expediente no encontrado.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $archivoUpload = $this->handleUpload('archivo_adjunto');
        if ($archivoUpload === false) {
            $_SESSION['flash_error'] = "Archivo adjunto inválido. Solo se permiten imágenes (JPG, PNG, WEBP, GIF) o documentos PDF (Máx. 8MB).";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        if ($archivoUpload && !empty($exp['archivo_adjunto'])) {
            $oldFile = $this->uploadDir . basename((string)$exp['archivo_adjunto']);
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
            ImageOptimizer::deleteWebp($exp['archivo_adjunto'], $this->uploadDir);
        }

        $archivoPath = $archivoUpload ? $archivoUpload['filename'] : null;
        $mimeType = $archivoUpload ? $archivoUpload['mime'] : null;

        $this->personalModel->updateExpediente($expId, $titulo, $descripcion, $archivoPath, $mimeType, $fechaVencimiento);
        $this->logAction("Modificó el registro de expediente #{$expId}: '{$titulo}'.");
        $_SESSION['flash_success'] = "Registro del expediente actualizado exitosamente.";

        header("Location: " . BASE_URL . "/personal?id=" . $personalId);
        exit;
    }

    public function deleteExpediente(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/personal");
            exit;
        }

        Security::verifyCSRFToken($_POST['csrf_token'] ?? '');

        $expId = (int)($_POST['exp_id'] ?? 0);
        $personalId = (int)($_POST['personal_id'] ?? 0);

        if (!$expId || !$personalId) {
            $_SESSION['flash_error'] = "Parámetros no válidos para eliminar.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $personal = $this->personalModel->findById($personalId);
        if ($personal && ($personal['activo'] ?? 1) == 0) {
            $_SESSION['flash_error'] = "No se pueden eliminar registros del expediente de un integrante deshabilitado.";
            header("Location: " . BASE_URL . "/personal?id=" . $personalId);
            exit;
        }

        $exp = $this->personalModel->getExpedienteById($expId);
        if ($exp) {
            if (!empty($exp['archivo_adjunto'])) {
                $file = $this->uploadDir . basename((string)$exp['archivo_adjunto']);
                if (file_exists($file)) {
                    @unlink($file);
                }
                ImageOptimizer::deleteWebp($exp['archivo_adjunto'], $this->uploadDir);
            }
            $this->personalModel->deleteExpediente($expId);
            $this->logAction("Eliminó el registro de expediente '{$exp['titulo']}' (ID #{$expId}).");
            $_SESSION['flash_success'] = "El registro '{$exp['titulo']}' fue eliminado del expediente.";
        }

        header("Location: " . BASE_URL . "/personal?id=" . $personalId);
        exit;
    }

    // sirve las fotos de perfil y los archivos adjuntos para ver o descargar
    public function downloadFile(): void {
        $expId = filter_var($_GET['exp_id'] ?? 0, FILTER_VALIDATE_INT);
        $fotoId = filter_var($_GET['foto_id'] ?? 0, FILTER_VALIDATE_INT);
        $forceDownload = isset($_GET['download']) && $_GET['download'] == '1';

        $file = null;
        $mime = 'application/octet-stream';
        $customDownloadName = null;

        if ($expId) {
            $exp = $this->personalModel->getExpedienteById($expId);
            if ($exp && !empty($exp['archivo_adjunto'])) {
                $file = $this->uploadDir . basename((string)$exp['archivo_adjunto']);
                $mime = $exp['mime_type'] ?: ImageOptimizer::detectMimeType($file);
                $ext = pathinfo((string)$exp['archivo_adjunto'], PATHINFO_EXTENSION);
                $rawTitle = trim((string)($exp['titulo'] ?? 'Documento'));
                $cleanTitle = (string)preg_replace('/[^\p{L}\p{N}_\-\. ]/u', '', $rawTitle);
                $cleanTitle = trim((string)preg_replace('/\s+/', ' ', $cleanTitle)) ?: ('Documento_' . $expId);
                $customDownloadName = $cleanTitle . ($ext ? '.' . $ext : '');
            }
        } elseif ($fotoId) {
            $persona = $this->personalModel->findById($fotoId);
            if ($persona && !empty($persona['foto'])) {
                $file = $this->uploadDir . basename((string)$persona['foto']);
                $mime = ImageOptimizer::detectMimeType($file, 'image/jpeg');
                $customDownloadName = basename($file);
            }
        }

        if ($file && file_exists($file)) {
            $formatParam = trim((string)($_GET['format'] ?? ''));
            $preferWebp = (!$forceDownload && $formatParam !== 'original');
            $filenameHeader = $customDownloadName ?: basename($file);
            ImageOptimizer::streamFile($file, $mime, $filenameHeader, $forceDownload, $preferWebp, $this->uploadDir);
        }

        http_response_code(404);
        die("El archivo solicitado no existe o fue removido.");
    }

    public function printExpediente(): void {
        $expId = filter_var($_GET['exp_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$expId) {
            http_response_code(404);
            die("Registro de expediente no especificado.");
        }

        $exp = $this->personalModel->getExpedienteById($expId);
        if (!$exp) {
            http_response_code(404);
            die("El registro de expediente solicitado no existe.");
        }

        $personal = $this->personalModel->findById((int)$exp['personal_id']);
        if (!$personal) {
            http_response_code(404);
            die("El personal asociado a este expediente no existe.");
        }

        require_once __DIR__ . "/../views/expediente_print.php";
        exit;
    }
}
