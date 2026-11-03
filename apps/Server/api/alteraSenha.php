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

$atual = $dados['senha_atual'] ?? '';
$nova = $dados['nova_senha'] ?? '';

if (!is_string($atual) || !is_string($nova) || $atual === '' || $nova === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe a senha atual e a nova senha."
    ]);
    exit;
}

if (strlen($nova) < 8) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "A nova senha deve ter pelo menos 8 caracteres."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../config/database.php";
    require_once __DIR__ . "/registrarLog.php";

    $stmt = $pdo->prepare(
        "SELECT nome_completo, senha_hash FROM pacientes WHERE id = :id"
    );
    $stmt->execute([
        "id" => $_SESSION['usuario_id']
    ]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente || !password_verify($atual, $paciente['senha_hash'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "A senha atual está incorreta."
        ]);
        exit;
    }

    $hash = password_hash($nova, PASSWORD_DEFAULT);
    if ($hash === false) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Não foi possível proteger a nova senha."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "UPDATE pacientes SET senha_hash = :senha_hash WHERE id = :id"
    );
    $stmt->execute([
        "senha_hash" => $hash,
        "id" => $_SESSION['usuario_id']
    ]);

    registrarLog(
        $pdo,
        $_SESSION['usuario_id'],
        "SENHA_ALTERADA",
        "O paciente " . $paciente['nome_completo'] . " alterou a própria senha na area de privacidade na home.",
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        "SECURITY"
    );

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Senha alterada com sucesso."
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Falha ao alterar senha do paciente: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível alterar a senha no momento."
    ]);
}
