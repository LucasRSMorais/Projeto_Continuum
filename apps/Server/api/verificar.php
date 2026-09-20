<?php

// Página de validação do código de autenticação em duas etapas (2FA).
//Corrigido

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

require_once __DIR__ . "/../config/cors.php";
require_once __DIR__ . "/registrarLog.php";
require_once __DIR__ . "/../config/database.php";



if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Lê o código enviado no corpo da requisição em JSON.
$data = json_decode(file_get_contents("php://input"), true);
$codigo = trim($data['codigo'] ?? '');

// Registra o início do processo de recuperação de senha após a validação do primeiro fator.
// Esse evento ajuda a rastrear que o usuário entrou no fluxo de alteração de credencial.
 registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "RECUPERACAO_INICIADA",
        "O usuário " . $_SESSION['2fa_nome'] . " começou a recuperar a senha.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );

// Se o código não vier, retorna erro 400.
if ($codigo === '') {
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
// Caso o valor esteja incorreto, um evento de falha de 2FA é gravado para auditoria.
if (!password_verify($codigo, $_SESSION['2fa_codigo'])) {
    registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "2FA_FALHA",
        "O usuário " . $_SESSION['2fa_nome'] . " informou um código de verificação incorretamente.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'SECURITY'
    );


    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Código inválido."
    ]);
    exit;
}

// Quando o código estiver correto, o sistema grava a confirmação da etapa de verificação.
registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "2FA_SUCESSO",
        "O usuário " . $_SESSION['2fa_nome'] . " informou um código de verificação corretamente.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );

 




// 2FA aprovado: o usuário passa a ser autenticado de verdade.
session_regenerate_id(true);

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