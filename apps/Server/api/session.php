<?php

// Página responsável por verificar se o usuário está autenticado.
// Também controla a expiração da sessão após 30 minutos de inatividade.
require_once __DIR__ . "/../config/database.php";









session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);

session_start();

// Define o tempo máximo permitido sem atividade do usuário.
// Isso protege contra sessões abertas por muito tempo sem uso real.
$tempoExpiracao = 30 * 60; // Expira a sessão depois de 30 minutos sem atividade.

// Se o tempo de inatividade excedeu o limite, apaga os dados da sessão no servidor.
// Em seguida, a API responde 401 para indicar que o usuário precisa autenticar novamente.
if (
    isset($_SESSION['ultima_atividade']) &&
    time() - $_SESSION['ultima_atividade'] > $tempoExpiracao
) {
    $_SESSION = [];
    session_destroy();
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Sessão expirada."
    ]);
    exit;
}

// Atualiza a marca de atividade somente depois de verificar que ela não expirou.
$_SESSION['ultima_atividade'] = time();

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Se o usuário ainda não estiver autenticado, responde 401.
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    
    echo json_encode([
        "success" => false,
        "authenticated" => false,
        "message" => "Usuário não autenticado."
    ]);
    exit;
}




// Complementa os dados básicos da sessão com informações do cadastro do paciente.
// A consulta parametrizada associa o resultado ao ID guardado na sessão, sem
// aceitar um identificador de usuário fornecido pelo cliente.
$stmt = $pdo -> prepare(
"SELECT cpf , telefone  , aceite_termo_uso FROM pacientes WHERE id = :id"

);


$stmt->execute(


[


':id' => $_SESSION ['usuario_id']

]

);


$paciente = $stmt -> fetch(PDO::FETCH_ASSOC);




// Recupera o primeiro registro de auditoria do cadastro para expor a data
// associada ao consentimento/cadastro apresentado no perfil do titular.
$stmtConsetimento = $pdo->prepare(


"SELECT data_hora FROM logs_auditoria WHERE id_usuario = :id AND acao_realizada = 'CADASTRO_PACIENTE' AND tabela_afetada = 'pacientes' ORDER BY data_hora ASC LIMIT 1
"
);



$stmtConsetimento-> execute([

':id' => $_SESSION ['usuario_id']


]);

$data_cadastro = $stmtConsetimento-> fetchColumn();



// Retorna somente os campos usados pelo frontend; dados internos da sessão
// e credenciais (como hashes de senha) não são incluídos na resposta.
echo json_encode([
    "success" => true,
    "authenticated" => true,
    "usuario" => [
        "id" => $_SESSION['usuario_id'],
        "nome" => $_SESSION['nome'],
        "email" => $_SESSION['email'],
        "cpf" => $paciente ['cpf'] ?? null,
        "telefone" => $paciente ['telefone']?? null ,
          "data" => $data_cadastro ?: null ,
          "aceite_termo_uso" => $paciente ['aceite_termo_uso'] ?? null
        
        
    ]
]);