<?php

// Página de cadastro de novos usuários.
// Recebe dados do frontend, valida e salva o usuário no banco com senha criptografada.
require_once __DIR__ . '/../vendor/autoload.php';
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/patient_data_encryption.php";

try {
    // Lê os dados enviados em JSON pelo frontend.
    $dados = json_decode(file_get_contents("php://input"), true);
    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Corpo da requisição inválido."
        ]);
        exit;
    }

    $nome = trim($dados["nome_completo"] ?? $dados["nome"] ?? "");
    $email = trim($dados["email"] ?? "");
    $cpf = trim($dados["cpf"] ?? "");
 
    $dataNascimento = trim($dados["data_nascimento"] ?? "");
    $sexo = trim($dados["sexo"] ?? "");
    $etnia = trim($dados["etnia"] ?? "");
    $telefone = trim($dados["telefone"] ?? "");
    $aceiteTermo = strtolower(trim((string) ($dados["aceite_termo"] ?? "nao")));
  
    $endereco = trim($dados["endereco"] ?? "");
    $fkMedicoId = isset($dados["fk_medic_id"]) ? (int) $dados["fk_medic_id"] : null;

    // Valida os campos obrigatórios do cadastro do paciente conforme o ER.
    if ($nome === "" || $email === "" || $cpf === "" || $dataNascimento === "" || $sexo === "" || $telefone === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nome completo, e-mail, CPF, data de nascimento, sexo, telefone e endereço são obrigatórios."
        ]);
        exit;
    }

    $sexoPermitido = ['masculino', 'feminino'];
    $etniasPermitidas = [
        'branca',
        'negra',
        'parda',
        'amarela',
        'indigena',
        'outra',
        'nao_informar',
    ];
    $etnia = $etnia !== '' ? $etnia : 'nao_informar';

    if (!in_array($sexo, $sexoPermitido, true) || !in_array($etnia, $etniasPermitidas, true)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Selecione uma opção válida para sexo e etnia."
        ]);
        exit;
    }

    try {
        getPatientDataEncryptionKey();
    } catch (RuntimeException $e) {
        error_log('Patient data encryption is not configured: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "A criptografia dos dados do paciente não está configurada. Configure PATIENT_DATA_ENCRYPTION_KEY no servidor."
        ]);
        exit;
    }

    $columns = $pdo->query(
        "SHOW COLUMNS FROM pacientes WHERE Field IN ('sexo', 'etnia')"
    )->fetchAll(PDO::FETCH_ASSOC);
    $columnTypes = [];
    foreach ($columns as $column) {
        $columnTypes[$column['Field']] = $column['Type'];
    }
    foreach (['sexo', 'etnia'] as $field) {
        if (
            !isset($columnTypes[$field]) ||
            !preg_match('/^varchar\((\d+)\)$/i', $columnTypes[$field], $matches) ||
            (int) $matches[1] < 512
        ) {
            $sexoType = $columnTypes['sexo'] ?? 'coluna ausente';
            $etniaType = $columnTypes['etnia'] ?? 'coluna ausente';
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "O banco conectado ainda não está preparado para os dados criptografados (sexo: {$sexoType}; etnia: {$etniaType}). Aplique a migração na mesma base configurada em DB_NAME no servidor."
            ]);
            exit;
        }
    }

    $sexoCriptografado = encryptPatientSensitiveValue($sexo);
    $etniaCriptografada = encryptPatientSensitiveValue($etnia);

        $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=';

        $senha = '';
        for ($i = 0; $i < 20; $i++) {
            $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }




            $resendAPI = getenv('RESEND_API_KEY');



if (!$resendAPI){


http_response_code(500);

echo json_encode([
    "success" => false,
    "message" => "Configurando do resend não encontrada"
]);


exit;

}

try {
$resend = Resend::client($resendAPI);
$senhaHtml = htmlspecialchars($senha, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$resultado = $resend->emails->send([
'from' => 'Continuum <seguranca@projcontinnum.com.br>',

'to'=> [$email],
"subject"=> 'Senha temporária',
'html' => "
<h1> Senha temporaria  </h1>
<p>  Sua senha temporaria de login é   </p>

<h2> Verificação de segurança  </h2>

<h2>  Senha :   ($senhaHtml )  </h2>
"
]);


}catch (Exception $emailError){

error_log(

'falha ao enviar o codigo 2FA'
. $emailError->getMessage()

);

http_response_code(500);

echo json_encode([
    "success"=>false,
    "message"=> "Não foi possível enviar o e-mail de cadastro."
]);


exit ;
}

    // Verifica se o e-mail já existe antes de inserir um novo paciente.
    $consulta = $pdo->prepare(
        "SELECT id FROM pacientes WHERE email = :email"
    );

    $consulta->execute([
        "email" => $email
    ]);

    if ($consulta->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este e-mail já está cadastrado."
        ]);
        exit;
    }


    // Valida o valor do consentimento do termo.
    if (!in_array($aceiteTermo, ['sim', 'nao'], true)) {
        $aceiteTermo = 'nao';
    }

    $senhaHash = password_hash(
        $senha,
        PASSWORD_ARGON2ID,
        [
            "memory_cost" => 65536,
            "time_cost" => 3,
            "threads" => 2
        ]
    );

    if ($senhaHash === false) {
        throw new Exception("Não foi possível gerar o hash da senha.");
    }

    // Insere os dados do paciente no banco de acordo com o ER definido.
    $sql = "
        INSERT INTO pacientes
        (nome_completo, email, cpf, data_nascimento, sexo, etnia,  senha_hash, telefone,  aceite_termo_uso)
        VALUES
        (:nome_completo, :email, :cpf, :data_nascimento, :sexo, :etnia, :senha, :telefone, :aceite_termo)
    ";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        "nome_completo" => $nome,
        "email" => $email,
        "cpf" => $cpf,
        "data_nascimento" => $dataNascimento,
        "sexo" => $sexoCriptografado,
        "etnia" => $etniaCriptografada,
        "senha" => $senhaHash,
        "telefone" => $telefone !== "" ? $telefone : null,
        "aceite_termo" => $aceiteTermo,
       
        
        //"fk_medic_id" => $fkMedicoId,
        
    ]);


    // Grava no log a criação do cadastro do paciente como evento de auditoria.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
        NULL , // Nenhum usuário logado no momento do cadastro do paciente.
        "CADASTRO_PACIENTE",
        "Paciente " . $nome . " foi cadastrado com sucesso." ,
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        'INFO'
    );

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Cadastro concluído. Senha temporária enviada para seu e-mail."
    ]);

} catch (PDOException $e) {
    // Erro relacionado ao banco.
    error_log('Patient registration database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao realizar cadastro no banco de dados."
    ]);

} catch (Exception $e) {
    // Erro geral de execução.
    error_log('Patient registration failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao realizar cadastro."
    ]);
}
