<?php

// Página responsável por verificar se o usuário está autenticado.
// Também controla a expiração da sessão após 30 minutos de inatividade.
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/patient_data_encryption.php";

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

// Define o tempo máximo permitido sem atividade do usuário.
$tempoExpiracao = 30 * 60; // Expira a sessão depois de 30 minutos sem atividade.

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
require_once __DIR__ . "/../../config/cors.php";
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

$perfil = $_SESSION['perfil'] ?? '';
$paciente = [];
$medico = [];
$data_cadastro = null;

// Se for paciente, busca nas colunas reais da tabela pacientes
if (strtolower($perfil) === 'paciente') {
    $stmt = $pdo->prepare(
        "SELECT cpf, telefone, sexo, data_nascimento FROM pacientes WHERE id = :id"
    );
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmtConsetimento = $pdo->prepare(
        "SELECT data_hora FROM logs_auditoria WHERE id_usuario = :id AND acao_realizada = 'CADASTRO_PACIENTE' AND tabela_afetada = 'pacientes' ORDER BY data_hora ASC LIMIT 1"
    );
    $stmtConsetimento->execute([':id' => $_SESSION['usuario_id']]);
    $data_cadastro = $stmtConsetimento->fetchColumn();
} else {
    // Caso seja médico ou outro perfil da equipe
    $stmtMedico = $pdo->prepare(
        "SELECT especialidade FROM medicos WHERE id = :id"
    );
    $stmtMedico->execute([':id' => $_SESSION['usuario_id']]);
    $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Descriptografa o sexo do paciente caso ele exista
$sexoDescriptografado = null;
if (!empty($paciente['sexo'])) {
    try {
        $sexoDescriptografado = decryptPatientSensitiveValue($paciente['sexo']);
    } catch (Throwable $e) {
        $sexoDescriptografado = null;
    }
}

echo json_encode([
    "success" => true,
    "authenticated" => true,
    "usuario" => [
        "id" => $_SESSION['usuario_id'],
        "nome" => $_SESSION['nome'],
        "email" => $_SESSION['email'],
        "perfil" => $perfil,
        "especialidade" => $medico['especialidade'] ?? null,
        "cpf" => $paciente['cpf'] ?? null,
        "telefone" => $paciente['telefone'] ?? null,
        "data" => $data_cadastro ?: null,
        "sexo" => $sexoDescriptografado,
        "data_nascimento" => $paciente['data_nascimento'] ?? null
    ]
]);