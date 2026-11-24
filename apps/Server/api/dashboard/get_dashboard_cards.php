
<?php

require_once __DIR__ . '/../../config/cors.php';

applyCorsHeaders();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, OPTIONS');
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Entre novamente.'
    ]);
    exit;
}

if (!isset($_SESSION['perfil'])) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Acesso permitido apenas a profissionais da equipe.'
    ]);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

try {

    // Identifica o médico autenticado
    $idMedico = (int) $_SESSION['usuario_id'];

    $medicoQuery = $pdo->prepare("
        SELECT id, id_instituicao, especialidade, status
        FROM medicos
        WHERE id = :id_medico
        LIMIT 1
    ");

    $medicoQuery->execute([
        'id_medico' => $idMedico
    ]);

    $medico = $medicoQuery->fetch(PDO::FETCH_ASSOC);

    if (!$medico || $medico['status'] !== 'ativo') {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Médico não encontrado ou inativo.'
        ]);
        exit;
    }

    $idInstituicao = (int) $medico['id_instituicao'];
    $especialidade = trim($medico['especialidade'] ?? '');

    if ($idInstituicao < 1 || $especialidade === '') {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Médico sem instituição ou especialidade cadastrada.'
        ]);
        exit;
    }

    // Define o setor conforme a especialidade
    $idSetorEsperado = null;
    $tipoSetor = null;

    if ($especialidade === 'Clínica Geral') {
        $idSetorEsperado = 1;
        $tipoSetor = 'Clínica Geral';

    } elseif ($especialidade === 'Cirúrgica') {
        $idSetorEsperado = 2;
        $tipoSetor = 'Cirúrgica';

    } elseif ($especialidade === 'Obstétrica e Perinatal') {
        $idSetorEsperado = 3;
        $tipoSetor = 'Obstétrica e Perinatal';

    } elseif ($especialidade === 'Cardiologia') {
        $idSetorEsperado = 4;
        $tipoSetor = 'Cardiologia';

    } elseif ($especialidade === 'Neurologia') {
        $idSetorEsperado = 5;
        $tipoSetor = 'Neurologia';

    } elseif ($especialidade === 'Isolamento') {
        $idSetorEsperado = 6;
        $tipoSetor = 'Isolamento';

    } elseif ($especialidade === 'Enfermaria') {
        $idSetorEsperado = 7;
        $tipoSetor = 'Enfermaria';

    } else {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Especialidade sem setor autorizado.'
        ]);
        exit;
    }

    // Confirma se o setor pertence à instituição do médico
    $setorQuery = $pdo->prepare("
        SELECT id
        FROM setores
        WHERE id = :id_setor
          AND tipo = :tipo
          AND id_instituicao = :id_instituicao
        LIMIT 1
    ");

    $setorQuery->execute([
        'id_setor' => $idSetorEsperado,
        'tipo' => $tipoSetor,
        'id_instituicao' => $idInstituicao
    ]);

    $setor = $setorQuery->fetch(PDO::FETCH_ASSOC);

    if (!$setor) {
        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'O setor correspondente não foi encontrado na instituição do médico.'
        ]);
        exit;
    }

    // Setor autorizado pelo servidor
    $idSetor = (int) $setor['id'];

    // Fila de admissões
    $queueQuery = $pdo->prepare("
        SELECT fila_id, paciente_nome, origem, gravidade,
               tempo_espera_minutos
        FROM vw_dash_fila_admissoes
        WHERE id_setor = :id_setor
        ORDER BY FIELD(gravidade, 'alta', 'media', 'baixa'),
                 tempo_espera_minutos DESC
        LIMIT 10
    ");

    $queueQuery->execute([
        'id_setor' => $idSetor
    ]);

    // Altas pendentes
    $pendingQuery = $pdo->prepare("
        SELECT assinatura_id, paciente_nome, leito_numero,
               descricao_alta
        FROM vw_dash_altas_pendentes
        WHERE id_setor = :id_setor
        ORDER BY assinatura_id DESC
        LIMIT 10
    ");

    $pendingQuery->execute([
        'id_setor' => $idSetor
    ]);

    // Movimentações de leitos
    $movementQuery = $pdo->prepare("
        SELECT pe.id AS movement_id,
               pe.movimentacao_leito,
               pe.ultima_atualizacao,
               p.nome_completo AS paciente_nome,
               l.numero AS leito_numero
        FROM pacientes_enfermarias pe
        JOIN enfermarias e ON e.id = pe.id_enfermaria
        JOIN pacientes p ON p.id = pe.id_paciente
        JOIN leitos l ON l.id = pe.id_leito
        WHERE e.id_setor = :id_setor
        ORDER BY pe.ultima_atualizacao DESC, pe.id DESC
        LIMIT 10
    ");

    $movementQuery->execute([
        'id_setor' => $idSetor
    ]);

    // Eventos críticos
    $criticalEventsQuery = $pdo->prepare("
        SELECT ea.id AS event_id,
               DATE_FORMAT(ea.data_evento, '%d/%m - %H:%i') AS data_hora,
               ea.tipo_evento,
               p.nome_completo AS paciente_nome,
               l.numero AS leito_numero,
               ea.status_investigacao
        FROM eventos_adversos ea
        JOIN pacientes p ON p.id = ea.id_paciente
        LEFT JOIN pacientes_enfermarias pe
            ON pe.id_paciente = p.id
           AND pe.id = (
               SELECT MAX(current_pe.id)
               FROM pacientes_enfermarias current_pe
               WHERE current_pe.id_paciente = p.id
           )
        LEFT JOIN leitos l ON l.id = pe.id_leito
        LEFT JOIN enfermarias e ON e.id = pe.id_enfermaria
        WHERE e.id_setor = :id_setor
          AND ea.grau_dano = 'alto'
          AND (
              ea.status_investigacao IN ('pendente', 'em_investigacao')
              OR (
                  ea.status_investigacao = 'concluido'
                  AND ea.data_evento >= NOW() - INTERVAL 24 HOUR
              )
          )
        ORDER BY ea.data_evento DESC, ea.id DESC
        LIMIT 10
    ");

    $criticalEventsQuery->execute([
        'id_setor' => $idSetor
    ]);

    // Mapa de leitos somente do setor autorizado
    $bedMapQuery = $pdo->prepare("
        SELECT l.id AS bed_id,
               l.numero AS leito_numero,
               l.status AS status_leito,
               CASE
                   WHEN l.status = 'ocupado'
                   THEN p.nome_completo
                   ELSE NULL
               END AS paciente_nome,
               CASE
                   WHEN l.status = 'ocupado' THEN EXISTS (
                       SELECT 1
                       FROM eventos_adversos ea
                       WHERE ea.id_paciente = p.id
                         AND ea.grau_dano = 'alto'
                         AND ea.status_investigacao IN (
                             'pendente',
                             'em_investigacao'
                         )
                   )
                   ELSE 0
               END AS alerta_critico
        FROM setores s
        JOIN enfermarias e
            ON e.id_setor = s.id
           AND e.status = 'ativo'
        JOIN leitos l ON l.id_enfermaria = e.id
        LEFT JOIN pacientes_enfermarias pe
            ON pe.id_leito = l.id
           AND pe.id = (
               SELECT MAX(current_pe.id)
               FROM pacientes_enfermarias current_pe
               WHERE current_pe.id_leito = l.id
           )
        LEFT JOIN pacientes p ON p.id = pe.id_paciente
        WHERE s.id = :id_setor
        ORDER BY l.numero
    ");

    $bedMapQuery->execute([
        'id_setor' => $idSetor
    ]);

    $beds = $bedMapQuery->fetchAll(PDO::FETCH_ASSOC);

    // Mantém os campos esperados pelo React,
    // mas não consulta leitos de outros setores.
    $utiBeds = [];
    $enfermariaBeds = [];

    if ($idSetor === 4) {
        $utiBeds = $beds;
    }

    if ($idSetor === 7) {
        $enfermariaBeds = $beds;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'patients' => $queueQuery->fetchAll(PDO::FETCH_ASSOC),
            'pending' => $pendingQuery->fetchAll(PDO::FETCH_ASSOC),
            'movements' => $movementQuery->fetchAll(PDO::FETCH_ASSOC),
            'criticalEvents' => $criticalEventsQuery->fetchAll(PDO::FETCH_ASSOC),
            'utiBeds' => $utiBeds,
            'enfermariaBeds' => $enfermariaBeds,
            'id_setor' => $idSetor,
            'especialidade' => $especialidade
        ]
    ]);

} catch (PDOException $exception) {

    error_log(
        'Dashboard cards query failed: ' . $exception->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível consultar os dados do painel.'
    ]);
}
