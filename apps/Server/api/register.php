<?php

// Página de cadastro de novos usuários.
// Recebe dados do frontend, valida e salva o usuário no banco com senha criptografada.

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

try {
    // Lê os dados enviados em JSON pelo frontend.
    $dados = json_decode(file_get_contents("php://input"), true);
    $nome = trim($dados["nome"] ?? "");
    $email = trim($dados["email"] ?? "");
    $senha = $dados["senha"] ?? "";
    $perfil = $dados["perfil"] ?? "";

    // Valida se os campos obrigatórios vieram preenchidos.
    if ($nome === "" || $email === "" || $senha === "" || $perfil === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Todos os campos são obrigatórios."
        ]);
        exit;
    }

    // Verifica se o email já existe antes de inserir um novo usuário.
    $consulta = $pdo->prepare(
        "SELECT id FROM usuarios WHERE email = :email"
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

    // Insere o usuário no banco.
    $sql = "
        INSERT INTO usuarios
        (nome, email, senha_hash, perfil)
        VALUES
        (:nome, :email, :senha_hash, :perfil)
    ";

    $consulta = $pdo->prepare($sql);
    $consulta->execute([
        "nome" => $nome,
        "email" => $email,
        "senha_hash" => $senhaHash,
        "perfil" => $perfil
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Usuário cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    // Erro relacionado ao banco.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao realizar cadastro."
    ]);

} catch (Exception $e) {
    // Erro geral de execução.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro interno."
    ]);
}