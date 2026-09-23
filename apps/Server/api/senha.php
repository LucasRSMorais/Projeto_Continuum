<?php

// Atualiza a senha depois que o usuário conclui a verificação em duas etapas.

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Define a política de resposta e permite o uso da sessão pelo frontend.
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Importa a conexão com o banco de dados.
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/registrarLog.php";
// Importa a conexão do sistema.log


if (

!isset ($_SESSION['2fa_verificado']) || $_SESSION ['2fa_verificado'] !== true



){
http_response_code(403);

echo json_encode([
"success"=> false ,
"message"=> "Não foi confirmado o codigo de verificação"
]);
exit ;


}


// Garante que a sessão contém um usuário identificado para atualizar.
if (!isset($_SESSION['usuario_id'])){
http_response_code(401);

echo json_encode([
"success"=> false,
"message"=> "Usuário não identificado"
]);
exit ;


}
$usuario = $pdo -> prepare (

"SELECT nome_completo FROM medicos WHERE id = :id"

);

$usuario-> execute([

":id" => $_SESSION['usuario_id']


]);

$nomeUsuario = $usuario ->fetch(PDO::FETCH_ASSOC);

try {
$dados = json_decode(
file_get_contents("php://input"),
true
);

$SenhaNova = trim($dados["nova_senha"] ?? "");
// Recusa a atualização quando a nova senha não foi informada.
if ($SenhaNova === ""){

http_response_code(400);

echo json_encode([
"success"=> false ,
"message"=> "A senha esta em branco."

]);
exit ;
}



// Aplica o tamanho mínimo definido pela regra de negócio.
// Se a senha nova for inválida, o sistema registra a tentativa para manter o histórico de ações.
if (strlen($SenhaNova) <6){

registrarLog(
        $pdo,
        $_SESSION['usuario_id'],
        "SENHA_VALIDACAO_FALHA",
        "O usuário " . $nomeUsuario['nome_completo'] . " informou uma senha com menos de 6 caracteres.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'WARNING'
    );


http_response_code(400);
echo json_encode([
"success"=> false ,
"message"=> "A senha deve ter pelo menos 6 caracteres"
]);

exit ;

}

// Armazena somente o hash da nova senha, nunca o valor original.
$senhahash = password_hash($SenhaNova , PASSWORD_DEFAULT);

// Atualiza a senha do usuário autenticado por meio de uma consulta parametrizada.
$SQL = $pdo-> prepare("UPDATE medicos SET senha_hash = :senha_hash WHERE id = :id");

$SQL->execute([
"senha_hash"=> $senhahash ,
"id"=> $_SESSION['usuario_id']

]);

// Registra a recuperação de senha concluída com sucesso.
// Esse evento fornece um histórico confiável de ações sensíveis do usuário.
registrarLog(
        $pdo,
        $_SESSION['usuario_id'],
        "SENHA_ALTERADA",
        "O usuário " . $nomeUsuario['nome_completo'] . " realizou recuperação de senha com sucesso.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );


unset ($_SESSION['2fa_verificado']);

echo json_encode([
"success" => true ,
"message" => "Senha atualizada com sucesso"

]);

exit ;

}catch (PDOException $e){

http_response_code(500);
echo json_encode([
"success"=> false,
"message"=> "Erro do servidor"

]);
exit ;
}