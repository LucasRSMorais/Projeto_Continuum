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

if (
    !isset($_SESSION['usuario_id'], $_SESSION['email'])
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

$nome = trim(is_string($dados['nome'] ?? null) ? $dados['nome'] : '');
$email = strtolower(trim(is_string($dados['email'] ?? null) ? $dados['email'] : ''));
$telefone = trim(is_string($dados['telefone'] ?? null) ? $dados['telefone'] : '');
$sexo = $dados['sexo'] ?? null;
$digitosTelefone = preg_replace('/\D/', '', $telefone);

if ($nome === '' || preg_match('/^.{1,150}$/u', $nome) !== 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um nome válido com até 150 caracteres."
    ]);
    exit;
}

if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um email válido."
    ]);
    exit;
}

if (
    $telefone === ''
    || strlen($telefone) > 20
    || !preg_match('/^\+?[0-9\s().-]+$/D', $telefone)
    || $digitosTelefone === null
    || strlen($digitosTelefone) < 10
    || strlen($digitosTelefone) > 15
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um telefone válido, com 10 a 15 dígitos."
    ]);
    exit;
}

if (!in_array($sexo, ['masculino', 'feminino', 'nao_informado'], true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Selecione uma opção válida para sexo."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../config/database.php";
    require_once __DIR__ . "/../config/patient_data_encryption.php";
    require_once __DIR__ . "/registrarLog.php";

    $stmt = $pdo->prepare(
        "SELECT id, nome_completo, email, telefone, sexo
         FROM pacientes
         WHERE id = :id"
    );
    $stmt->execute([
        "id" => $_SESSION['usuario_id']
    ]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente || !hash_equals((string) $paciente['email'], (string) $_SESSION['email'])) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Não foi possível confirmar a sessão do paciente."
        ]);
        exit;
    }

    $camposAlterados = [];
    if ($paciente['nome_completo'] !== $nome) {
        $camposAlterados[] = "Nome completo";
    }
    if ($paciente['email'] !== $email) {
        $camposAlterados[] = "Email";
    }
    if ($paciente['telefone'] !== $telefone) {
        $camposAlterados[] = "Telefone";
    }
    if (decryptPatientSensitiveValue($paciente['sexo']) !== $sexo) {
        $camposAlterados[] = "Sexo";
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM pacientes WHERE email = :email AND id <> :id LIMIT 1"
    );
    $stmt->execute([
        "email" => $email,
        "id" => $_SESSION['usuario_id']
    ]);
    if ($stmt->fetchColumn()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este email já está sendo usado por outra conta."
        ]);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "UPDATE pacientes
         SET nome_completo = :nome, email = :email, telefone = :telefone, sexo = :sexo
         WHERE id = :id"
    );
    $stmt->execute([
        "nome" => $nome,
        "email" => $email,
        "telefone" => $telefone,
        "sexo" => encryptPatientSensitiveValue($sexo),
        "id" => $_SESSION['usuario_id']
    ]);

    if ($camposAlterados !== []) {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'],
            "PERFIL_ATUALIZADO",
            "Campos alterados: " . implode(", ", $camposAlterados) . ".",
            "pacientes",
            $_SERVER['REMOTE_ADDR'] ?? null,
            "INFO"
        );
    }

    $pdo->commit();

    $_SESSION['nome'] = $nome;
    $_SESSION['email'] = $email;

    echo json_encode([
        "success" => true,
        "message" => "Perfil atualizado com sucesso.",
        "usuario" => [
            "nome" => $nome,
            "email" => $email,
            "telefone" => $telefone,
            "sexo" => $sexo
        ]
    ]);
} catch (PDOException $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (($error->errorInfo[1] ?? null) === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este email já está sendo usado por outra conta."
        ]);
        exit;
    }

    error_log("Falha ao atualizar perfil do paciente: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível atualizar o perfil no momento."
    ]);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Falha ao atualizar perfil do paciente: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível atualizar o perfil no momento."
    ]);
}
