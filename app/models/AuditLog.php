<?php
/* Modelo para guardar y consultar los logs de auditoria */

class AuditLog {
    private ?PDO $db = null;

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = Database::getConnection();
        }
    }

    public function insert(array $data): bool {
        if (!$this->db) {
            return false;
        }

        $ip = $data['ip'] ?? $data['ip_origen'] ?? 'Desconocida';
        $userName = mb_substr(trim((string)($data['usuario_nombre'] ?? 'Sistema')), 0, 150, 'UTF-8');
        $accion = (string)($data['accion'] ?? '');
        $userId = $data['usuario_id'] ?? null;

        try {
            $stmt = $this->db->prepare("INSERT INTO logs (usuario_id, usuario_nombre, accion, ip_origen) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$userId, $userName, $accion, $ip]);
        } catch (PDOException $e) {
            error_log("Error en AuditLog::insert: " . $e->getMessage());
            return false;
        }
    }

    /* Filtrar bitacora segun el perido y orden seleccionado */
    public function getFiltered(string $periodo = 'hoy', string $orden = 'desc'): array {
        if (!$this->db) {
            return [];
        }

        $dir = (strtolower($orden) === 'asc') ? 'ASC' : 'DESC';

        $whereSql = match (strtolower($periodo)) {
            'hoy'     => "WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            'semanal' => "WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'mensual' => "WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            default   => ""
        };

        try {
            $stmt = $this->db->query("SELECT * FROM logs {$whereSql} ORDER BY fecha {$dir}, id {$dir}");
            $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
            foreach ($rows as &$r) {
                $r['ip'] = $r['ip_origen'] ?? 'Desconocida';
                $r['nombres'] = $r['usuario_nombre'] ?? 'Sistema';
            }
            return $rows;
        } catch (Exception $e) {
            error_log("Error en AuditLog::getFiltered: " . $e->getMessage());
            return [];
        }
    }
}
