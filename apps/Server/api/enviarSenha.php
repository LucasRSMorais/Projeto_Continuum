<?php

// É uma parte do backend que vai ser dedicada que caso o usuario esquecer a senha dele  , o usuario vai precisar digitar o email dele para recuperar , e o codigo vai vir pelo email dele


require_once __DIR__ . '/../vendor/autoload.php';

// O código gerado será vinculado à sessão até a confirmação do 2FA.



session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'None',
    'secure' => true
]);

session_start();

// O endpoint inicia a recuperação enviando um código de verificação por e-mail.
// O código é temporário e fica associado à sessão até ser validado no fluxo seguinte.

// Define contadores de tentativas e bloqueio para reduzir solicitações repetidas
// dentro da sessão atual; não representa um limite global por conta ou endereço IP.
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
// Importa a conexão do sistema.log
require_once __DIR__ . "/../config/logger.php";

try {
    // Lê o e-mail informado no corpo JSON para localizar a conta que solicitou
    // a recuperação; nenhuma alteração de senha ocorre neste endpoint.
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
    // Registra no arquivo de log o início do processo de recuperação por e-mail.
   
   

    // Valida se email  não veio vazio.
    // Se estiver vazio, a operação é interrompida antes de gerar ou enviar qualquer código.
    if ($email === "" ) {
        
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email é obrigatorio de colocar."
        ]);
        exit;
    }

    // Busca a conta pelo e-mail usando parâmetro vinculado, evitando concatenar
    // a entrada recebida diretamente no comando SQL.
    $consulta = $pdo->prepare(
        "SELECT * FROM medicos WHERE email = :email LIMIT 1"
        
    );
   

    $consulta->execute([
        "email" => $email
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário, recusa a recuperação.
    // O log registra a falha para investigação e auditoria de abuso ou tentativa indevida.
    if (!$usuario) {
        SecurityLogger::logSecurityEvent($pdo, null, 'EMAIL_RECOVERY', 'Tentativa de recuperação de senha com email não encontrado.', ['email' => $email]);
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "O Email não existir ."
        ]);
        exit;
    }

    
    // Cria um identificador de sessão novo antes de vincular a sessão ao desafio
    // de recuperação, evitando reaproveitar um identificador previamente conhecido.
    session_regenerate_id(true);

    // Guarda somente os dados necessários para identificar a conta na validação
    // do código; a sessão ainda não deve permitir alteração direta da senha.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];
    $_SESSION['2fa_nome'] = $usuario['nome_completo'];
    $_SESSION['2fa_email'] = $usuario['email'];
    

    // Gera um código de seis dígitos com fonte aleatória criptograficamente segura.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Armazena o hash do código na sessão e limita sua validade a cinco minutos.
    // O código original é usado somente para compor a mensagem enviada por e-mail.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);

// Obtém a chave do serviço de e-mail da configuração do ambiente; ela não deve
// ser armazenada no repositório nem enviada ao navegador.

$resendAPI = getenv('RESEND_API_KEY');



if (!$resendAPI){


http_response_code(500);

// Informa que a etapa inicial foi concluída e que o frontend deve solicitar
// a validação do código antes de permitir a troca de senha.
echo json_encode([
    "success" => false,
    "message" => "Configurando do resend não encontrada"
]);


exit;

}


try {

$resend = Resend::client($resendAPI);


$resultado = $resend->emails->send([
'from' => 'Continuum <seguranca@projcontinnum.com.br>',

'to'=> [$email],
"subject"=> 'Codigo de verificação',
'html' => "
<h1> Verificação de segurança  </h1>
<p>  Seu codigo de verificação de recuperação de senha é   </p>

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