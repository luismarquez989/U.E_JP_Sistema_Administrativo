<?php
// Modelo de actas de graduacion, nominas de estudiantes e imagenes del libro fisico

class Acta {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    private function obtenerSiguienteNumeroActa(string $anioEscolar): int {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(numero_acta), 0) + 1 FROM actas WHERE anio_escolar = :anio");
        $stmt->execute([':anio' => trim($anioEscolar)]);
        return max(1, (int)$stmt->fetchColumn());
    }

    public static function normalizarTexto(string $texto): string {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $mapa = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ª' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'º' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n'
        ];
        $texto = strtr($texto, $mapa);
        $texto = (string)preg_replace('/[^a-z0-9\s]/', ' ', $texto);
        $texto = (string)preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    public static function normalizarCedula(string $cedula): string {
        return (string)preg_replace('/[^0-9]/', '', $cedula);
    }

    public function getAniosEscolares(): array {
        try {
            $stmt = $this->db->query("SELECT DISTINCT anio_escolar FROM actas ORDER BY anio_escolar DESC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        } catch (PDOException) {
            return [];
        }
    }

    // Busqueda de actas por nombre de graduado (en cualquier orden), cedula, fecha o anio escolar
    public function buscarActas(array $filtros = []): array {
        $queryRaw    = trim((string)($filtros['q'] ?? ''));
        $anioEscolar = trim((string)($filtros['anio_escolar'] ?? ''));
        $fechaNac    = trim((string)($filtros['fecha_nacimiento'] ?? ''));
        $estado      = trim((string)($filtros['estado'] ?? 'activo'));

        if ($queryRaw === '' && $anioEscolar === '' && $fechaNac === '' && $estado !== 'inactivo') {
            return [];
        }

        $whereActa = [];
        $params = [];

        if ($estado === 'activo') {
            $whereActa[] = "a.activo = 1";
        } elseif ($estado === 'inactivo') {
            $whereActa[] = "a.activo = 0";
        }

        if ($anioEscolar !== '') {
            $whereActa[] = "a.anio_escolar LIKE :anio_escolar";
            $params[':anio_escolar'] = '%' . $anioEscolar . '%';
        }

        $condicionesEstudiante = [];

        if ($fechaNac !== '') {
            $condicionesEstudiante[] = "e.fecha_nacimiento = :fecha_nac";
            $params[':fecha_nac'] = $fechaNac;
        }

        if ($queryRaw !== '') {
            $soloDigitos = self::normalizarCedula($queryRaw);
            $textoNorm = self::normalizarTexto($queryRaw);
            $esConsultaNumerica = ($soloDigitos !== '' && strlen($soloDigitos) === strlen(str_replace(['V', 'v', 'E', 'e', '-', '.', ' '], '', $queryRaw)));

            if ($esConsultaNumerica) {
                $condicionesEstudiante[] = "(e.cedula_normalizada LIKE :ced_q OR e.nombre_normalizado LIKE :nom_num_q)";
                $params[':ced_q'] = '%' . $soloDigitos . '%';
                $params[':nom_num_q'] = '%' . $soloDigitos . '%';
            } else {
                $tokens = array_values(array_filter(explode(' ', $textoNorm), fn($t) => $t !== ''));
                $subConds = [];
                foreach ($tokens as $idx => $token) {
                    $keyNom = ":tok_nom_{$idx}";
                    if (ctype_digit($token)) {
                        $keyCed = ":tok_ced_{$idx}";
                        $subConds[] = "(e.nombre_normalizado LIKE {$keyNom} OR e.cedula_normalizada LIKE {$keyCed})";
                        $params[$keyNom] = '%' . $token . '%';
                        $params[$keyCed] = '%' . $token . '%';
                    } else {
                        $subConds[] = "e.nombre_normalizado LIKE {$keyNom}";
                        $params[$keyNom] = '%' . $token . '%';
                    }
                }
                if (!empty($subConds)) {
                    $condicionesEstudiante[] = '(' . implode(' AND ', $subConds) . ')';
                }
            }
        }

        $filtroEstudianteSQL = !empty($condicionesEstudiante) ? implode(' AND ', $condicionesEstudiante) : '';

        if ($filtroEstudianteSQL !== '') {
            $whereActa[] = "EXISTS (SELECT 1 FROM acta_estudiantes e WHERE e.acta_id = a.id AND {$filtroEstudianteSQL})";
        }

        $whereClause = !empty($whereActa) ? ('WHERE ' . implode(' AND ', $whereActa)) : '';

        $sql = "
            SELECT 
                a.id,
                a.anio_escolar,
                a.numero_acta,
                a.activo,
                a.creado_en,
                (SELECT COUNT(*) FROM acta_estudiantes ce WHERE ce.acta_id = a.id) AS total_estudiantes,
                (SELECT COUNT(*) FROM acta_imagenes ci WHERE ci.acta_id = a.id) AS total_imagenes
            FROM actas a
            {$whereClause}
            ORDER BY a.anio_escolar DESC, a.numero_acta DESC, a.id DESC
            LIMIT 200
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $actas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($actas)) {
            return [];
        }

        foreach ($actas as &$acta) {
            $actaId = (int)$acta['id'];
            if ($filtroEstudianteSQL !== '') {
                $paramsEst = [':acta_id' => $actaId];
                foreach ($params as $k => $v) {
                    if ($k !== ':anio_escolar') {
                        $paramsEst[$k] = $v;
                    }
                }
                $sqlMatch = "
                    SELECT orden, apellidos_nombres, cedula, fecha_nacimiento, anio_formato, literal_nota, observacion
                    FROM acta_estudiantes e
                    WHERE e.acta_id = :acta_id AND {$filtroEstudianteSQL}
                    ORDER BY e.orden ASC
                ";
                $stMatch = $this->db->prepare($sqlMatch);
                $stMatch->execute($paramsEst);
                $coincidenciasAll = $stMatch->fetchAll(PDO::FETCH_ASSOC);
                $acta['total_coincidencias'] = count($coincidenciasAll);
                $acta['coincidencias'] = array_slice($coincidenciasAll, 0, 8);
                $acta['filtro_estudiante_activo'] = true;
            } else {
                $stPreview = $this->db->prepare("
                    SELECT orden, apellidos_nombres, cedula, fecha_nacimiento, anio_formato, literal_nota, observacion
                    FROM acta_estudiantes
                    WHERE acta_id = :acta_id
                    ORDER BY orden ASC
                    LIMIT 3
                ");
                $stPreview->execute([':acta_id' => $actaId]);
                $acta['total_coincidencias'] = (int)$acta['total_estudiantes'];
                $acta['coincidencias'] = $stPreview->fetchAll(PDO::FETCH_ASSOC);
                $acta['filtro_estudiante_activo'] = false;
            }
        }
        unset($acta);

        if ($filtroEstudianteSQL !== '') {
            usort($actas, function (array $a, array $b): int {
                $cmp = (int)$b['total_coincidencias'] <=> (int)$a['total_coincidencias'];
                if ($cmp !== 0) {
                    return $cmp;
                }
                $cmpAnio = strcmp((string)$b['anio_escolar'], (string)$a['anio_escolar']);
                if ($cmpAnio !== 0) {
                    return $cmpAnio;
                }
                return (int)$b['numero_acta'] <=> (int)$a['numero_acta'];
            });
        }

        return $actas;
    }

    public function getActaById(int|string $id): array|false {
        $actaId = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM actas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $actaId]);
        $acta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$acta) {
            return false;
        }

        $stEst = $this->db->prepare("SELECT * FROM acta_estudiantes WHERE acta_id = :id ORDER BY orden ASC, id ASC");
        $stEst->execute([':id' => $actaId]);
        $acta['estudiantes'] = $stEst->fetchAll(PDO::FETCH_ASSOC);

        $stImg = $this->db->prepare("SELECT * FROM acta_imagenes WHERE acta_id = :id ORDER BY orden ASC, id ASC");
        $stImg->execute([':id' => $actaId]);
        $acta['imagenes'] = $stImg->fetchAll(PDO::FETCH_ASSOC);

        return $acta;
    }

    public function getImagenById(int|string $imgId): array|false {
        $stmt = $this->db->prepare("
            SELECT i.*, a.anio_escolar, a.numero_acta, a.activo AS acta_activa
            FROM acta_imagenes i
            INNER JOIN actas a ON a.id = i.acta_id
            WHERE i.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$imgId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createActa(string $anioEscolar, array $estudiantes, array $imagenes, ?int $userId = null): int|false {
        try {
            $this->db->beginTransaction();

            $anioLimpio = trim($anioEscolar);
            $numeroActa = $this->obtenerSiguienteNumeroActa($anioLimpio);

            $stmtActa = $this->db->prepare("
                INSERT INTO actas (anio_escolar, numero_acta, activo, usuario_id)
                VALUES (:anio, :num, 1, :uid)
            ");
            $stmtActa->execute([
                ':anio' => $anioLimpio,
                ':num'  => $numeroActa,
                ':uid'  => $userId
            ]);

            $actaId = (int)$this->db->lastInsertId();
            $this->insertarEstudiantesLote($actaId, $estudiantes);

            if (!empty($imagenes)) {
                $stmtImg = $this->db->prepare("
                    INSERT INTO acta_imagenes (acta_id, orden, archivo, nombre_original, mime_type)
                    VALUES (:acta_id, :orden, :archivo, :original, :mime)
                ");
                $ordenImg = 1;
                foreach ($imagenes as $img) {
                    if ($ordenImg > 4) {
                        break;
                    }
                    $stmtImg->execute([
                        ':acta_id'  => $actaId,
                        ':orden'    => $ordenImg++,
                        ':archivo'  => $img['archivo'],
                        ':original' => mb_substr((string)$img['nombre_original'], 0, 250, 'UTF-8'),
                        ':mime'     => $img['mime_type']
                    ]);
                }
            }

            $this->db->commit();
            return $actaId;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function updateActa(int $actaId, string $anioEscolar, array $estudiantes, array $idsImagenesConservar, array $nuevasImagenes): bool {
        try {
            $this->db->beginTransaction();

            $stOld = $this->db->prepare("SELECT anio_escolar, numero_acta FROM actas WHERE id = :id LIMIT 1");
            $stOld->execute([':id' => $actaId]);
            $oldRow = $stOld->fetch(PDO::FETCH_ASSOC);

            $anioLimpio = trim($anioEscolar);
            $numeroActa = (int)($oldRow['numero_acta'] ?? 1);
            if ($oldRow && (string)$oldRow['anio_escolar'] !== $anioLimpio) {
                $numeroActa = $this->obtenerSiguienteNumeroActa($anioLimpio);
            }

            $stmtActa = $this->db->prepare("UPDATE actas SET anio_escolar = :anio, numero_acta = :num WHERE id = :id");
            $stmtActa->execute([
                ':anio' => $anioLimpio,
                ':num'  => $numeroActa,
                ':id'   => $actaId
            ]);

            $this->db->prepare("DELETE FROM acta_estudiantes WHERE acta_id = :id")->execute([':id' => $actaId]);
            $this->insertarEstudiantesLote($actaId, $estudiantes);

            $stActuales = $this->db->prepare("SELECT * FROM acta_imagenes WHERE acta_id = :id ORDER BY orden ASC, id ASC");
            $stActuales->execute([':id' => $actaId]);
            $imgsActuales = $stActuales->fetchAll(PDO::FETCH_ASSOC);

            $idsConservarInt = array_map('intval', $idsImagenesConservar);
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';

            foreach ($imgsActuales as $imgAct) {
                $imgId = (int)$imgAct['id'];
                if (!in_array($imgId, $idsConservarInt, true)) {
                    $archivoNombre = basename((string)$imgAct['archivo']);
                    $filePath = $uploadDir . $archivoNombre;
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                    ImageOptimizer::deleteWebp($archivoNombre, $uploadDir);
                    $this->db->prepare("DELETE FROM acta_imagenes WHERE id = :img_id")->execute([':img_id' => $imgId]);
                }
            }

            $stRestantes = $this->db->prepare("SELECT id FROM acta_imagenes WHERE acta_id = :id ORDER BY orden ASC, id ASC");
            $stRestantes->execute([':id' => $actaId]);
            $restantes = $stRestantes->fetchAll(PDO::FETCH_COLUMN);

            $ordenActual = 1;
            $stUpdOrden = $this->db->prepare("UPDATE acta_imagenes SET orden = :orden WHERE id = :img_id");
            foreach ($restantes as $rId) {
                $stUpdOrden->execute([':orden' => $ordenActual++, ':img_id' => (int)$rId]);
            }

            if (!empty($nuevasImagenes) && $ordenActual <= 4) {
                $stmtImg = $this->db->prepare("
                    INSERT INTO acta_imagenes (acta_id, orden, archivo, nombre_original, mime_type)
                    VALUES (:acta_id, :orden, :archivo, :original, :mime)
                ");
                foreach ($nuevasImagenes as $img) {
                    if ($ordenActual > 4) {
                        break;
                    }
                    $stmtImg->execute([
                        ':acta_id'  => $actaId,
                        ':orden'    => $ordenActual++,
                        ':archivo'  => $img['archivo'],
                        ':original' => mb_substr((string)$img['nombre_original'], 0, 250, 'UTF-8'),
                        ':mime'     => $img['mime_type']
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    private function insertarEstudiantesLote(int $actaId, array $estudiantes): void {
        $stmtEst = $this->db->prepare("
            INSERT INTO acta_estudiantes (
                acta_id, orden, apellidos_nombres, nombre_normalizado,
                cedula, cedula_normalizada, fecha_nacimiento, anio_formato, literal_nota, observacion
            ) VALUES (
                :acta_id, :orden, :nombre, :nombre_norm,
                :cedula, :cedula_norm, :fnac, :anio_fmt, :literal, :obs
            )
        ");

        $orden = 1;
        foreach ($estudiantes as $est) {
            $nombreLimpio = mb_substr(trim((string)$est['apellidos_nombres']), 0, 180, 'UTF-8');
            $cedulaLimpia = trim((string)($est['cedula'] ?? ''));
            $cedulaNorm = $cedulaLimpia !== '' ? self::normalizarCedula($cedulaLimpia) : null;
            $obsLimpia = trim((string)($est['observacion'] ?? ''));
            $anioFmt = (int)($est['anio_formato'] ?? 2) === 4 ? 4 : 2;

            $stmtEst->execute([
                ':acta_id'     => $actaId,
                ':orden'       => $orden++,
                ':nombre'      => $nombreLimpio,
                ':nombre_norm' => self::normalizarTexto($nombreLimpio),
                ':cedula'      => $cedulaLimpia !== '' ? mb_substr($cedulaLimpia, 0, 35, 'UTF-8') : null,
                ':cedula_norm' => ($cedulaNorm !== null && $cedulaNorm !== '') ? substr($cedulaNorm, 0, 25) : null,
                ':fnac'        => $est['fecha_nacimiento'],
                ':anio_fmt'    => $anioFmt,
                ':literal'     => mb_substr(trim((string)$est['literal_nota']), 0, 15, 'UTF-8'),
                ':obs'         => $obsLimpia !== '' ? mb_substr($obsLimpia, 0, 40, 'UTF-8') : null
            ]);
        }
    }

    public function setEstadoActa(int $actaId, int $activo): bool {
        $stmt = $this->db->prepare("UPDATE actas SET activo = :act WHERE id = :id");
        return $stmt->execute([
            ':act' => $activo ? 1 : 0,
            ':id'  => $actaId
        ]);
    }
}
