<?php
/* Vista de bitacora de auditoria (logs) */
$logs = $logs ?? [];
$periodo = $periodo ?? 'hoy';
$orden = $orden ?? 'desc';
?>
<div class="card card-custom p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h2 class="fw-bold mb-0 fs-4 fs-md-3">
            <i class="bi bi-journal-text text-info me-2"></i>Registro de Auditoría (Logs Inalterables)
        </h2>

        <form id="formFiltrosLogs" method="GET" action="<?= BASE_URL ?>/auditoria" autocomplete="off" class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 w-100 w-md-auto">
            <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0">
                <label for="filtroPeriodo" class="small fw-semibold text-secondary me-2 text-nowrap mb-0">
                    <i class="bi bi-calendar3 text-primary me-1"></i>Período:
                </label>
                <select id="filtroPeriodo" name="periodo" class="form-select form-select-sm audit-select-periodo flex-grow-1">
                    <option value="hoy" <?= $periodo === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                    <option value="semanal" <?= $periodo === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                    <option value="mensual" <?= $periodo === 'mensual' ? 'selected' : '' ?>>Mensual</option>
                    <option value="todos" <?= $periodo === 'todos' ? 'selected' : '' ?>>Todos</option>
                </select>
            </div>

            <div class="d-flex align-items-center flex-grow-1 flex-sm-grow-0">
                <label for="filtroOrden" class="small fw-semibold text-secondary me-2 text-nowrap mb-0">
                    <i class="bi bi-arrow-down-up text-primary me-1"></i>Orden:
                </label>
                <select id="filtroOrden" name="orden" class="form-select form-select-sm audit-select-orden flex-grow-1">
                    <option value="desc" <?= $orden === 'desc' ? 'selected' : '' ?>>Descendente</option>
                    <option value="asc" <?= $orden === 'asc' ? 'selected' : '' ?>>Ascendente</option>
                </select>
            </div>

            <noscript>
                <button type="submit" class="btn btn-sm btn-primary">Aplicar</button>
            </noscript>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 audit-table" id="tablaAuditoria">
            <thead class="table-dark">
                <tr>
                    <th class="text-nowrap audit-col-fecha">Fecha</th>
                    <th class="audit-col-usuario">Usuario</th>
                    <th>Acción</th>
                    <th class="text-nowrap audit-col-ip">IP</th>
                </tr>
            </thead>
            <tbody id="tbodyLogs">
                <?php if (empty($logs)): ?>
                    <tr id="rowSinResultados">
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-info-circle me-1"></i>No hay registros de auditoría disponibles para el período seleccionado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-nowrap audit-cell-fecha">
                            <span class="d-inline-flex align-items-center">
                                <i class="bi bi-clock me-1 text-muted d-lg-none"></i>
                                <?= Security::sanitize((string)($log['fecha'] ?? 'N/A')) ?>
                            </span>
                        </td>
                        <td class="audit-cell-user">
                            <span class="d-inline-block text-truncate fw-bold audit-user-truncate" title="<?= Security::sanitize((string)($log['usuario_nombre'] ?? 'Sistema')) ?>">
                                <i class="bi bi-person-fill me-1 text-primary d-lg-none"></i>
                                <?= Security::sanitize((string)($log['usuario_nombre'] ?? 'Sistema')) ?>
                            </span>
                        </td>
                        <td class="text-break audit-cell-action"><?= Security::sanitize((string)($log['accion'] ?? '')) ?></td>
                        <td class="text-nowrap audit-cell-ip">
                            <span class="text-muted small">
                                <i class="bi bi-hdd-network me-1 d-lg-none"></i>
                                <span class="d-lg-none fw-semibold">IP: </span><?= Security::sanitize((string)($log['ip_origen'] ?? 'Desconocida')) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
