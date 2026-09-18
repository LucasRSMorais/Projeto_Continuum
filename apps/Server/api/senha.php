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
// Importa a conexão do sistema.log
require_once __DIR__ . "/../config/logger.php";

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
if (strlen($SenhaNova) <6){
acessadolog_Continuum("A senha deve ter pelo menos 6 caracteres " , "ERRO");
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
$SQL = $pdo-> prepare("UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id");

$SQL->execute([
"senha_hash"=> $senhahash ,
"id"=> $_SESSION['usuario_id']

]);

unset ($_SESSION['2fa_verificado']);

echo json_encode([
"success" => true ,
"message" => "Senha atualizada com sucesso"

]);
acessadolog_Continuum(" Senha atualizado com sucesso " , "SUCESSO");
exit ;

}catch (PDOException $e){

http_response_code(500);
echo json_encode([
"success"=> false,
"message"=> "Erro do servidor"

]);
exit ;
}