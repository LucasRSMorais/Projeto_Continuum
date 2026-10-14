<?php

// Página de cadastro de novos usuários.
// Recebe dados do frontend, valida e salva o usuário no banco com senha criptografada.

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/../config/database.php";

try {
    // Lê os dados enviados em JSON pelo frontend.

 if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

$idPaciente = $_SESSION['usuario_id'] ?? null;
if (!$idPaciente) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Usuário não autenticado."
    ]);
    exit;
}




    $dados = json_decode(file_get_contents("php://input"), true);


if (!is_array($dados)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Dados inválidos. Certifique-se de enviar um JSON válido."
        ]);
        exit;
    }


    $cep = trim($dados["cep"] ?? "");
    $cidade = trim($dados["cidade"] ?? "");
    $estado = trim($dados["estado"] ?? "");
    $rua = trim($dados["rua"] ?? "");
    $numero = trim($dados["numero"] ?? "");



    // Valida os campos obrigatórios do cadastro do paciente conforme o ER.
    if ($cep === "" || $cidade === "" || $estado === "" || $rua === "" || $numero === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "CEP, cidade, estado, rua e número são obrigatórios."
        ]);
        exit;
    }

  
if (!preg_match('/^\d{8}$/', $cep)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "CEP inválido. O formato correto é 00000-000."
        ]);
        exit;
    }



    // Verifica se o e-mail já existe antes de inserir um novo paciente.
  

    /*SELECT id FROM enderecos WHERE id_paciente = :id_paciente
AND cep = :cep AND cidade = :cidade AND estado = :estado AND rua = :rua AND numero = :numero
LIMIT 1*/
$sql = "


SELECT id FROM enderecos WHERE cep = :cep AND cidade = :cidade AND estado = :estado AND rua = :rua AND numero = :numero 
LIMIT 1


";


$consulta = $pdo->prepare($sql);
$consulta->execute([
        "cep" => $cep,
        "cidade" => $cidade,
        "estado" => $estado,
        "rua" => $rua,
        "numero" => $numero
    ]);


    if ($consulta->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este endereço já está cadastrado para este paciente."
        ]);
        exit;
    }

$sql = "INSERT INTO enderecos (id_paciente, cep, cidade, estado, rua, numero) VALUES (:id_paciente, :cep, :cidade, :estado, :rua, :numero)";
$consulta = $pdo->prepare($sql);


$consulta->execute([
        "id_paciente" => $idPaciente,
        "cep" => $cep,
        "cidade" => $cidade,
        "estado" => $estado,
        "rua" => $rua,
        "numero" => $numero
    ]);



    // Grava no log a criação do cadastro do paciente como evento de auditoria.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
         // Nenhum usuário logado no momento do cadastro do paciente.
        $idPaciente,
        "CADASTRO_ENDERECO",
        
        "Endereço cadastrado com sucesso." ,
        "endereco",
        $_SERVER['REMOTE_ADDR'] ?? null,
        'INFO'
    );

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Dados  do endereço cadastrado com sucesso."
    ]);

} catch (PDOException $e) {
    // Erro relacionado ao banco.

    error_log("Erro no cadastro de endereco: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" =>  $e->getMessage()
    ]);

} catch (Exception $e) {
    // Erro geral de execução.
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao realizar cadastro."
    ]);
}