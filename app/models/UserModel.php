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
        $sql = "SELECT id, nacionalidad, cedula, nombres_completos, rol, activo, ultima_actividad,
                (TIMESTAMPDIFF(SECOND, ultima_actividad, NOW())) AS segundos_inactivo
                FROM usuarios ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt ? ($stmt->fetchAll() ?: []) : [];
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
        $stmt = $this->db->prepare("INSERT INTO usuarios (nacionalidad, cedula, nombres_completos, password, rol, activo, debe_cambiar_clave) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nacionalidad'] ?? 'V',
            $data['cedula'],
            $data['nombres_completos'],
            $data['password'],
            $data['rol'] ?? 'regular',
            $data['activo'] ?? 1,
            $data['debe_cambiar_clave'] ?? 0
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
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function clearLastActivity(int $userId): void {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
