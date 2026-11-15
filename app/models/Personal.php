<?php
// modelo para consultas de personal y sus expedientes

class Personal {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // listar todo el personal o filtrar por estado
    public function getAll(?int $activo = null): array {
        try {
            if ($activo !== null) {
                $stmt = $this->db->prepare("SELECT * FROM personal WHERE activo = ? ORDER BY creado_en DESC, id DESC");
                $stmt->execute([$activo]);
                return $stmt->fetchAll() ?: [];
            }
            $stmt = $this->db->query("SELECT * FROM personal ORDER BY creado_en DESC, id DESC");
            return $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (PDOException $e) {
            error_log("Error en Personal::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM personal WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Personal::findById: " . $e->getMessage());
            return false;
        }
    }

    // buscar por cedula y nacionalidad para validar duplicados
    public function findByCedula(string|int $cedula, ?string $nacionalidad = null): array|false {
        if ($nacionalidad !== null && $nacionalidad !== '') {
            $stmt = $this->db->prepare("SELECT * FROM personal WHERE cedula = ? AND nacionalidad = ?");
            $stmt->execute([$cedula, $nacionalidad]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM personal WHERE cedula = ?");
            $stmt->execute([$cedula]);
        }
        return $stmt->fetch();
    }

    // guardar nuevo trabajador en la bd
    public function create(array $data): int {
        $sql = "INSERT INTO personal (
                    nombres, apellidos, nacionalidad, cedula, direccion, fecha_nacimiento,
                    telefono, correo, estado_civil, sexo, cargo, padece_enfermedad, detalle_enfermedad, foto, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['nombres'],
            $data['apellidos'],
            $data['nacionalidad'] ?? 'V',
            $data['cedula'],
            $data['direccion'],
            $data['fecha_nacimiento'],
            $data['telefono'],
            $data['correo'],
            $data['estado_civil'],
            $data['sexo'],
            $data['cargo'],
            $data['padece_enfermedad'],
            $data['detalle_enfermedad'],
            $data['foto']
        ]);
        return (int)$this->db->lastInsertId();
    }

    // actualizar datos personales (actualiza la foto solo si se subio una nueva)
    public function update(int $id, array $data): bool {
        $campos = [
            'nombres = ?', 'apellidos = ?', 'nacionalidad = ?', 'cedula = ?',
            'direccion = ?', 'fecha_nacimiento = ?', 'telefono = ?', 'correo = ?',
            'estado_civil = ?', 'sexo = ?', 'cargo = ?', 'padece_enfermedad = ?', 'detalle_enfermedad = ?'
        ];
        $params = [
            $data['nombres'], $data['apellidos'], $data['nacionalidad'] ?? 'V', $data['cedula'],
            $data['direccion'], $data['fecha_nacimiento'], $data['telefono'], $data['correo'],
            $data['estado_civil'], $data['sexo'], $data['cargo'], $data['padece_enfermedad'],
            $data['detalle_enfermedad']
        ];

        if (!empty($data['foto'])) {
            $campos[] = 'foto = ?';
            $params[] = $data['foto'];
        }

        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE personal SET " . implode(', ', $campos) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function setActivo(int $id, int $activo): bool {
        $stmt = $this->db->prepare("UPDATE personal SET activo = ? WHERE id = ?");
        return $stmt->execute([$activo, $id]);
    }

    // consultas para los documentos del expediente
    public function getExpedientes(int $personalId): array {
        $stmt = $this->db->prepare("SELECT * FROM expediente_personal WHERE personal_id = ? ORDER BY creado_en DESC, id DESC");
        $stmt->execute([$personalId]);
        return $stmt->fetchAll() ?: [];
    }

    public function getExpedienteById(int $expId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM expediente_personal WHERE id = ?");
        $stmt->execute([$expId]);
        return $stmt->fetch();
    }

    public function addExpediente(int $personalId, string $titulo, string $descripcion, ?string $archivoPath, ?string $mimeType, ?string $fechaVenc = null): bool {
        $stmt = $this->db->prepare("INSERT INTO expediente_personal (personal_id, titulo, descripcion, archivo_adjunto, mime_type, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$personalId, $titulo, $descripcion, $archivoPath, $mimeType, $fechaVenc ?: null]);
    }

    public function updateExpediente(int $id, string $titulo, string $descripcion, ?string $archivoPath = null, ?string $mimeType = null, ?string $fechaVenc = null): bool {
        if ($archivoPath) {
            $stmt = $this->db->prepare("UPDATE expediente_personal SET titulo = ?, descripcion = ?, archivo_adjunto = ?, mime_type = ?, fecha_vencimiento = ? WHERE id = ?");
            return $stmt->execute([$titulo, $descripcion, $archivoPath, $mimeType, $fechaVenc ?: null, $id]);
        }

        $stmt = $this->db->prepare("UPDATE expediente_personal SET titulo = ?, descripcion = ?, fecha_vencimiento = ? WHERE id = ?");
        return $stmt->execute([$titulo, $descripcion, $fechaVenc ?: null, $id]);
    }

    public function deleteExpediente(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM expediente_personal WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
