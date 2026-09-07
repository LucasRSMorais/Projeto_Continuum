<?php

// É uma parte do backend que vai ser dedicada que caso o usuario esquecer a senha dele  , o usuario vai precisar digitar o email dele para recuperar , e o codigo vai vir pelo email dele

use PHPMailer\PHPMailer\PHPMailer ;
use PHPMailer\PHPMailer\SMTP ;
use PHPMailer\PHPMailer\Exception ;
require_once __DIR__ . '/../vendor/autoload.php';




session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Define contadores de tentativas e bloqueio para evitar brute force.
if (!isset($_SESSION['tentativas_login'])) {
    $_SESSION['tentativas_login'] = 0;
}

if (!isset($_SESSION['bloqueio_login'])) {
    $_SESSION['bloqueio_login'] = 0;
}

$_SESSION['ultima_atividade'] = time();

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

try {
    // Lê os dados enviados pelo frontend em JSON.
    $dados = json_decode(
        file_get_contents("php://input"),
        true
    );

    // Se o usuário ainda estiver bloqueado, nega o login temporariamente.
    if (time() < $_SESSION['bloqueio_login']) {
        $restante = $_SESSION['bloqueio_login'] - time();
        http_response_code(429);
        echo json_encode([
            "success" => false,
            "message" => "Muitas tentativas. Aguarde {$restante} segundos."
        ]);
        exit;
    }

    // Conta cada tentativa para proteger contra ataques de força bruta.
    $_SESSION['tentativas_login']++;

    // Depois de 5 tentativas, bloqueia por 60 segundos.
    if ($_SESSION['tentativas_login'] >= 5) {
        $_SESSION['bloqueio_login'] = time() + 60;
        $_SESSION['tentativas_login'] = 0;

        http_response_code(429);

        echo json_encode([
            "success" => false,
            "message" => "Muitas tentativas. Aguarde 60 segundos."
        ]);
        exit;
    }

    // Extrai os dados do formulário enviado pelo cliente.
    $email = trim($dados["email"] ?? "");
    acessadolog_Continuum("Ação do email começando.." , "Ação");
   

    // Valida se email  não veio vazio.
    if ($email === "" ) {
        
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email é obrigatorio de colocar."
        ]);
        exit;
    }

    // Busca o usuário pelo email no banco.
    $consulta = $pdo->prepare(
        "SELECT * FROM usuarios WHERE email = :email LIMIT 1"
        
    );
   

    $consulta->execute([
        "email" => $email
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário, recusa a recuperação.
    if (!$usuario) {
        acessadolog_Continuum("O usuário não identificado.." , "ERRO");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "O Email não existir ."
        ]);
        exit;
    }

    
    session_regenerate_id(true);

    // Guarda os dados do usuário temporariamente na sessão até o 2FA ser validado.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];
    $_SESSION['2fa_nome'] = $usuario['nome'];
    $_SESSION['2fa_email'] = $usuario['email'];
    $_SESSION['2fa_perfil'] = $usuario['perfil'];

    // Gera um código de 6 dígitos para simular o 2FA.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Salva o código em hash e a validade por 5 minutos.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);
// Resumidamente a tag $mail vai ser responsavel de enviar o codigo para o usuario para ele ir na pagina de recuperação de email
acessadolog_Continuum("O email indeficado..". $email , "Sucesso");
acessadolog_Continuum("O codigo verificação enviando pelo email.." , "AÇÃO");
$mail = new PHPMailer(true);
$mail -> isSMTP();
$mail -> Host = 'smtp.gmail.com';
$mail -> SMTPAuth = true ;
$mail -> Username = 'continuum517@gmail.com';
$mail -> Password = 'lblt bylz gaqu cegs';
$mail -> SMTPSecure = PHPMailer ::ENCRYPTION_STARTTLS;
$mail ->Port =587;
$mail -> setFrom('continuum517@gmail.com' , 'Codigo');
$mail -> addAddress($email);
$mail -> isHTML(true);
$mail -> Subject = 'Codigo de verificacao';

$mail -> Body = "
<h2>  Verificação de segurança   </h2>
<p> Seu codigo de verificação é  :  </p>
<h1>  $codigo  </h1>
<p> Esse código é valido por 5 minutos  </p>
";
$mail->send();

   
    
    echo json_encode([
        "success" => true,
        "requires_2fa" => true,
        "message" => "Código de verificação enviado para o seu email.",
        
    ]); 
    exit; 

    // Código antigo que não será executado porque o script sai antes.
    // Ele seria usado para login direto sem verificação de duas etapas.
    echo json_encode([
        "success" => true,
        "message" => "Login realizado com sucesso.",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"],
            "perfil" => $usuario["perfil"]
        ]
    ]);

} catch (PDOException $e) {
    // Caso ocorra algum erro de banco de dados.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro interno do servidor."
    ]);
}