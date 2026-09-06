<?php

// Página responsável por verificar se o usuário está autenticado.
// Também controla a expiração da sessão após 30 minutos de inatividade.

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

$tempoExpiracao = 30 * 60; // Expira a sessão depois de 30 minutos sem atividade.

// Se o tempo de inatividade excedeu o limite, encerra a sessão.
if (
    isset($_SESSION['ultima_atividade']) &&
    time() - $_SESSION['ultima_atividade'] > $tempoExpiracao
) {
    $_SESSION = [];
    session_destroy();
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Sessão expirada."
    ]);
    exit;
}

// Atualiza a marca de última atividade para manter a sessão ativa.
$_SESSION['ultima_atividade'] = time();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Se o usuário ainda não estiver autenticado, responde 401.
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Usuário não autenticado."
    ]);
    exit;
}

// Se estiver autenticado, retorna os dados públicos do usuário.
echo json_encode([
    "success" => true,
    "authenticated" => true,
    "usuario" => [
        "id" => $_SESSION['usuario_id'],
        "nome" => $_SESSION['nome'],
        "email" => $_SESSION['email'],
        "perfil" => $_SESSION['perfil']
    ]
]);