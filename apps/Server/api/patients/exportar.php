
<?php

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    header('Allow: GET, OPTIONS');

    echo json_encode([
        "success" => false,
        "message" => "Método não permitido."
    ]);
    exit;
}

// Verifica a autenticação do paciente
if (
    !isset($_SESSION['usuario_id'])
    || !is_numeric($_SESSION['usuario_id'])
    || ($_SESSION['2fa_verificado'] ?? false) !== true
    || ($_SESSION['tipo_usuario'] ?? '') !== 'paciente'
) {
    http_response_code(401);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        "success" => false,
        "message" => "Faça login novamente para exportar seus dados."
    ]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/patient_data_encryption.php";

$idPaciente = (int) $_SESSION['usuario_id'];

try {

    // Busca somente os dados da tabela pacientes
    $stmt = $pdo->prepare(
        "SELECT nome_completo, email, cpf, telefone,
                data_nascimento, sexo, etnia, aceite_termo_uso
         FROM pacientes
         WHERE id = :id
         LIMIT 1"
    );

    $stmt->execute([
        'id' => $idPaciente
    ]);

    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            "success" => false,
            "message" => "Paciente não encontrado."
        ]);
        exit;
    }

    // Descriptografa os campos protegidos
    $sexo = $paciente['sexo'] !== null
        ? decryptPatientSensitiveValue($paciente['sexo'])
        : 'Não informado';

    $etnia = $paciente['etnia'] !== null
        ? decryptPatientSensitiveValue($paciente['etnia'])
        : 'Não informado';

    // Organiza os dados em formato de texto
    $conteudo = "========================================\n";
    $conteudo .= "       DADOS DO PACIENTE - CONTINUUM\n";
    $conteudo .= "========================================\n\n";

    $conteudo .= "Data da exportação: " . gmdate('d/m/Y H:i:s') . " UTC\n\n";

    $conteudo .= "DADOS PESSOAIS\n";
    $conteudo .= "----------------------------------------\n";

    $conteudo .= "Nome completo: " . ($paciente['nome_completo'] ?? 'Não informado') . "\n";
    $conteudo .= "E-mail: " . ($paciente['email'] ?? 'Não informado') . "\n";
    $conteudo .= "CPF: " . ($paciente['cpf'] ?? 'Não informado') . "\n";
    $conteudo .= "Telefone: " . ($paciente['telefone'] ?? 'Não informado') . "\n";
    $conteudo .= "Data de nascimento: " . ($paciente['data_nascimento'] ?? 'Não informado') . "\n";
    $conteudo .= "Sexo: " . $sexo . "\n";
    $conteudo .= "Etnia: " . $etnia . "\n\n";

    $conteudo .= "TERMOS DE USO\n";
    $conteudo .= "----------------------------------------\n";
    $conteudo .= "Aceite dos termos: " . $paciente['aceite_termo_uso'] . "\n\n";

    $conteudo .= "========================================\n";
    $conteudo .= "       FIM DA EXPORTAÇÃO\n";
    $conteudo .= "========================================\n";

    
    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="meus-dados-continuum.txt"');
    header('Cache-Control: no-store, private');

    echo "\xEF\xBB\xBF" . $conteudo;

} catch (Throwable $error) {

    error_log('Patient data export failed: ' . $error->getMessage());

    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode([
        "success" => false,
        "message" => "Não foi possível exportar seus dados no momento."
    ]);
}
