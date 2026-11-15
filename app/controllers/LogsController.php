<?php
/* Controlador de la vista de auditoria (logs) */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/AuditLog.php';

class LogsController extends Controller {

    public function index(): void {
        $this->checkAuth();

        if (!in_array($_SESSION['user_rol'] ?? '', ['admin', 'admin_mayor'], true)) {
            header("Location: " . BASE_URL . "/panel");
            exit;
        }

        $periodo = strtolower(trim((string)($_GET['periodo'] ?? 'hoy')));
        if (!in_array($periodo, ['hoy', 'semanal', 'mensual', 'todos'], true)) {
            $periodo = 'hoy';
        }

        $orden = strtolower(trim((string)($_GET['orden'] ?? 'desc')));
        if (!in_array($orden, ['asc', 'desc'], true)) {
            $orden = 'desc';
        }

        $logModel = new AuditLog();
        $logs = $logModel->getFiltered($periodo, $orden);

        /* Respuesta en JSON cuando se filtra sin recargar la pagina */
        if (isset($_GET['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'      => true,
                'periodo' => $periodo,
                'orden'   => $orden,
                'logs'    => array_map(function (array $l): array {
                    return [
                        'id'             => $l['id'] ?? 0,
                        'fecha'          => $l['fecha'] ?? 'N/A',
                        'nombres'        => $l['usuario_nombre'] ?? 'Sistema',
                        'usuario_nombre' => $l['usuario_nombre'] ?? 'Sistema',
                        'accion'         => $l['accion'] ?? '',
                        'ip'             => $l['ip_origen'] ?? 'Desconocida'
                    ];
                }, $logs)
            ]);
            exit;
        }

        $this->view("logs", [
            'logs'    => $logs,
            'periodo' => $periodo,
            'orden'   => $orden
        ]);
    }
}
