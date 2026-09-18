<?php

// Página de validação do código de autenticação em duas etapas (2FA).


session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Esta API transforma a sessão temporária do 2FA em uma sessão autenticada.
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Lê o código enviado no corpo da requisição em JSON.
$data = json_decode(file_get_contents("php://input"), true);
$codigo = trim($data['codigo'] ?? '');

// Se o código não vier, retorna erro 400.
if (!$codigo==='') {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe o código de verificação."
    ]);
    exit;
}

// Garante que realmente existe um código pendente na sessão.
if (!isset($_SESSION['2fa_codigo'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Nenhum código de verificação disponível."
    ]);
    exit;
}

// Verifica se o código expirou.
if (time() > $_SESSION['2fa_expira']) {
    unset(
        $_SESSION['2fa_codigo'],
        $_SESSION['2fa_expira']
    );

    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Código expirado."
    ]);
    exit;
}

// Compara o código informado com o hash salvo na sessão.
if (!password_verify($codigo, $_SESSION['2fa_codigo'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Código inválido."
    ]);
    exit;
}


// 2FA aprovado: o usuário passa a ser autenticado de verdade.
session_regenerate_id(true);

// Copia os dados temporários antes de promover a sessão.
$usuarioId = $_SESSION['2fa_usuario_id'];
$nome = $_SESSION['2fa_nome'];
$email = $_SESSION['2fa_email'];
$perfil = $_SESSION['2fa_perfil'];

// Agora transforma a sessão temporária em sessão autenticada.
session_regenerate_id(true);

$_SESSION['usuario_id'] = $usuarioId;
$_SESSION['nome'] = $nome;
$_SESSION['email'] = $email;
$_SESSION['perfil'] = $perfil;
$_SESSION['ultima_atividade'] = time();

// Remove os dados temporários do 2FA após a autenticação bem-sucedida.
$_SESSION['2fa_verificado'] = true ;
unset(
    $_SESSION['2fa_usuario_id'],
    $_SESSION['2fa_nome'],
    $_SESSION['2fa_email'],
    $_SESSION['2fa_perfil'],
    $_SESSION['2fa_codigo'],
    $_SESSION['2fa_expira']
);

echo json_encode([
    "success" => true,
    "message" => "Autenticação concluída com sucesso."
]);