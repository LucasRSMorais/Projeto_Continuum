<?php
require_once __DIR__ . '/../../config/cors.php';
applyCorsHeaders();
header('Content-Type: application/json; charset=utf-8');

function plantaoRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function plantaoFailure(PDO $pdo, int $status, string $message): void
{
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    plantaoRespond($status, ['success' => false, 'message' => $message]);
}

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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && !plantaoAllowedOrigin($origin)) {
    plantaoRespond(403, ['success' => false, 'message' => 'Origem da solicitação não permitida.']);
}

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST, OPTIONS');
    plantaoRespond(405, ['success' => false, 'message' => 'Método não permitido.']);
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

$actorId = isset($_SESSION['usuario_id']) ? filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT) : null;
$authenticated = $actorId !== false && $actorId !== null && $actorId > 0 && !empty($_SESSION['perfil']);

if (!$authenticated) {
    plantaoRespond(401, ['success' => false, 'message' => 'Entre com uma sessão válida para consultar ou alterar o plantão.']);
}

if ($method === 'POST' && $origin === '') {
    plantaoRespond(403, ['success' => false, 'message' => 'Origem da solicitação ausente.']);
}

require_once __DIR__ . '/../registrarLog.php';
require_once __DIR__ . '/../../config/database.php';

$sectorId = $method === 'GET'
    ? filter_input(INPUT_GET, 'id_setor', FILTER_VALIDATE_INT)
    : null;

$request = [];
if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || $rawBody === '') {
        plantaoRespond(400, ['success' => false, 'message' => 'Corpo da requisição inválido.']);
    }

    $request = json_decode($rawBody, true);
    if (!is_array($request)) {
        plantaoRespond(400, ['success' => false, 'message' => 'Corpo JSON inválido.']);
    }

    $sectorId = filter_var($request['id_setor'] ?? null, FILTER_VALIDATE_INT);
}

if ($sectorId === false || $sectorId === null || $sectorId < 1) {
    plantaoRespond(400, ['success' => false, 'message' => 'O setor informado é inválido.']);
}

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
if ($ip !== null && filter_var($ip, FILTER_VALIDATE_IP) === false) {
    $ip = null;
}

try {
    $pdo->beginTransaction();

    $action = $request['action'] ?? '';

    if ($action === 'assumir') {
        $assignQuery = $pdo->prepare("
            UPDATE plantoes
            SET id_medico = :id_medico,
                assumido_em = NOW(),
                ip = :ip
            WHERE id = :id
              AND status = 'aberto'
              AND id_medico IS NULL
        ");
        $assignQuery->execute([
            ':id_medico' => (int) $actorId,
            ':ip' => $ip,
            ':id' => (int) $sectorId
        ]);

        $pdo->commit();
        plantaoRespond(200, ['success' => true, 'message' => 'Plantão assumido com sucesso.']);
    }

    if ($action === 'passar') {
        $description = trim((string) ($request['descricao_fato'] ?? ''));
        if ($description === '' || mb_strlen($description, 'UTF-8') > 5000) {
            plantaoFailure($pdo, 400, 'Descreva a passagem do plantão (até 5.000 caracteres).');
        }

        $eventQuery = $pdo->prepare("
            INSERT INTO eventos_adversos
                (id_medico, id_setor, tipo_evento, grau_dano, descricao_fato, data_evento, status_investigacao)
            VALUES
                (:id_medico, :id_setor, 'passagem_plantao', 'sem_dano', :descricao_fato, NOW(), 'concluido')
        ");
        $eventQuery->execute([
            ':id_medico' => (int) $actorId,
            ':id_setor' => $sectorId,
            ':descricao_fato' => $description
        ]);

        $pdo->commit();
        plantaoRespond(200, ['success' => true, 'message' => 'Plantão encerrado e passagem registrada com sucesso.']);
    }

    plantaoRespond(400, ['success' => false, 'message' => 'Ação de plantão inválida.']);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Plantao API failed: ' . $e->getMessage());
    plantaoRespond(500, ['success' => false, 'message' => 'Não foi possível processar o plantão.']);
}