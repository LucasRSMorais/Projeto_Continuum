<?php

// É uma parte do backend que vai ser dedicada que caso o usuario esquecer a senha dele  , o usuario vai precisar digitar o email dele para recuperar , e o codigo vai vir pelo email dele

use PHPMailer\PHPMailer\PHPMailer ;
use PHPMailer\PHPMailer\SMTP ;
use PHPMailer\PHPMailer\Exception ;
require_once __DIR__ . '/../vendor/autoload.php';

// O código gerado será vinculado à sessão até a confirmação do 2FA.



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

    // Busca o usuário pelo email no banco.
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

    
    session_regenerate_id(true);

    // Guarda os dados do usuário temporariamente na sessão até o 2FA ser validado.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];
    $_SESSION['2fa_nome'] = $usuario['nome_completo'];
    $_SESSION['2fa_email'] = $usuario['email'];
    

    // Gera um código de 6 dígitos para simular o 2FA.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Salva o código em hash e a validade por 5 minutos.
    // A sessão guarda o valor protegido, e não o código em texto puro.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);

// Configura o envio SMTP do código de recuperação por e-mail usando apenas variáveis de ambiente.
// Esses logs ajudam a rastrear que a recuperação foi iniciada e que o código estava sendo enviado.

 $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $smtpUsername = getenv('SMTP_USERNAME');
    $smtpPassword = getenv('SMTP_PASSWORD');
    $smtpPort = (int) (getenv('SMTP_PORT') ?: 587);

    // Se o e-mail não estiver configurado, não continua o login para evitar falha silenciosa.
    if (!$smtpUsername || !$smtpPassword) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Configuração de e-mail do 2FA não encontrada."
        ]);
        exit;
    }


    $verificacao_email = new PHPMailer(true);
    $verificacao_email->CharSet = 'UTF-8';
    $verificacao_email->isSMTP();
    $verificacao_email->Host = $smtpHost;
    $verificacao_email->SMTPAuth = true;
    $verificacao_email->Username = $_ENV['SMTP_USERNAME'];
    $verificacao_email->Password = $_ENV['SMTP_PASSWORD'];
    $verificacao_email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $verificacao_email->Timeout = 10;
    $verificacao_email->Port = $smtpPort;
    $verificacao_email->setFrom($smtpUsername, 'Continuum');
    $verificacao_email->addAddress($email);
    $verificacao_email->isHTML(true);
    $verificacao_email->Subject = 'Codigo de verificacao';

    // Corpo do e-mail com o código de verificação.
    $verificacao_email->Body = "
    <h2>  Verificação de segurança   </h2>
    <p> Seu codigo de verificação de login é   :  </p>
    <h1>  $codigo  </h1>
    <p> Esse código é valido por 5 minutos  </p>
    ";

    // Tenta enviar o e-mail e, se falhar, responde com erro explícito para o frontend.
    try {
        $verificacao_email->send();
    } catch (Exception $emailError) {
        error_log('Falha ao enviar o código 2FA: ' . $emailError->getMessage());
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => $emailError->getMessage()
        ]);
        exit;
    }

    // Se o e-mail foi enviado com sucesso, retorna o status e informa que o usuário precisa confirmar o 2FA.
    echo json_encode([
        "success" => true,
        "requires_2fa" => true,
        "message" => "Código de verificação gerado.",
        "codigo_teste" => ""
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