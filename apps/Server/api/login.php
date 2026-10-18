<?php

// Página de autenticação do usuário.
// Recebe email e senha, valida no banco, e se tudo estiver correto,
// gera um código temporário de verificação em duas etapas (2FA).
// Um teste


// Carrega as dependências do PHPMailer para envio de e-mail.
require_once __DIR__ . '/../vendor/autoload.php';

// Configura a sessão com cookies protegidos para reduzir risco de roubo de sessão.
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
      'samesite' => 'None'
]);

session_start();

// Mantém as falhas recentes na sessão para limitar tentativas de força bruta.
// A janela abaixo é móvel: tentativas com mais de 60 segundos são descartadas
// antes da validação das credenciais, evitando que falhas antigas acumulem indefinidamente.
if (!isset($_SESSION['tentativas_login_timestamps']) || !is_array($_SESSION['tentativas_login_timestamps'])) {
    $_SESSION['tentativas_login_timestamps'] = [];
}

if (!isset($_SESSION['bloqueio_login'])) {
    $_SESSION['bloqueio_login'] = 0;
}

// Remove dados inválidos e timestamps fora da janela atual; essa contagem está
// associada à sessão PHP corrente, não a um contador persistente por usuário/IP.
$agora = time();
$_SESSION['tentativas_login_timestamps'] = array_values(array_filter(
    $_SESSION['tentativas_login_timestamps'],
    static fn ($timestamp) => is_int($timestamp) && $timestamp > $agora - 60 && $timestamp <= $agora
));

// Registra a tentativa malsucedida e ativa um bloqueio temporário ao atingir o limite.
// O booleano informa ao chamador se deve responder com HTTP 429.
$registrarFalhaLogin = static function (): bool {
    $agora = time();
    $tentativas = $_SESSION['tentativas_login_timestamps'];
    $tentativas[] = $agora;
    $_SESSION['tentativas_login_timestamps'] = $tentativas;

    if (count($tentativas) >= 5) {
        $_SESSION['bloqueio_login'] = $agora + 60;
        $_SESSION['tentativas_login_timestamps'] = [];
        return true;
    }

    return false;
};

// Registra a última atividade para controle de expiração da sessão.
$_SESSION['ultima_atividade'] = time();

// Responde em JSON e aplica CORS para permitir a comunicação com o frontend.
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

// Preflight do navegador para requisições CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Importa a conexão com o banco de dados.
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/registrarLog.php";
try {
    // Lê o corpo JSON enviado pelo cliente para obter as credenciais.
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

    if ($_SESSION['bloqueio_login'] > 0) {
        $_SESSION['bloqueio_login'] = 0;
    }

    // Extrai os dados enviados pelo frontend no corpo da requisição.
    $email = trim($dados["email"] ?? "");
    $senha = trim($dados["senha"] ?? "");

    // Valida se os campos obrigatórios foram preenchidos antes de consultar o banco.
    if ($email === "" || $senha === "") {
         
        

        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email e senha são obrigatórios."
        ]);
        exit;
    }

    // Busca o usuário pelo e-mail para validar a identidade antes do 2FA.
    // O uso de prepared statement evita SQL injection.
    $consulta = $pdo->prepare(
        "SELECT * FROM medicos WHERE email = :email LIMIT 1"
    );

    $consulta->execute([
        "email" => $email
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário, recusa o login e registra a falha no histórico de auditoria.
    // Isso ajuda a detectar tentativas de acesso com e-mail inexistente ou brute force.
    // A resposta é igual à usada para senha incorreta para não revelar se o e-mail existe.
    if (!$usuario) {
        registrarLog(
            $pdo,
            null,
            "LOGIN_FALHA",
            "Tentativa de login com usuário não encontrado.",
            'Medicos',
            $_SERVER['REMOTE_ADDR'] ?? null,
          
            'SECURITY'
        );

        if ($registrarFalhaLogin()) {
            http_response_code(429);
            echo json_encode([
                "success" => false,
                "message" => "Muitas tentativas. Aguarde 60 segundos."
            ]);
            exit;
        }

        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou senha inválidos."
        ]);
        exit;
    }

    // Compara a senha informada com o hash salvo no banco.
    // password_verify é a maneira correta de validar senhas sem expor o valor em texto puro.
    // Quando a senha estiver incorreta, o sistema registra a falha associada ao usuário.
    if (!password_verify($senha, $usuario["senha_hash"])) {
        registrarLog(
            $pdo,
            $usuario['id'],
            "LOGIN_FALHA",
            "O usuário " . $usuario['nome'] . " informou uma senha inválida.",
            'Medicos',
            $_SERVER['REMOTE_ADDR'] ?? null,
           
            'SECURITY'
        );

        if ($registrarFalhaLogin()) {
            http_response_code(429);
            echo json_encode([
                "success" => false,
                "message" => "Muitas tentativas. Aguarde 60 segundos."
            ]);
            exit;
        }

        if (($usuario['status'] ?? null) !== 'ativo') {
            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "Sua conta ainda não foi aprovada."
            ]);
            exit;
        }

        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou senha inválidos."
        ]);
        exit;
    }

    $_SESSION['tentativas_login_timestamps'] = [];
    $_SESSION['bloqueio_login'] = 0;

    // Usuário e senha corretos: encerra a contagem de falhas e inicia a etapa de 2FA.
    // A sessão é renovada para reduzir o risco de fixação do identificador de sessão.
    session_regenerate_id(true);

    // Guarda apenas o contexto necessário para concluir a autenticação após o 2FA.
    // A sessão ainda não recebe usuario_id, portanto a etapa primária sozinha
    // não deve ser interpretada como uma sessão autenticada.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];

    // Registra que o usuário passou pela etapa de autenticação inicial e entrou no processo de 2FA.
    // Esse evento marca o início da verificação em duas etapas, antes da confirmação final do código.
    registrarLog(
        $pdo,
        $usuario['id'],
        "LOGIN_2FA",
        "O usuário " . $usuario['nome_completo'] . " iniciou o login e recebeu o código 2FA.",
        'Medicos',
        $_SERVER['REMOTE_ADDR'] ?? null,
       
        'INFO'
    );
    $_SESSION['2fa_nome'] = $usuario['nome_completo'];
    $_SESSION['2fa_email'] = $usuario['email'];
    $_SESSION['2fa_perfil'] = $usuario['cargo'];

    // Gera um código numérico de seis dígitos usando o gerador criptograficamente seguro.
    // O código em texto é enviado por e-mail; a sessão guarda somente seu hash
    // para que a validação posterior seja feita com password_verify.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Guarda o código em hash e define o prazo de validade por 5 minutos.
    // Isso evita que o código seja armazenado em texto puro e reduz o risco de exposição.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);


        // Obtém a credencial do serviço de e-mail do ambiente, sem embuti-la no código.
  

$resendAPI = getenv('RESEND_API_KEY');



if (!$resendAPI){


http_response_code(500);

echo json_encode([
    "success" => false,
    "message" => "Configurando do resend não encontrada"
]);


exit;

}


// O código só pode ser validado enquanto a sessão pendente e o prazo de cinco minutos
// permanecerem válidos no endpoint verify-2fa.php.
try {

$resend = Resend::client($resendAPI);


$resultado = $resend->emails->send([
'from' => 'Continuum <seguranca@projcontinnum.com.br>',

'to'=> [$email],
"subject"=> 'Codigo de verificação',
'html' => "
<h1> Verificação de segurança  </h1>
<p>  Seu codigo de verificação de login é   </p>

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

// Informa ao frontend que a senha foi aceita, mas que ainda falta concluir o 2FA.
echo json_encode([
    "success"=>false,
    "message"=> "Não foi possível enviar o código de verificação."
]);


exit ;
}


echo json_encode([

"success" => true,
"requires_2fa" => true ,
"message" => "Código de verificação enviado para seu e-mail."


]);


exit;


}catch (PDOException $e){

error_log('Doctor login database error: ' . $e->getMessage());

http_response_code(500);

echo json_encode([


"success" => false,
"message" => "Não foi possível concluir o login."

]);
exit;


}