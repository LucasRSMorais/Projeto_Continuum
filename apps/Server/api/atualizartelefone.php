<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode([
        "success" => false,
        "message" => "Método não permitido."
    ]);
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

if (!isset($_SESSION['usuario_id']) || ($_SESSION['2fa_verificado'] ?? false) !== true) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Faça login novamente."
    ]);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);
if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "O corpo da requisição deve ser um JSON válido."
    ]);
    exit;
}

$telefone = $dados['telefone'] ?? $dados['novo_telefone'] ?? '';
if (!is_string($telefone)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um número de telefone válido."
    ]);
    exit;
}

$telefone = trim($telefone);
$digitosTelefone = preg_replace('/\D/', '', $telefone);
if (
    $telefone === ''
    || !preg_match('/^\+?[0-9\s().-]+$/D', $telefone)
    || $digitosTelefone === null
    || strlen($digitosTelefone) < 10
    || strlen($digitosTelefone) > 15
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um número de telefone válido, com 10 a 15 dígitos."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../config/database.php";
    require_once __DIR__ . "/registrarLog.php";

    $stmt = $pdo->prepare(
        "SELECT nome_completo FROM pacientes WHERE id = :id"
    );
    $stmt->execute([
        "id" => $_SESSION['usuario_id']
    ]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Paciente não encontrado."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "UPDATE pacientes SET telefone = :telefone WHERE id = :id"
    );
    $stmt->execute([
        "telefone" => $telefone,
        "id" => $_SESSION['usuario_id']
    ]);

    registrarLog(
        $pdo,
        $_SESSION['usuario_id'],
        "TELEFONE_ATUALIZADO",
        "O paciente " . $paciente['nome_completo'] . " atualizou o próprio telefone.",
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        "INFO"
    );

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Telefone atualizado com sucesso.",
        "telefone" => $telefone
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Falha ao atualizar telefone do paciente: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível atualizar o telefone no momento."
    ]);
}
