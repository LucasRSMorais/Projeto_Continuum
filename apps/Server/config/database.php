<?php

// Arquivo responsável por conectar a API ao banco de dados MySQL.
// Ele cria o objeto $pdo, que será reutilizado pelas páginas que fazem consultas.

$host = "localhost";
$database = "continuum";
$username = "root";
$password = "";

try {
    // Cria a conexão com o banco usando PDO.
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );

    // Define que erros de banco devem lançar exceções para tratamento mais fácil.
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    // Se a conexão falhar, a API retorna erro 500 em JSON.
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erro ao conectar ao banco de dados."
    ]);

    exit;
}