<?php
header("Content-Type: text/plain; charset=UTF-8");

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/patient_data_encryption.php";

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
    $sql = "
        SELECT nome_completo, telefone, email, sexo, etnia, data_nascimento
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
        echo "Paciente não encontrado.\n";
        exit;
    }

    $sexo = isset($usuario['sexo']) ? decryptPatientSensitiveValue($usuario['sexo']) : null;
    $etnia = isset($usuario['etnia']) ? decryptPatientSensitiveValue($usuario['etnia']) : null;

    header("Content-Disposition: attachment; filename=exportacao.txt");
    echo "Dados do TITULAR\n";
    echo "Nome: {$usuario['nome_completo']}\n";
    echo "Email: {$usuario['email']}\n";
    echo "Telefone: {$usuario['telefone']}\n";
    echo "Sexo: {$sexo}\n";
    echo "Etnia: {$etnia}\n";
    echo "Data de Nascimento: {$usuario['data_nascimento']}\n";

} catch (PDOException $e) {
    error_log('Exportar paciente failed: ' . $e->getMessage());
    http_response_code(500);
    echo "Erro ao exportar os dados.\n";
} catch (Throwable $e) {
    error_log('Exportar paciente exception: ' . $e->getMessage());
    http_response_code(500);
    echo "Não foi possível exportar os dados.\n";
}