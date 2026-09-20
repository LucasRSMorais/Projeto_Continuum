<?php

// Página de autenticação do usuário.
// Recebe email e senha, valida no banco, e se tudo estiver correto,
// gera um código temporário de verificação em duas etapas (2FA).
// Um teste

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carrega as dependências do PHPMailer para envio de e-mail.
require_once __DIR__ . '/../vendor/autoload.php';

// Configura a sessão com cookies protegidos para reduzir risco de roubo de sessão.
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

// Mantém o estado do usuário durante o processo de autenticação.
// Isso ajuda a bloquear tentativas de força bruta e guardar o contexto do 2FA.
if (!isset($_SESSION['tentativas_login'])) {
    $_SESSION['tentativas_login'] = 0;
}

if (!isset($_SESSION['bloqueio_login'])) {
    $_SESSION['bloqueio_login'] = 0;
}

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
    // Quando o limite é atingido, o sistema bloqueia temporariamente o acesso.
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

    // Extrai os dados enviados pelo frontend no corpo da requisição.
    $email = trim($dados["email"] ?? "");
    $senha = trim($dados["senha"] ?? "");

    // Valida se os campos obrigatórios foram preenchidos antes de consultar o banco.
    if ($email === "" || $senha === "") {
         
        

        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email e senhasão obrigatórios."
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
    if (!$usuario) {
        registrarLog(
            $pdo,
            null,
            "LOGIN_FALHA",
            "Tentativa de login com usuário não encontrado.",
            'usuarios',
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

    // Compara a senha informada com o hash salvo no banco.
    // password_verify é a maneira correta de validar senhas sem expor o valor em texto puro.
    // Quando a senha estiver incorreta, o sistema registra a falha associada ao usuário.
    if (!password_verify($senha, $usuario["senha_hash"])) {
        registrarLog(
            $pdo,
            $usuario['id'],
            "LOGIN_FALHA",
            "O usuário " . $usuario['nome'] . " informou uma senha inválida.",
            'usuarios',
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


    // Usuário e senha corretos: inicia a etapa de verificação em duas etapas.
    // A sessão atual é renovada para reduzir o risco de fixação de sessão.
    session_regenerate_id(true);

    // Armazena temporariamente as informações do usuário até a validação do código 2FA.
    // Isso permite manter o contexto do login sem autenticar a sessão final ainda.
    $_SESSION['2fa_usuario_id'] = $usuario['id'];

    // Registra que o usuário passou pela etapa de autenticação inicial e entrou no processo de 2FA.
    // Esse evento marca o início da verificação em duas etapas, antes da confirmação final do código.
    registrarLog(
        $pdo,
        $usuario['id'],
        "LOGIN_2FA",
        "O usuário " . $usuario['nome'] . " iniciou o login e recebeu o código 2FA.",
        'usuarios',
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
        'INFO'
    );
    $_SESSION['2fa_nome'] = $usuario['nome_completo'];
    $_SESSION['2fa_email'] = $usuario['email'];
    $_SESSION['2fa_perfil'] = $usuario['cargo'];

    // Gera um código de 6 dígitos para simular a etapa de 2FA.
    // Esse valor será enviado ao usuário e comparado com o hash armazenado em sessão.
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Guarda o código em hash e define o prazo de validade por 5 minutos.
    // Isso evita que o código seja armazenado em texto puro e reduz o risco de exposição.
    $_SESSION['2fa_codigo'] = password_hash($codigo, PASSWORD_DEFAULT);
    $_SESSION['2fa_expira'] = time() + (5 * 60);

    // Configura e envia o código por e-mail usando PHPMailer.
    // Isso conclui a etapa de verificação em duas etapas do processo de login.
    // Lê as credenciais do provedor de e-mail via variáveis de ambiente do Railway.
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
    $verificacao_email->Username = $smtpUsername;
    $verificacao_email->Password = $smtpPassword;
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
            "message" => "Não foi possível enviar o código de verificação."
        ]);
        exit;
    }

    // Se o e-mail foi enviado com sucesso, retorna o status e informa que o usuário precisa confirmar o 2FA.
    echo json_encode([
        "success" => true,
        "requires_2fa" => true,
        "message" => "Código de verificação gerado.",
        "codigo_teste" => $codigo
    ]);
    exit;

    // Bloco legado: não executa porque o fluxo sai antes.
    // Era usado para fazer login direto sem a etapa de verificação em duas etapas.
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
        "message" => $e->getMessage()
    ]);
}