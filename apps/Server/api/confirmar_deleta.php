<?php

session_set_cookie_params([
    'httponly' => true,
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

    // Busca o usuário pelo email no banco.
    $sql = "SELECT * FROM pacientes WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar o usuário, recusa o login.
    if (!$usuario) {
          
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou senha inválidos."
        ]);
        exit;
    }

    // Verifica se a senha digitada corresponde ao hash salvo no banco.
    if (!password_verify($senha, $usuario["senha_hash"])) {
         
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email ou senha inválidos."
        ]);
        exit;
    }


    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = :id");
    $stmt->execute([':id' => $usuario['id']]);

    registrarLog(
        $pdo,
        null,
        "EXCLUSAO_CONTA_PACIENTE",
        "O paciente " . $usuario['nome_completo'] . " excluiu a própria conta.",
        "pacientes",
        $_SERVER['REMOTE_ADDR'] ?? null,
        "SECURITY"
    );

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Conta deletada com sucesso."
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
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erro de banco de dados: " . $e->getMessage());
    // Caso ocorra algum erro de banco de dados.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao acessar o banco de dados."
    ]);
}