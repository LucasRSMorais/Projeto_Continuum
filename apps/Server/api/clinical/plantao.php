
<?php

// Este endpoint consulta e altera o plantão corrente de um setor.
// O setor é definido pela especialidade do médico autenticado.
// As operações de escrita são transacionais para evitar alterações concorrentes.

require_once __DIR__ . '/../../config/cors.php';
applyCorsHeaders();
header('Content-Type: application/json; charset=utf-8');

/**
 * Envia uma resposta JSON com o status HTTP indicado e encerra a execução.
 */
function plantaoRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Verifica se a origem do navegador está entre as origens autorizadas.
 */
function plantaoAllowedOrigin(string $origin): bool
{
    $allowedOrigins = [
        'http://localhost:5173',
        'https://localhost:5173',
        'http://127.0.0.1:5173',
        'https://127.0.0.1:5173',
        'https://frontend-production-55eb.up.railway.app',
    ];

    $clientUrl = getenv('CLIENT_URL');

    if ($clientUrl) {
        $allowedOrigins[] = rtrim($clientUrl, '/');
    }

    $railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN');

    if ($railwayDomain) {
        $allowedOrigins[] = 'https://' . trim($railwayDomain, '/');
    }

    return in_array($origin, $allowedOrigins, true);
}

/**
 * Identifica o setor autorizado usando a especialidade do médico autenticado.
 */
function resolveDoctorSector(PDO $pdo, int $actorId): int
{
    $doctorQuery = $pdo->prepare("
        SELECT id, id_instituicao, especialidade, status
        FROM medicos
        WHERE id = :id
        LIMIT 1
    ");

    $doctorQuery->execute(['id' => $actorId]);
    $doctor = $doctorQuery->fetch(PDO::FETCH_ASSOC);

    if (!$doctor || $doctor['status'] !== 'ativo') {
        plantaoRespond(403, [
            'success' => false,
            'message' => 'Médico não encontrado ou inativo.'
        ]);
    }

    $institutionId = (int) ($doctor['id_instituicao'] ?? 0);
    $specialty = trim((string) ($doctor['especialidade'] ?? ''));

    if ($institutionId < 1 || $specialty === '') {
        plantaoRespond(403, [
            'success' => false,
            'message' => 'O médico não possui instituição ou especialidade cadastrada.'
        ]);
    }

    // Inicializa a variável para evitar aviso de possível indefinição.
    $sectorType = '';

    // Relaciona a especialidade do médico ao tipo de setor.
    if ($specialty === 'Clínica Geral') {
        $sectorType = 'Clínica Geral';
    } elseif ($specialty === 'Cirúrgica') {
        $sectorType = 'Cirúrgica';
    } elseif ($specialty === 'Obstétrica e Perinatal') {
        $sectorType = 'Obstétrica e Perinatal';
    } elseif ($specialty === 'Cardiologia') {
        $sectorType = 'Cardiologia';
    } elseif ($specialty === 'Neurologia') {
        $sectorType = 'Neurologia';
    } elseif ($specialty === 'Isolamento') {
        $sectorType = 'Isolamento';
    } elseif ($specialty === 'Enfermaria') {
        $sectorType = 'Enfermaria';
    } else {
        plantaoRespond(403, [
            'success' => false,
            'message' => 'A especialidade do médico não possui um setor autorizado.'
        ]);
    }

    // Cardiologia utiliza o setor de ID 4 confirmado no banco.
    if ($specialty === 'Cardiologia') {
        $sectorQuery = $pdo->prepare("
            SELECT id
            FROM setores
            WHERE id = 4
              AND tipo = :tipo
              AND id_instituicao = :instituicao
        ");

        $sectorQuery->execute([
            'tipo' => $sectorType,
            'instituicao' => $institutionId
        ]);
    } else {
        // Para as demais especialidades, procura pelo tipo e instituição.
        $sectorQuery = $pdo->prepare("
            SELECT id
            FROM setores
            WHERE tipo = :tipo
              AND id_instituicao = :instituicao
        ");

        $sectorQuery->execute([
            'tipo' => $sectorType,
            'instituicao' => $institutionId
        ]);
    }

    $sectorIds = $sectorQuery->fetchAll(PDO::FETCH_COLUMN);

    if (count($sectorIds) !== 1) {
        plantaoRespond(403, [
            'success' => false,
            'message' => 'Não foi possível identificar um único setor autorizado para sua especialidade.'
        ]);
    }

    return (int) $sectorIds[0];
}

/**
 * Calcula o intervalo de 12 horas que contém o momento atual.
 */
function currentPlantaoWindow(): array
{
    $timezone = new DateTimeZone('America/Sao_Paulo');
    $now = new DateTimeImmutable('now', $timezone);
    $hour = (int) $now->format('G');

    if ($hour < 7) {
        $start = $now->modify('-1 day')->setTime(19, 0);
        $end = $now->setTime(7, 0);
    } elseif ($hour < 19) {
        $start = $now->setTime(7, 0);
        $end = $now->setTime(19, 0);
    } else {
        $start = $now->setTime(19, 0);
        $end = $now->modify('+1 day')->setTime(7, 0);
    }

    $utc = new DateTimeZone('UTC');
    $startUtc = $start->setTimezone($utc);
    $endUtc = $end->setTimezone($utc);

    return [
        'type' => $start->format('H') === '07' ? 'diurno' : 'noturno',
        'label' => $start->format('H:i') . ' - ' . $end->format('H:i'),
        'start' => $startUtc->format('Y-m-d H:i:s'),
        'end' => $endUtc->format('Y-m-d H:i:s'),
        'now' => (new DateTimeImmutable('now', $utc))->format('Y-m-d H:i:s'),
    ];
}

/**
 * Garante que o plantão corrente exista e encerra períodos anteriores abertos.
 */
function prepareCurrentPlantao(
    PDO $pdo,
    int $sectorId,
    array $window,
    ?int $actorId,
    ?string $ip
): int {
    $sectorQuery = $pdo->prepare(
        'SELECT id FROM setores WHERE id = :id FOR UPDATE'
    );

    $sectorQuery->execute(['id' => $sectorId]);

    if (!$sectorQuery->fetchColumn()) {
        throw new DomainException('O setor informado não existe.');
    }

    $expiredQuery = $pdo->prepare("
        SELECT id
        FROM plantoes
        WHERE id_setor = :id_setor
          AND status = 'aberto'
          AND fim_em <= :inicio_atual
        FOR UPDATE
    ");

    $expiredQuery->execute([
        'id_setor' => $sectorId,
        'inicio_atual' => $window['start'],
    ]);

    $expiredShifts = $expiredQuery->fetchAll(PDO::FETCH_ASSOC);

    if ($expiredShifts) {
        $closeExpired = $pdo->prepare("
            UPDATE plantoes
            SET status = 'encerrado',
                data_ocorrido = :data_ocorrido,
                encerrado_em = :encerrado_em,
                ip = :ip
            WHERE id = :id AND status = 'aberto'
        ");

        foreach ($expiredShifts as $expiredShift) {
            $closeExpired->execute([
                'data_ocorrido' => $window['now'],
                'encerrado_em' => $window['now'],
                'ip' => $ip,
                'id' => $expiredShift['id'],
            ]);

            registrarLog(
                $pdo,
                $actorId,
                'PLANTAO_ENCERRADO_POR_HORARIO',
                'Plantão ' . $expiredShift['id'] . ' encerrado ao iniciar um novo período.',
                'plantoes',
                $ip,
                'WARNING'
            );
        }
    }

    $insertShift = $pdo->prepare("
        INSERT INTO plantoes (id_setor, plantao, inicio_em, fim_em)
        VALUES (:id_setor, :plantao, :inicio_em, :fim_em)
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
    ");

    $insertShift->execute([
        'id_setor' => $sectorId,
        'plantao' => $window['type'],
        'inicio_em' => $window['start'],
        'fim_em' => $window['end'],
    ]);

    $shiftQuery = $pdo->prepare("
        SELECT id
        FROM plantoes
        WHERE id_setor = :id_setor
          AND inicio_em = :inicio_em
        FOR UPDATE
    ");

    $shiftQuery->execute([
        'id_setor' => $sectorId,
        'inicio_em' => $window['start'],
    ]);

    return (int) $shiftQuery->fetchColumn();
}

/**
 * Desfaz a transação e retorna uma falha padronizada.
 */
function plantaoFailure(PDO $pdo, int $status, string $message): void
{
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    plantaoRespond($status, [
        'success' => false,
        'message' => $message
    ]);
}

// Valida método e origem.
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && !plantaoAllowedOrigin($origin)) {
    plantaoRespond(403, [
        'success' => false,
        'message' => 'Origem da solicitação não permitida.'
    ]);
}

// Responde às verificações CORS.
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST, OPTIONS');

    plantaoRespond(405, [
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
}

// Configuração da sessão.
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None',
]);

session_start();

// Valida a sessão autenticada.
$actorId = isset($_SESSION['usuario_id'])
    ? filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT)
    : null;

$authenticated = $actorId !== false
    && $actorId !== null
    && $actorId > 0
    && !empty($_SESSION['perfil']);

if (!$authenticated) {
    plantaoRespond(401, [
        'success' => false,
        'message' => 'Entre com uma sessão válida para consultar ou alterar o plantão.'
    ]);
}

if ($method === 'POST' && $origin === '') {
    plantaoRespond(403, [
        'success' => false,
        'message' => 'Origem da solicitação ausente.'
    ]);
}

require_once __DIR__ . '/../shared/registrarLog.php';
require_once __DIR__ . '/../../config/database.php';

// No POST, recebe apenas os dados da operação.
$request = [];

if ($method === 'POST') {
    $request = json_decode(file_get_contents('php://input'), true);

    if (!is_array($request)) {
        plantaoRespond(400, [
            'success' => false,
            'message' => 'Corpo JSON inválido.'
        ]);
    }
}

// O setor é identificado exclusivamente pelo médico autenticado.
$sectorId = resolveDoctorSector($pdo, (int) $actorId);

// Valida o endereço IP para os registros de auditoria.
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

if ($ip !== null && filter_var($ip, FILTER_VALIDATE_IP) === false) {
    $ip = null;
}

$window = currentPlantaoWindow();

try {
    $pdo->beginTransaction();

    $shiftId = prepareCurrentPlantao(
        $pdo,
        $sectorId,
        $window,
        (int) $actorId,
        $ip
    );

    if ($method === 'GET') {
        $shiftQuery = $pdo->prepare("
            SELECT p.id, p.id_medico, m.nome_completo AS medico_nome,
                   p.status, p.assumido_em, p.encerrado_em
            FROM plantoes p
            LEFT JOIN medicos m ON m.id = p.id_medico
            WHERE p.id = :id
        ");

        $shiftQuery->execute(['id' => $shiftId]);
        $shift = $shiftQuery->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        plantaoRespond(200, [
            'success' => true,
            'data' => [
                'id' => (int) $shift['id'],
                'sectorId' => (int) $sectorId,
                'period' => $window['label'],
                'type' => $window['type'],
                'startAt' => $window['start'],
                'endAt' => $window['end'],
                'status' => $shift['status'],
                'doctorId' => $shift['id_medico'] === null
                    ? null
                    : (int) $shift['id_medico'],
                'doctorName' => $shift['medico_nome'],
                'isMine' => (int) $shift['id_medico'] === (int) $actorId,
                'assignedAt' => $shift['assumido_em'],
                'closedAt' => $shift['encerrado_em'],
                'canManage' => true,
            ],
        ]);
    }

    // Apenas médicos ativos podem realizar operações de escrita.
    $doctorQuery = $pdo->prepare("
        SELECT id
        FROM medicos
        WHERE id = :id AND status = 'ativo'
        FOR UPDATE
    ");

    $doctorQuery->execute(['id' => (int) $actorId]);

    if (!$doctorQuery->fetchColumn()) {
        plantaoFailure(
            $pdo,
            403,
            'Somente um médico ativo pode assumir ou passar o plantão.'
        );
    }

    // Bloqueia o registro atual para evitar alterações concorrentes.
    $currentQuery = $pdo->prepare(
        'SELECT * FROM plantoes WHERE id = :id FOR UPDATE'
    );

    $currentQuery->execute(['id' => $shiftId]);
    $shift = $currentQuery->fetch(PDO::FETCH_ASSOC);

    if (!$shift || $shift['status'] !== 'aberto') {
        plantaoFailure(
            $pdo,
            409,
            'Este período de plantão já foi encerrado.'
        );
    }

    $action = $request['action'] ?? '';

    if ($action === 'assumir') {
        if ($shift['id_medico'] !== null) {
            plantaoFailure(
                $pdo,
                409,
                'Este plantão já foi assumido por outro profissional.'
            );
        }

        $assignQuery = $pdo->prepare("
            UPDATE plantoes
            SET id_medico = :id_medico,
                assumido_em = :now,
                ip = :ip
            WHERE id = :id
              AND status = 'aberto'
              AND id_medico IS NULL
        ");

        $assignQuery->execute([
            'id_medico' => (int) $actorId,
            'now' => $window['now'],
            'ip' => $ip,
            'id' => $shiftId,
        ]);

        if ($assignQuery->rowCount() !== 1) {
            plantaoFailure(
                $pdo,
                409,
                'O plantão foi alterado por outra solicitação. Atualize a tela.'
            );
        }

        registrarLog(
            $pdo,
            (int) $actorId,
            'PLANTAO_ASSUMIDO',
            'Médico assumiu o plantão ' . $shiftId . ' do setor ' . $sectorId . '.',
            'plantoes',
            $ip
        );

        $pdo->commit();

        plantaoRespond(200, [
            'success' => true,
            'message' => 'Plantão assumido com sucesso.'
        ]);
    }

    if ($action === 'passar') {
        if ((int) $shift['id_medico'] !== (int) $actorId) {
            plantaoFailure(
                $pdo,
                403,
                'Somente o profissional que assumiu o plantão pode encerrá-lo.'
            );
        }

        $description = trim((string) ($request['descricao_fato'] ?? ''));

        $descriptionLength = function_exists('mb_strlen')
            ? mb_strlen($description, 'UTF-8')
            : strlen($description);

        if ($description === '' || $descriptionLength > 5000) {
            plantaoFailure(
                $pdo,
                400,
                'Descreva a passagem do plantão (até 5.000 caracteres).'
            );
        }

        // Disponibiliza identidade e IP para triggers de auditoria.
        $setUserId = $pdo->prepare(
            'SET @continuum_usuario_id = :usuario_id'
        );

        $setUserId->bindValue(
            ':usuario_id',
            (int) $actorId,
            PDO::PARAM_INT
        );

        $setUserId->execute();

        $setIp = $pdo->prepare(
            'SET @continuum_ip_address = :ip'
        );

        $setIp->bindValue(
            ':ip',
            (string) ($ip ?? ''),
            PDO::PARAM_STR
        );

        $setIp->execute();

        // Registra a passagem como evento associado ao setor e médico.
        $eventQuery = $pdo->prepare("
            INSERT INTO eventos_adversos
                (id_paciente, id_medico, id_setor, tipo_evento, grau_dano,
                 descricao_fato, data_evento, status_investigacao)
            VALUES
                (NULL, :id_medico, :id_setor, 'passagem_plantao', 'sem_dano',
                 :descricao_fato, :data_evento, 'concluido')
        ");

        $eventQuery->execute([
            'id_medico' => (int) $actorId,
            'id_setor' => $sectorId,
            'descricao_fato' => $description,
            'data_evento' => $window['now'],
        ]);

        $eventId = (int) $pdo->lastInsertId();

        // Fecha o plantão somente se ainda estiver aberto e atribuído ao médico.
        $closeQuery = $pdo->prepare("
            UPDATE plantoes
            SET status = 'encerrado',
                id_evento_adverso = :id_evento,
                descricao_fato = :descricao_fato,
                data_ocorrido = :data_ocorrido,
                encerrado_em = :encerrado_em,
                ip = :ip
            WHERE id = :id
              AND status = 'aberto'
              AND id_medico = :id_medico
        ");

        $closeQuery->execute([
            'id_evento' => $eventId,
            'descricao_fato' => $description,
            'data_ocorrido' => $window['now'],
            'encerrado_em' => $window['now'],
            'ip' => $ip,
            'id' => $shiftId,
            'id_medico' => (int) $actorId,
        ]);

        if ($closeQuery->rowCount() !== 1) {
            plantaoFailure(
                $pdo,
                409,
                'O plantão foi alterado por outra solicitação. Atualize a tela.'
            );
        }

        registrarLog(
            $pdo,
            (int) $actorId,
            'PASSAGEM_PLANTAO_REGISTRADA',
            'Evento adverso ' . $eventId . ' registrado na passagem do plantão ' . $shiftId . '.',
            'eventos_adversos',
            $ip
        );

        registrarLog(
            $pdo,
            (int) $actorId,
            'PLANTAO_ENCERRADO',
            'Plantão ' . $shiftId . ' encerrado com o evento adverso ' . $eventId . '.',
            'plantoes',
            $ip
        );

        // Limpa as variáveis de auditoria utilizadas pelas triggers.
        $clearAuditContext = $pdo->prepare(
            'SET @continuum_usuario_id = NULL, @continuum_ip_address = NULL'
        );

        $clearAuditContext->execute();

        $pdo->commit();

        plantaoRespond(200, [
            'success' => true,
            'message' => 'Plantão encerrado e passagem registrada com sucesso.',
            'data' => [
                'eventId' => $eventId
            ],
        ]);
    }

    plantaoFailure(
        $pdo,
        400,
        'Ação de plantão inválida.'
    );

} catch (DomainException $exception) {
    plantaoFailure(
        $pdo,
        404,
        $exception->getMessage()
    );

} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Plantao API failed: ' . $exception->getMessage());

    plantaoRespond(500, [
        'success' => false,
        'message' => 'Não foi possível processar o plantão.'
    ]);
}
