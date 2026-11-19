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
        $fechaActual = date('Y-m-d H:i:s');

        try {
            $stmt = $this->db->prepare("INSERT INTO logs (usuario_id, usuario_nombre, accion, ip_origen, fecha) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$userId, $userName, $accion, $ip, $fechaActual]);
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

        $fechaCorte = match (strtolower($periodo)) {
            'hoy'     => date('Y-m-d H:i:s', strtotime('-24 hours')),
            'semanal' => date('Y-m-d H:i:s', strtotime('-7 days')),
            'mensual' => date('Y-m-d H:i:s', strtotime('-30 days')),
            default   => null
        };

        try {
            if ($fechaCorte !== null) {
                $stmt = $this->db->prepare("SELECT * FROM logs WHERE fecha >= :corte ORDER BY fecha {$dir}, id {$dir}");
                $stmt->execute([':corte' => $fechaCorte]);
            } else {
                $stmt = $this->db->query("SELECT * FROM logs ORDER BY fecha {$dir}, id {$dir}");
            }
            $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
            foreach ($rows as &$r) {
                $rawFecha = (string)($r['fecha'] ?? '');
                $ts = $rawFecha !== '' ? strtotime($rawFecha) : false;
                $r['fecha'] = $ts ? date('d-m-Y H:i:s', $ts) : ($rawFecha ?: 'N/A');
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
