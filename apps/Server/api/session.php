<?php
// Página responsável por verificar se o usuário está autenticado.
require_once __DIR__ . "/../config/database.php";

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

$tempoExpiracao = 30 * 60; // 30 minutos

if (
    isset($_SESSION['ultima_atividade']) &&
    time() - $_SESSION['ultima_atividade'] > $tempoExpiracao
) {
    $_SESSION = [];
    session_destroy();
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Sessão expirada."
    ]);
    exit;
}

$_SESSION['ultima_atividade'] = time();

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Usuário não autenticado."
    ]);
    exit;
}

try {
    // Consulta segura com tratamento de exceções
    $stmt = $pdo->prepare("SELECT cpf, telefone, aceite_termo_uso, sexo, data_nascimento FROM pacientes WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $data_cadastro = null;
    $stmtConsetimento = $pdo->prepare("SELECT data_hora FROM logs_auditoria WHERE id_usuario = :id AND acao_realizada = 'CADASTRO_PACIENTE' AND tabela_afetada = 'pacientes' ORDER BY data_hora ASC LIMIT 1");
    $stmtConsetimento->execute([':id' => $_SESSION['usuario_id']]);
    $data_cadastro = $stmtConsetimento->fetchColumn();

    echo json_encode([
        "success" => true,
        "authenticated" => true,
        "usuario" => [
            "id" => $_SESSION['usuario_id'],
            "nome" => $_SESSION['nome'] ?? '',
            "email" => $_SESSION['email'] ?? '',
            "cpf" => $paciente['cpf'] ?? null,
            "telefone" => $paciente['telefone'] ?? null,
            "data" => $data_cadastro ?: null,
            "aceite_termo_uso" => $paciente['aceite_termo_uso'] ?? null,
            "sexo" => $paciente['sexo'] ?? null,
            "data_nascimento" => $paciente['data_nascimento'] ?? null
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro no banco de dados.",
        "error" => $e->getMessage()
    ]);
}