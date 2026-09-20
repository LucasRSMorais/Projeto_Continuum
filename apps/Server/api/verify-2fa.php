<?php

// Página de validação do código de autenticação em duas etapas (2FA).
// Após o login, o frontend envia o código gerado na etapa anterior e esta API confirma.
//Corrigido
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

// Esta API confirma o código enviado após o login e conclui a autenticação.
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

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

// Se o código não vier, retorna erro 400.
// Essa validação impede que a API aceite requisições vazias ou malformadas.
if (!$codigo) {
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
// Se o valor estiver errado, o evento é registrado como falha de verificação em duas etapas.
if (!password_verify($codigo, $_SESSION['2fa_codigo'])) {
     registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "2FA_FALHA",
        "O usuário " . $_SESSION['2fa_nome'] . " informou um código de verificação inválido.",
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
// Em caso de sucesso, o sistema registra o evento de verificação e o login concluído.
// Isso permite auditar tanto a aprovação do 2FA quanto a autenticação final do usuário.
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

 registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "LOGIN_SUCESSO",
        "O usuário " . $_SESSION['2fa_nome'] . " realizou login com sucesso.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );
 



// 2FA aprovado: o usuário passa a ser autenticado de verdade.
session_regenerate_id(true);

// Recupera os dados temporários antes de criar a sessão autenticada.
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

$atualizaUltimoAcesso = $pdo->prepare(
    "UPDATE medicos SET ultimo_acesso = NOW() WHERE id = :id"
);
$atualizaUltimoAcesso->execute(['id' => $usuarioId]);

// Remove os dados temporários do 2FA após a autenticação bem-sucedida.
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