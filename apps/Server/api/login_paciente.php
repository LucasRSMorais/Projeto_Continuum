<?php

// Página de autenticação do usuário.
// Recebe email e senha, valida no banco, e se tudo estiver correto,
// gera um código temporário de verificação em duas etapas (2FA).
// Um teste


require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . "/registrarLog.php";

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
    
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
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Importa a conexão com o banco de dados.
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/registrarLog.php";
try {
    // Lê os dados enviados pelo frontend em JSON.
    $dados = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Corpo da requisição inválido."
        ]);
        exit;
    }

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
    $senha = trim($dados["senha"] ?? "");

   

    // Valida se email e senha não vieram vazios.
    if ($email === "" || $senha === "") {
        

        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email e senha são obrigatórios."
        ]);
        exit;
    }

    // Busca o usuário pelo e-mail na tabela de pacientes.
    $consulta = $pdo->prepare(
        "SELECT * FROM pacientes WHERE email = :email LIMIT 1"
    );

    $consulta->execute([
        "email" => $email
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário, recusa o login.
    if (!$usuario) {
    registrarLog(
        $pdo,
        null,
        "LOGIN_FALHA",
        "Tentativa de login com paciente não encontrado.",
        'pacientes',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'SECURITY'
    );

    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Email ou senha inválidos."
    ]);
    exit;
}

    // Verifica se a senha digitada corresponde ao hash salvo no banco.
    if (!password_verify($senha, $usuario["senha"])) {
         registrarLog(
        $pdo,
        $usuario['id'],
        "LOGIN_FALHA",
        "O paciente chamado " . $usuario['nome_completo'] . " informou uma senha inválida.",
        'pacientes',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'SECURITY'
    );
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou senha inválidos."
        ]);
        exit;
    }
    
    registrarLog(
    $pdo,
    $usuario['id'],
    "LOGIN_2FA",
    "O usuário " . $usuario['nome_completo'] . " iniciou o login e recebeu o código 2FA.",
    'pacientes',
    $_SERVER['REMOTE_ADDR'] ?? null,
    $_SERVER['HTTP_USER_AGENT'] ?? null,
    $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
    'INFO'
);

    // Usuário e senha corretos: inicia a etapa de verificação em duas etapas.
    session_regenerate_id(true);

    // Guarda os dados do usuário temporariamente na sessão até o 2FA ser validado.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];
    $_SESSION['2fa_nome'] = $usuario['nome_completo'];
    $_SESSION['2fa_email'] = $usuario['email'];
   

    // Gera um código de 6 dígitos para simular o 2FA.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Salva o código em hash e a validade por 5 minutos.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);

  
    $resendAPI = getenv('RESEND_API_KEY');



if (!$resendAPI){


http_response_code(500);

echo json_encode([
    "success" => false,
    "message" => "Configurando do resend não encontrada"
]);


exit;

}


try {

$resend = Resend::client($resendAPI);


$resultado = $resend->emails->send([

'from'=> 'Continuum <onboarding@resend.dev>',
'to'=> [$email],
"subject"=> 'Codigo de verificação',
'html' => "
<h1> Verificação de segurança  </h1>
<p>  Seu codigo de verificação de login para os pacientes é   </p>

<h2> Verificação de segurança  </h2>

<h2>  $codigo   </h2>

<p>  Codigo expirar em cinco minutos   </p>


"


]);


}catch (Exception $emailError){

error_log(

'falha ao enviar o codigo 2FA'
. $emailError->getMessage()

);

http_response_code(500);

echo json_encode([
    "success"=>false,
    "message"=> "Não foi possivel de enviar o codigo de verificação"
]);


exit ;
}


echo json_encode([

"success" => true,
"requires_2fa" => true ,
"message" => "Código de verificação enviado para seu e-mail.",
"codigo_teste" => $codigo


]);


exit;


}catch (PDOException $e){


http_response_code(500);

echo json_encode([


"success" => false,
"message" => $e->getMessage()

]);
exit;


}