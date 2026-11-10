<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/cors.php';
applyCorsHeaders();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None',
]);

session_start();

// Expira a sessão após 30 minutos sem atividade.
$tempoExpiracao = 30 * 60;
$ultimaAtividade = filter_var(
    $_SESSION['ultima_atividade'] ?? null,
    FILTER_VALIDATE_INT
);

if (
    $ultimaAtividade !== false
    && $ultimaAtividade !== null
    && time() - $ultimaAtividade > $tempoExpiracao
) {
    $_SESSION = [];
    session_destroy();

    http_response_code(401);
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Sessão expirada.',
    ]);
    exit;
}

// Valida o identificador antes de utilizá-lo em consultas ao banco.
$usuarioId = filter_var(
    $_SESSION['usuario_id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($usuarioId === false || $usuarioId === null || $usuarioId < 1) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Usuário não autenticado.',
    ]);
    exit;
}

$_SESSION['ultima_atividade'] = time();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/patient_data_encryption.php';

try {
    $stmtPaciente = $pdo->prepare(
        'SELECT cpf, telefone, data_nascimento, sexo, aceite_termo_uso
         FROM pacientes
         WHERE id = :id
         LIMIT 1'
    );
    $stmtPaciente->bindValue(':id', $usuarioId, PDO::PARAM_INT);
    $stmtPaciente->execute();
    $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

    $stmtEndereco = $pdo->prepare(
        "SELECT rua, cidade, estado, pais, cep
         FROM enderecos
         WHERE id_paciente = :id
           AND status = 'ativo'
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmtEndereco->bindValue(':id', $usuarioId, PDO::PARAM_INT);
    $stmtEndereco->execute();
    $endereco = $stmtEndereco->fetch(PDO::FETCH_ASSOC);

    $stmtConsentimento = $pdo->prepare(
        "SELECT data_hora
         FROM logs_auditoria
         WHERE id_usuario = :id
           AND acao_realizada = 'CADASTRO_PACIENTE'
           AND tabela_afetada = 'pacientes'
         ORDER BY data_hora ASC
         LIMIT 1"
    );
    $stmtConsentimento->bindValue(':id', $usuarioId, PDO::PARAM_INT);
    $stmtConsentimento->execute();
    $dataCadastro = $stmtConsentimento->fetchColumn();

    $sexo = null;
    if (is_array($paciente) && isset($paciente['sexo'])) {
        $sexo = decryptPatientSensitiveValue($paciente['sexo']);
    }

    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'usuario' => [
            'id' => $usuarioId,
            'nome' => $_SESSION['nome'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'cpf' => $paciente['cpf'] ?? null,
            'telefone' => $paciente['telefone'] ?? null,
            'dataNascimento' => $paciente['data_nascimento'] ?? null,
            'sexo' => $sexo,
            'endereco' => $endereco ?: null,
            'data' => $dataCadastro ?: null,
            'aceite_termo_uso' => $paciente['aceite_termo_uso'] ?? null,
        ],
    ]);
} catch (Throwable $error) {
    error_log('Session API failed: ' . $error->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Não foi possível verificar a sessão.',
    ]);
}