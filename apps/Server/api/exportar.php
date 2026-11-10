<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

if (!isset($_SESSION['usuario_id']) || !is_numeric($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão inválida.'
    ]);
    exit;
}

require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/patient_data_encryption.php";

try {
    $json = file_get_contents('php://input');
    $dados = $json !== false ? json_decode($json, true) : null;
    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Corpo JSON inválido.'
        ]);
        exit;
    }

    $email = trim((string) ($dados['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email inválido.'
        ]);
        exit;
    }

    $usuarioId = (int) $_SESSION['usuario_id'];

    $sql = "
        SELECT nome_completo, telefone, email, sexo, etnia, data_nascimento
        FROM pacientes
        WHERE email = :email
          AND id = :usuario_id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':usuario_id' => $usuarioId
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Paciente não encontrado.'
        ]);
        exit;
    }

    $usuario['sexo'] = decryptPatientSensitiveValue($usuario['sexo']);
    $usuario['etnia'] = decryptPatientSensitiveValue($usuario['etnia']);

    header("Content-Type: text/plain; charset=UTF-8");
    header("Content-Disposition: attachment; filename=exportacao.txt");

    echo "Dados do TITULAR\n";
    echo "Nome: {$usuario['nome_completo']}\n";
    echo "Email: {$usuario['email']}\n";
    echo "Telefone: {$usuario['telefone']}\n";
    echo "Sexo: {$usuario['sexo']}\n";
    echo "Etnia: {$usuario['etnia']}\n";
    echo "Data de Nascimento: {$usuario['data_nascimento']}\n";

} catch (PDOException $e) {
    error_log('Exportar paciente failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao acessar o banco de dados.'
    ]);
} catch (RuntimeException $e) {
    error_log('Export patient decryption failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível acessar os dados protegidos do paciente.'
    ]);
}