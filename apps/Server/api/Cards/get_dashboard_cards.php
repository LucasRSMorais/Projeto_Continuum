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

$isProduction = getenv('RAILWAY_ENVIRONMENT') !== false
    || getenv('RAILWAY_PUBLIC_DOMAIN') !== false
    || getenv('PORT') !== false;
$localDashboardAuthDisabled = !$isProduction
    && getenv('DASHBOARD_AUTH_DISABLED') === 'true';

if (!$localDashboardAuthDisabled && !isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Entre novamente.'
    ]);
    exit;
}

if (!$localDashboardAuthDisabled && !isset($_SESSION['perfil'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso permitido apenas a profissionais da equipe.'
    ]);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$idSetor = filter_input(INPUT_GET, 'id_setor', FILTER_VALIDATE_INT);
$idSetor = $idSetor === false || $idSetor === null ? 1 : $idSetor;

if ($idSetor < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'O setor informado é inválido.'
    ]);
    exit;
}

try {
    $queueQuery = $pdo->prepare("
        SELECT fila_id, paciente_nome, origem, gravidade, tempo_espera_minutos
        FROM vw_dash_fila_admissoes
        WHERE id_setor = :id_setor
        ORDER BY FIELD(gravidade, 'alta', 'media', 'baixa'),
                 tempo_espera_minutos DESC
        LIMIT 10
    ");
    $queueQuery->execute(['id_setor' => $idSetor]);

    $pendingQuery = $pdo->prepare("
        SELECT assinatura_id, paciente_nome, leito_numero, descricao_alta
        FROM vw_dash_altas_pendentes
        WHERE id_setor = :id_setor
        ORDER BY assinatura_id DESC
        LIMIT 10
    ");
    $pendingQuery->execute(['id_setor' => $idSetor]);

    $movementQuery = $pdo->prepare("
        SELECT pe.id AS movement_id, pe.movimentacao_leito, pe.ultima_atualizacao,
               p.nome_completo AS paciente_nome, l.numero AS leito_numero
        FROM pacientes_enfermarias pe
        JOIN enfermarias e ON e.id = pe.id_enfermaria
        JOIN pacientes p ON p.id = pe.id_paciente
        JOIN leitos l ON l.id = pe.id_leito
        WHERE e.id_setor = :id_setor
        ORDER BY pe.ultima_atualizacao DESC, pe.id DESC
        LIMIT 10
    ");
    $movementQuery->execute(['id_setor' => $idSetor]);

    echo json_encode([
        'success' => true,
        'data' => [
            'patients' => $queueQuery->fetchAll(),
            'pending' => $pendingQuery->fetchAll(),
            'movements' => $movementQuery->fetchAll()
        ]
    ]);
} catch (PDOException $exception) {
    error_log('Dashboard cards query failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível consultar os dados do painel.'
    ]);
}
