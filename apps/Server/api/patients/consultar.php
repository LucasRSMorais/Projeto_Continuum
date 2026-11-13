<?php
header("Content-Type: application/json; charset=UTF-8");

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/patient_data_encryption.php";

if (!isset($_SESSION['usuario_id']) || !is_numeric($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Sessão inválida."
    ]);
    exit;
}

$idPaciente = (int) $_SESSION['usuario_id'];

try {
    $dados = json_decode(file_get_contents("php://input"), true);
    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Corpo da requisição inválido."
        ]);
        exit;
    }

    $sql = "
        SELECT nome_completo, email, telefone, sexo, etnia, data_nascimento, endereco
        FROM pacientes
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $idPaciente
    ]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Usuário não encontrado."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Dados do usuário consultados com sucesso.",
        "usuario" => [
            "nome_completo" => $usuario["nome_completo"],
            "email" => $usuario["email"],
            "telefone" => $usuario["telefone"],
            "sexo" => isset($usuario["sexo"]) ? decryptPatientSensitiveValue($usuario["sexo"]) : null,
            "etnia" => isset($usuario["etnia"]) ? decryptPatientSensitiveValue($usuario["etnia"]) : null,
            "data_nascimento" => $usuario["data_nascimento"],
            "endereco" => $usuario["endereco"] ?? null
        ]
    ]);

} catch (PDOException $e) {
    error_log('Consultar paciente failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao consultar o paciente."
    ]);
} catch (Throwable $e) {
    error_log('Consultar paciente exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível consultar os dados."
    ]);
}