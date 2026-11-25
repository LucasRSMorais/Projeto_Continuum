<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/patient_data_encryption.php";

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

$usuarioId = $_SESSION['usuario_id'];
$perfil = $_SESSION['perfil'] ?? '';
$paciente = [];
$medico = [];
$data_cadastro = null;

// 1. Tenta buscar primeiro na tabela de pacientes
$stmtPaciente = $pdo->prepare(
    "SELECT cpf, telefone, sexo, data_nascimento FROM pacientes WHERE id = :id"
);
$stmtPaciente->execute([':id' => $usuarioId]);
$paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

if ($paciente) {
    // É paciente! Busca a data do cadastro nos logs
    $stmtConsetimento = $pdo->prepare(
        "SELECT data_hora FROM logs_auditoria WHERE id_usuario = :id AND acao_realizada = 'CADASTRO_PACIENTE' AND tabela_afetada = 'pacientes' ORDER BY data_hora ASC LIMIT 1"
    );
    $stmtConsetimento->execute([':id' => $usuarioId]);
    $data_cadastro = $stmtConsetimento->fetchColumn();
} else {
    // Se não está em pacientes, busca na tabela de médicos
    $stmtMedico = $pdo->prepare(
        "SELECT especialidade FROM medicos WHERE id = :id"
    );
    $stmtMedico->execute([':id' => $usuarioId]);
    $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC) ?: [];
}

// Descriptografa o sexo do paciente se ele existir
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
        "id" => $usuarioId,
        "nome" => $_SESSION['nome'] ?? ($paciente ? 'Paciente' : 'Usuário'),
        "email" => $_SESSION['email'] ?? '',
        "perfil" => $perfil,
        "especialidade" => $medico['especialidade'] ?? null,
        "cpf" => $paciente['cpf'] ?? null,
        "telefone" => $paciente['telefone'] ?? null,
        "data" => $data_cadastro ?: null,
        "sexo" => $sexoDescriptografado,
        "data_nascimento" => $paciente['data_nascimento'] ?? null
    ]
]);