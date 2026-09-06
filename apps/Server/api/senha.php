<?php

// A parte do backend que vai ser resposavel de recuperação da senha do usuarios 

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


// Avisar que caso não encontrar o id do usuario , identificar que não existir nenhum usuario

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
// Indetificar se a senha está em branco
if ($SenhaNova === ""){

http_response_code(400);

echo json_encode([
"success"=> false ,
"message"=> "A senha esta em branco."

]);
exit ;
}



// Avisar a senha que precisa ter pelo menos 6 caracteres
if (strlen($SenhaNova) <6){
acessadolog_Continuum("A senha deve ter pelo menos 6 caracteres " , "ERRO");
http_response_code(400);
echo json_encode([
"success"=> false ,
"message"=> "A senha deve ter pelo menos 6 caracteres"
]);

exit ;

}

// A nova senha sera totalmente criptografia 
$senhahash = password_hash($SenhaNova , PASSWORD_DEFAULT);

// O processo da atualização da nova senha .
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