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
    $dados = json_decode(file_get_contents("php://input"), true) ?? [];
    $nome = trim($dados["nome_completo"] ?? $dados["nome"] ?? "");
    $email = trim($dados["email"] ?? "");
    $senha = $dados["senha"] ?? "";
    $cargo = trim($dados["cargo"] ?? "medico");
    $crm   = preg_replace('/\D/', '', $dados["crm"] ?? "");
    $crmUf = strtoupper(trim($dados["crm_uf"] ?? ""));

    if ($nome === "" || $email === "" || $senha === "" || $crm === "" || $crmUf === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nome completo, e-mail, senha, CRM e UF do CRM são obrigatórios."
        ]);
        exit;
    }

    if (!preg_match('/^\d{1,6}$/', $crm)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "CRM inválido."]);
        exit;
    }

    if (!preg_match('/^[A-Z]{2}$/', $crmUf)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "UF do CRM inválida."]);
        exit;
    }

    // Monta o registro a partir do CRM e da UF, ex.: "123456/SP".
    // Assim a mesma numeração em estados diferentes não conta como duplicada.
    $registro = $crm . "/" . $crmUf;

    // Valida se os campos obrigatórios vieram preenchidos.
    if ($nome === "" || $email === "" || $senha === "" || $registro === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Nome completo, e-mail, senha e registro são obrigatórios."
        ]);
        exit;
    }

    // Verifica se o e-mail ou registro já existem antes de inserir um novo médico.
    $consulta = $pdo->prepare(
        "SELECT id FROM medicos WHERE email = :email OR (crm_numero = :crm_numero AND crm_uf = :crm_uf)"
    );
    $consulta->execute([
        "email" => $email,
        "crm_numero" => $crm,
        "crm_uf" => $crmUf
    ]);

    if ($consulta->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este e-mail ou registro já está cadastrado."
        ]);
        exit;
    }

    // Valida o formato do CRM e da UF do CRM.
    if (!preg_match('/^\d{1,6}$/', $crm)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'CRM inválido.'
        ]);
        exit;
    }

    if (!preg_match('/^[A-Z]{2}$/', $crmUf)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'UF do CRM inválida.'
        ]);
        exit;
    }


    // Criptografa a senha com Argon2id.
    // Isso é importante para proteger a senha mesmo se o banco for vazado.
    // Requisito 1.1 - criptografia da senha em hash
    // Requisito 1.2 - parâmetro de custo
    // Requisito 1.3 - salt único gerado automaticamente pelo PHP
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

    // Insere o médico no banco conforme o modelo ER definido.
    $sql = "
        INSERT INTO medicos
        (nome_completo, email, senha_hash, crm_numero, crm_uf, cargo, status)
        VALUES
        (:nome_completo, :email, :senha_hash, :crm_numero, :crm_uf, :cargo, :status)
    ";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        "nome_completo" => $nome,
        "email" => $email,
        "senha_hash" => $senhaHash,
        "crm_numero" => $crm,
        "crm_uf" => $crmUf,
        "cargo" => $cargo,
        "status" => true
    ]);

    $usuarioId = $pdo->lastInsertId();

    // Após a criação do cadastro, o sistema registra o evento de sucesso.
    // Isso ajuda a manter um histórico de usuários criados e facilita auditoria operacional.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
        $usuarioId ?: null,
        "CADASTRO_SUCESSO",
        "Usuário " . $nome . " foi cadastrado com sucesso.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Usuário cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    // 23000 = violação de integridade (chave duplicada, UNIQUE etc.)
    // 1062 = código específico do MySQL para "Duplicate entry"
    $codigoMysql = $e->errorInfo[1] ?? null;

    if ($e->getCode() === '23000' && $codigoMysql === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este e-mail ou registro já está cadastrado."
        ]);
        exit;
    }

    // Qualquer outro erro de banco continua sendo 500.
    error_log("Erro no cadastro: ". $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" =>  $e->getMessage()
    ]);

} catch (Exception $e) {
    // Erro geral de execução.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro interno."
    ]);
}