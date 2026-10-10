<?php

// Página de cadastro de novos usuários.
// Recebe dados do frontend, valida e salva o usuário no banco com senha criptografada.

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

try {
    // Lê os dados enviados em JSON pelo frontend.
    $dados = json_decode(file_get_contents("php://input"), true);
    $nome = trim($dados["nome_completo"] ?? $dados["nome"] ?? "");
    $email = trim($dados["email"] ?? "");
    $cpf = trim($dados["cpf"] ?? "");
    $senha = $dados["senha"] ?? "";
    $dataNascimento = trim($dados["data_nascimento"] ?? "");
    $sexo = trim($dados["sexo"] ?? "");
    $etnia = trim($dados["etnia"] ?? "");
    $telefone = trim($dados["telefone"] ?? "");
    $aceiteTermo = strtolower(trim((string) ($dados["aceite_termo"] ?? "nao")));
    $alergias = trim($dados["alergias"] ?? "");
    $endereco = trim($dados["endereco"] ?? "");
    $fkMedicoId = isset($dados["fk_medic_id"]) ? (int) $dados["fk_medic_id"] : null;

    // Valida os campos obrigatórios do cadastro do paciente conforme o ER.
    if ($nome === "" || $email === "" || $cpf === "" || $senha === "" || $dataNascimento === "" || $sexo === "" || $telefone === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nome completo, e-mail, CPF, senha, data de nascimento, sexo, telefone e endereço são obrigatórios."
        ]);
        exit;
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
        (nome_completo, email, cpf, data_nascimento, sexo, etnia,  senha_hash, telefone,  aceite_termo_uso, alergias)
        VALUES
        (:nome_completo, :email, :cpf, :data_nascimento, :sexo, :etnia, :senha, :telefone, :aceite_termo, :alergias)
    ";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        "nome_completo" => $nome,
        "email" => $email,
        "cpf" => $cpf,
        "data_nascimento" => $dataNascimento,
        "sexo" => $sexo,
        "etnia" => $etnia !== "" ? $etnia : null,
        "senha" => $senhaHash,
        "telefone" => $telefone !== "" ? $telefone : null,
        "aceite_termo" => $aceiteTermo,
        "alergias" => $alergias !== "" ? $alergias : null,
        
        //"fk_medic_id" => $fkMedicoId,
        
    ]);

    // Grava no log a criação do cadastro do paciente como evento de auditoria.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
        null, // Nenhum usuário logado no momento do cadastro do paciente.
        "CADASTRO_PACIENTE",
        "Paciente " . $nome . " foi cadastrado com sucesso." ,
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        'INFO'
    );

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Dados  do paciente cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    // Erro relacionado ao banco.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

} catch (Exception $e) {
    // Erro geral de execução.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao realizar cadastro."
    ]);
}