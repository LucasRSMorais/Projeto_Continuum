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
    if ($nome === "" || $email === "" || $senha === "" || $dataNascimento === "" || $sexo === "" || $endereco === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nome completo, e-mail, senha, data de nascimento, sexo e endereço são obrigatórios."
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
            "time_cost" => 4,
            "threads" => 2
        ]
    );

    if ($senhaHash === false) {
        throw new Exception("Não foi possível gerar o hash da senha.");
    }

    // Insere os dados do paciente no banco de acordo com o ER definido.
    $sql = "
        INSERT INTO pacientes
        (nome_completo, email, data_nascimento, sexo, etnia, senha, telefone, aceite_termo, alergias, endereco, fk_medic_id, status)
        VALUES
        (:nome_completo, :email, :data_nascimento, :sexo, :etnia, :senha, :telefone, :aceite_termo, :alergias, :endereco, :fk_medic_id, :status)
    ";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        "nome_completo" => $nome,
        "email" => $email,
        "data_nascimento" => $dataNascimento,
        "sexo" => $sexo,
        "etnia" => $etnia !== "" ? $etnia : null,
        "senha" => $senhaHash,
        "telefone" => $telefone !== "" ? $telefone : null,
        "aceite_termo" => $aceiteTermo,
        "alergias" => $alergias !== "" ? $alergias : null,
        "endereco" => $endereco,
        "fk_medic_id" => $fkMedicoId,
        "status" => true
    ]);

    // Grava no log a criação do cadastro do paciente como evento de auditoria.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
        $pdo->lastInsertId(),
        "CADASTRO_PACIENTE_SUCESSO",
        "Paciente " . $nome . " foi cadastrado com sucesso.",
        'pacientes',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
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