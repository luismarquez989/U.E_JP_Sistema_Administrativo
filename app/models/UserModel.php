<?php
/* Modelo para el control de usuarios y roles del sistema */

require_once __DIR__ . '/../../config/Database.php';

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /* Trae todos los usuarios y calcula los segundos desde su ultima conexion */
    public function getAll(): array {
        $sql = "SELECT id, nacionalidad, cedula, nombres_completos, rol, activo, ultima_actividad
                FROM usuarios ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        $rows = $stmt ? ($stmt->fetchAll() ?: []) : [];

        $ahoraTs = time();
        $idsExpirados = [];

        foreach ($rows as &$row) {
            $ultima = $row['ultima_actividad'] ?? null;
            if (!empty($ultima)) {
                $ultimaTs = strtotime((string)$ultima);
                if ($ultimaTs !== false) {
                    $diff = $ahoraTs - $ultimaTs;
                    if ($diff >= -5 && $diff <= 45) {
                        $row['segundos_inactivo'] = max(0, $diff);
                    } else {
                        $row['segundos_inactivo'] = null;
                        $row['ultima_actividad'] = null;
                        $idsExpirados[] = (int)$row['id'];
                    }
                } else {
                    $row['segundos_inactivo'] = null;
                }
            } else {
                $row['segundos_inactivo'] = null;
            }
        }
        unset($row);

        // Limpiar en base de datos las sesiones que ya expiraron por inactividad
        if (!empty($idsExpirados)) {
            try {
                $placeholders = implode(',', array_fill(0, count($idsExpirados), '?'));
                $cleanStmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = NULL WHERE id IN ({$placeholders})");
                $cleanStmt->execute($idsExpirados);
            } catch (PDOException) {
            }
        }

        return $rows;
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : false;
    }

    public function findByCedula(string|int $cedula, ?string $nacionalidad = null): array|false {
        if ($nacionalidad !== null && $nacionalidad !== '') {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE cedula = ? AND nacionalidad = ?");
            $stmt->execute([$cedula, $nacionalidad]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE cedula = ?");
            $stmt->execute([$cedula]);
        }
        $row = $stmt->fetch();
        return is_array($row) ? $row : false;
    }

    public function create(array $data): int {
        $ahora = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO usuarios (nacionalidad, cedula, nombres_completos, password, rol, activo, debe_cambiar_clave, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nacionalidad'] ?? 'V',
            $data['cedula'],
            $data['nombres_completos'],
            $data['password'],
            $data['rol'] ?? 'regular',
            $data['activo'] ?? 1,
            $data['debe_cambiar_clave'] ?? 0,
            $ahora
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        if (!empty($data['password'])) {
            $stmt = $this->db->prepare("UPDATE usuarios SET nacionalidad = ?, cedula = ?, nombres_completos = ?, password = ?, rol = ? WHERE id = ?");
            return $stmt->execute([
                $data['nacionalidad'] ?? 'V',
                $data['cedula'],
                $data['nombres_completos'],
                $data['password'],
                $data['rol'] ?? 'regular',
                $id
            ]);
        }

        $stmt = $this->db->prepare("UPDATE usuarios SET nacionalidad = ?, cedula = ?, nombres_completos = ?, rol = ? WHERE id = ?");
        return $stmt->execute([
            $data['nacionalidad'] ?? 'V',
            $data['cedula'],
            $data['nombres_completos'],
            $data['rol'] ?? 'regular',
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateLastActivity(int $userId): void {
        $ahora = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = ? WHERE id = ?");
        $stmt->execute([$ahora, $userId]);
    }

    public function clearLastActivity(int $userId): void {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
