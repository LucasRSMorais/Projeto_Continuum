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

// Lê o código enviado no corpo JSON; o valor precisa pertencer ao desafio
// iniciado na sessão pelo fluxo de login, não a uma sessão autenticada existente.
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

// Garante que existe um desafio pendente. Sem ele, a verificação não pode
// ser associada com segurança a um usuário que já passou pela senha.
if (!isset($_SESSION['2fa_codigo'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Nenhum código de verificação disponível."
    ]);
    exit;
}

// O prazo é conferido antes do hash para rejeitar desafios vencidos e remover
// seus dados temporários da sessão.
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
        'Medicos',
        $_SERVER['REMOTE_ADDR'] ?? null,
   
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
        'Medicos',
        $_SERVER['REMOTE_ADDR'] ?? null,
       
        'INFO'
    );

 registrarLog(
        $pdo,
        $_SESSION['2fa_usuario_id'],
        "LOGIN_SUCESSO",
        "O usuário " . $_SESSION['2fa_nome'] . " realizou login com sucesso.",
        'Medicos',
        $_SERVER['REMOTE_ADDR'] ?? null,
     
        'INFO'
    );
 



// 2FA aprovado: rotaciona o identificador da sessão para separar a sessão
// pré-autenticada da sessão autenticada e reduzir risco de fixação de sessão.
session_regenerate_id(true);

// Copia os dados temporários antes de removê-los, pois serão usados para
// preencher a identidade, o e-mail e o perfil da sessão autenticada.
$usuarioId = $_SESSION['2fa_usuario_id'];
$nome = $_SESSION['2fa_nome'];
$email = $_SESSION['2fa_email'];
$perfil = $_SESSION['2fa_perfil'];

// Rotaciona novamente o identificador antes de concluir a transição do estado
// e define os campos que os endpoints autenticados usam para reconhecer o médico.
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

// Apaga o desafio e os dados pré-autenticados para impedir reutilização do código
// depois que a sessão autenticada já foi estabelecida.
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