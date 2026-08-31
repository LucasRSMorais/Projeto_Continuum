<?php

// Página responsável por encerrar a sessão do usuário.
// Quando o frontend chama este endpoint, a sessão é limpa e o cookie é invalidado.

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Zera todos os dados da sessão atual.
$_SESSION = [];

// Se estiver usando cookies de sessão, remove o cookie do navegador.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão no servidor.
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logout realizado com sucesso."
]);