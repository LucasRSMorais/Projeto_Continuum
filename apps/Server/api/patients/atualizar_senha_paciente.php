<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../config/cors.php";
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

if (
    !isset($_SESSION['usuario_id'])
    || ($_SESSION['2fa_verificado'] ?? false) !== true
) {
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

$senhaAtual = $dados['senha_atual'] ?? '';
$novaSenha = $dados['nova_senha'] ?? '';
$confirmarSenha = $dados['confirmar_senha'] ?? '';

if ($senhaAtual === '' || $novaSenha === '' || $confirmarSenha === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Preencha todos os campos de senha."
    ]);
    exit;
}

if ($novaSenha !== $confirmarSenha) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "A nova senha e a confirmação não coincidem."
    ]);
    exit;
}

if (strlen($novaSenha) < 6) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "A nova senha deve ter pelo menos 6 caracteres."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../../config/database.php";
    require_once __DIR__ . "/../shared/registrarLog.php";

    // Busca a senha atual armazenada na tabela pacientes
    $stmt = $pdo->prepare("SELECT senha_hash FROM pacientes WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente || !password_verify($senhaAtual, $paciente['senha_hash'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "A senha atual está incorreta."
        ]);
        exit;
    }

    // Gera o novo hash seguro (compatível com Argon2 / password_hash padrão do PHP)
    $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $stmtUpdate = $pdo->prepare("UPDATE pacientes SET senha_hash = :senha WHERE id = :id");
    $stmtUpdate->execute([
        ':senha' => $novoHash,
        ':id' => $_SESSION['usuario_id']
    ]);

    registrarLog(
        $pdo,
        $_SESSION['usuario_id'],
        "SENHA_ALTERADA",
        "O paciente alterou sua senha de acesso.",
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        "INFO"
    );

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Senha alterada com sucesso!"
    ]);

} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao alterar senha: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro interno ao alterar a senha."
    ]);
}