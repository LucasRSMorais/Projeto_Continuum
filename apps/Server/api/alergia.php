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


    $tipo = trim($dados["tipo"] ?? "");
     $gravidade = trim($dados["gravidade"] ?? "");
     $contra_medida = trim($dados["contra_medida"] ?? "");



    // Valida os campos obrigatórios do cadastro do paciente conforme o ER.
    if ($tipo === "" || $gravidade === "" || $contra_medida === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Tipo, gravidade e contra-medida são obrigatórios."
        ]);
        exit;
    }

  



    // Verifica se o e-mail já existe antes de inserir um novo paciente.
  
$sql = "



SELECT id FROM alergias WHERE id_paciente = :id_paciente AND tipo = :tipo LIMIT 1

";
$consulta = $pdo->prepare($sql);
$consulta->execute([
        "id_paciente" => $idPaciente,
        "tipo" => $tipo,
      
    ]);


    if ($consulta->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Esta alergia já está cadastrada para este paciente."
        ]);
        exit;
    }

$sql = "INSERT INTO alergias (id_paciente, tipo, gravidade, contra_medida) VALUES (:id_paciente, :tipo, :gravidade, :contra_medida)";
$consulta = $pdo->prepare($sql);


$consulta->execute([
        "id_paciente" => $idPaciente,
        "tipo" => $tipo,
        "gravidade" => $gravidade,
        "contra_medida" => $contra_medida
    ]);



    // Grava no log a criação do cadastro do paciente como evento de auditoria.
    require_once __DIR__ . "/registrarLog.php";
    registrarLog(
        $pdo,
         // Nenhum usuário logado no momento do cadastro do paciente.
        $idPaciente,
        "CADASTRO_ALERGIA",
        
        "Alergia cadastrada com sucesso." ,
        "alergia",
        $_SERVER['REMOTE_ADDR'] ?? null,
        'INFO'
    );

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Dados  da alergia cadastrada com sucesso."
    ]);

} catch (PDOException $e) {
    // Erro relacionado ao banco.

    error_log("Erro no cadastro de alergia: " . $e->getMessage());
    
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