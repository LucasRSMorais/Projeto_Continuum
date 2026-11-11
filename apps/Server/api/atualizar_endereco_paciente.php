<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode([
        "success" => false,
        "message" => "Método não permitido."
    ]);
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

if (
    !isset($_SESSION['usuario_id'])
    || ($_SESSION['2fa_verificado'] ?? false) !== true
) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Faça login novamente."
    ]);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);
if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "O corpo da requisição deve ser um JSON válido."
    ]);
    exit;
}

$rua = trim(is_string($dados['rua'] ?? null) ? $dados['rua'] : '');
$cidade = trim(is_string($dados['cidade'] ?? null) ? $dados['cidade'] : '');
$estado = trim(is_string($dados['estado'] ?? null) ? $dados['estado'] : '');
$pais = trim(is_string($dados['pais'] ?? null) ? $dados['pais'] : '');
$cep = preg_replace('/\D/', '', is_string($dados['cep'] ?? null) ? $dados['cep'] : '');

if (
    $rua === '' || strlen($rua) > 150
    || $cidade === '' || strlen($cidade) > 100
    || $estado === '' || strlen($estado) > 100
    || $pais === '' || strlen($pais) > 100
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Preencha rua, cidade, estado e país com valores válidos."
    ]);
    exit;
}

if (!is_string($cep) || !preg_match('/^\d{8}$/D', $cep)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Informe um CEP válido com 8 dígitos."
    ]);
    exit;
}

try {
    require_once __DIR__ . "/../config/database.php";
    require_once __DIR__ . "/registrarLog.php";

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT id, rua, cidade, estado, pais, cep
         FROM enderecos
         WHERE id_paciente = :id_paciente
         ORDER BY (status = 'ativo') DESC, id DESC
         LIMIT 1
         FOR UPDATE"
    );
    $stmt->execute([
        "id_paciente" => $_SESSION['usuario_id']
    ]);
    $enderecoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    $enderecoEnviado = [
        'rua' => $rua,
        'cidade' => $cidade,
        'estado' => $estado,
        'pais' => $pais,
        'cep' => $cep,
    ];
    $camposAlterados = [];
    foreach ([
        'rua' => 'Rua',
        'cidade' => 'Cidade',
        'estado' => 'Estado',
        'pais' => 'País',
        'cep' => 'CEP',
    ] as $campo => $rotulo) {
        if (!$enderecoAtual || (string) $enderecoAtual[$campo] !== $enderecoEnviado[$campo]) {
            $camposAlterados[] = $rotulo;
        }
    }

    if ($enderecoAtual) {
        $stmt = $pdo->prepare(
            "UPDATE enderecos
             SET rua = :rua, cidade = :cidade, estado = :estado,
                 pais = :pais, cep = :cep, status = 'ativo'
             WHERE id = :id AND id_paciente = :id_paciente"
        );
        $stmt->execute([
            "rua" => $rua,
            "cidade" => $cidade,
            "estado" => $estado,
            "pais" => $pais,
            "cep" => $cep,
            "id" => $enderecoAtual['id'],
            "id_paciente" => $_SESSION['usuario_id']
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO enderecos (id_paciente, rua, cep, cidade, estado, pais, status)
             VALUES (:id_paciente, :rua, :cep, :cidade, :estado, :pais, 'ativo')"
        );
        $stmt->execute([
            "id_paciente" => $_SESSION['usuario_id'],
            "rua" => $rua,
            "cep" => $cep,
            "cidade" => $cidade,
            "estado" => $estado,
            "pais" => $pais
        ]);
    }

    if ($camposAlterados !== []) {
        registrarLog(
            $pdo,
            $_SESSION['usuario_id'],
            "ENDERECO_ATUALIZADO",
            "Campos alterados: " . implode(", ", $camposAlterados) . ".",
            "enderecos",
            $_SERVER['REMOTE_ADDR'] ?? null,
            "INFO"
        );
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Endereço atualizado com sucesso.",
        "endereco" => [
            "rua" => $rua,
            "cidade" => $cidade,
            "estado" => $estado,
            "pais" => $pais,
            "cep" => $cep
        ]
    ]);
} catch (PDOException $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Falha ao atualizar endereço do paciente: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível atualizar o endereço no momento."
    ]);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Falha ao atualizar endereço do paciente: " . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível atualizar o endereço no momento."
    ]);
}
