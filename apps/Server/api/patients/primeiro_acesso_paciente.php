<?php

require_once __DIR__ . '/../../vendor/autoload.php';
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST, OPTIONS');
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../shared/catalogo_termos_paciente.php";
$documentos = catalogoTermosPaciente();

$dados = json_decode(file_get_contents("php://input"), true);
if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Corpo da requisição inválido."]);
    exit;
}

$acao = $dados['acao'] ?? '';
$email = strtolower(trim((string) ($dados['email'] ?? '')));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Informe um e-mail válido."]);
    exit;
}

try {
    if ($acao === 'solicitar_codigo') {
        $pdo->beginTransaction();
        $stmtPaciente = $pdo->prepare(
            "SELECT id, nome_completo, email, acesso_ativado
             FROM pacientes
             WHERE LOWER(email) = :email
             LIMIT 1
             FOR UPDATE"
        );
        $stmtPaciente->execute([':email' => $email]);
        $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

        if (!$paciente || (int) $paciente['acesso_ativado'] === 1) {
            $pdo->commit();
            echo json_encode([
                "success" => true,
                "message" => "Se a conta puder ser ativada, enviaremos um código ao e-mail cadastrado."
            ]);
            exit;
        }

        $stmtCodigo = $pdo->prepare(
            "SELECT solicitado_em_utc > UTC_TIMESTAMP(6) - INTERVAL 60 SECOND AS aguardar
             FROM codigos_primeiro_acesso_paciente
             WHERE id_paciente = :id
             FOR UPDATE"
        );
        $stmtCodigo->execute([':id' => $paciente['id']]);
        if ((int) ($stmtCodigo->fetchColumn() ?: 0) === 1) {
            $pdo->commit();
            echo json_encode([
                "success" => true,
                "message" => "Se a conta puder ser ativada, enviaremos um código ao e-mail cadastrado."
            ]);
            exit;
        }

        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codigoHash = password_hash($codigo, PASSWORD_ARGON2ID);
        if ($codigoHash === false) {
            throw new RuntimeException('Não foi possível gerar o código de ativação.');
        }

        $stmtSalvarCodigo = $pdo->prepare(
            "INSERT INTO codigos_primeiro_acesso_paciente
                (id_paciente, codigo_hash, solicitado_em_utc, expira_em_utc, tentativas)
             VALUES
                (:id, :codigo_hash, UTC_TIMESTAMP(6), DATE_ADD(UTC_TIMESTAMP(6), INTERVAL 10 MINUTE), 0)
             ON DUPLICATE KEY UPDATE
                codigo_hash = VALUES(codigo_hash),
                solicitado_em_utc = UTC_TIMESTAMP(6),
                expira_em_utc = DATE_ADD(UTC_TIMESTAMP(6), INTERVAL 10 MINUTE),
                tentativas = 0"
        );
        $stmtSalvarCodigo->execute([
            ':id' => $paciente['id'],
            ':codigo_hash' => $codigoHash,
        ]);

        $resendApiKey = getenv('RESEND_API_KEY');
        if (!$resendApiKey) {
            throw new RuntimeException('O serviço de envio de e-mail não está configurado.');
        }

        $nome = htmlspecialchars($paciente['nome_completo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $resend = Resend::client($resendApiKey);
        $resend->emails->send([
            'from' => 'Continuum <seguranca@projcontinnum.com.br>',
            'to' => [$paciente['email']],
            'subject' => 'Código para primeiro acesso ao Continuum',
            'html' => "<p>Olá, {$nome}.</p>
                <p>Seu código para ativar o acesso ao Portal do Paciente é:</p>
                <h2>{$codigo}</h2>
                <p>O código expira em 10 minutos. Se você não solicitou este acesso, ignore esta mensagem.</p>",
        ]);

        $pdo->commit();
        echo json_encode([
            "success" => true,
            "message" => "Se a conta puder ser ativada, enviaremos um código ao e-mail cadastrado."
        ]);
        exit;
    }

    if ($acao !== 'ativar') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ação inválida."]);
        exit;
    }

    $codigo = trim((string) ($dados['codigo'] ?? ''));
    $senha = (string) ($dados['senha'] ?? '');
    $aceitaTermo = ($dados['aceita_termo_uso_servico'] ?? false) === true;

    if (!preg_match('/^\d{6}$/', $codigo)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Informe o código de seis dígitos enviado por e-mail."]);
        exit;
    }
    if (strlen($senha) < 12) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "A senha deve ter pelo menos 12 caracteres."]);
        exit;
    }
    if (!$aceitaTermo) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "É necessário aceitar o Termo de Uso e Serviço para ativar o acesso."]);
        exit;
    }

    $pdo->beginTransaction();
    $stmtPaciente = $pdo->prepare(
        "SELECT id, acesso_ativado
         FROM pacientes
         WHERE LOWER(email) = :email
         LIMIT 1
         FOR UPDATE"
    );
    $stmtPaciente->execute([':email' => $email]);
    $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

    if (!$paciente || (int) $paciente['acesso_ativado'] === 1) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Não foi possível validar o primeiro acesso. Solicite um novo código."]);
        exit;
    }

    $stmtCodigo = $pdo->prepare(
        "SELECT codigo_hash, tentativas, expira_em_utc > UTC_TIMESTAMP(6) AS valido
         FROM codigos_primeiro_acesso_paciente
         WHERE id_paciente = :id
         FOR UPDATE"
    );
    $stmtCodigo->execute([':id' => $paciente['id']]);
    $registroCodigo = $stmtCodigo->fetch(PDO::FETCH_ASSOC);

    if (
        !$registroCodigo
        || (int) $registroCodigo['valido'] !== 1
        || (int) $registroCodigo['tentativas'] >= 5
    ) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Código inválido ou expirado. Solicite um novo código."]);
        exit;
    }

    if (!password_verify($codigo, $registroCodigo['codigo_hash'])) {
        $stmtFalha = $pdo->prepare(
            "UPDATE codigos_primeiro_acesso_paciente
             SET tentativas = tentativas + 1
             WHERE id_paciente = :id"
        );
        $stmtFalha->execute([':id' => $paciente['id']]);
        $pdo->commit();
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Código inválido."]);
        exit;
    }

    $senhaHash = password_hash(
        $senha,
        PASSWORD_ARGON2ID,
        ["memory_cost" => 65536, "time_cost" => 3, "threads" => 2]
    );
    if ($senhaHash === false) {
        throw new RuntimeException('Não foi possível proteger a senha.');
    }

    $stmtAtivar = $pdo->prepare(
        "UPDATE pacientes
         SET senha_hash = :senha_hash, acesso_ativado = 1
         WHERE id = :id AND acesso_ativado = 0"
    );
    $stmtAtivar->execute([
        ':senha_hash' => $senhaHash,
        ':id' => $paciente['id'],
    ]);
    if ($stmtAtivar->rowCount() !== 1) {
        throw new RuntimeException('Não foi possível concluir a ativação da conta.');
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = null;
    }
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $stmtEvento = $pdo->prepare(
        "INSERT INTO eventos_termos_paciente
            (id_paciente, documento, versao, acao, hash_documento, data_hora_utc, ip_address, user_agent)
         VALUES
            (:id, :documento, :versao, 'aceite', :hash, UTC_TIMESTAMP(6), :ip, :user_agent)"
    );

    $documento = 'termo_uso_servico';
    $stmtEvento->execute([
        ':id' => $paciente['id'],
        ':documento' => $documento,
        ':versao' => $documentos[$documento]['versao'],
        ':hash' => $documentos[$documento]['hash'],
        ':ip' => $ip,
        ':user_agent' => $userAgent !== '' ? $userAgent : null,
    ]);

    $stmtExcluirCodigo = $pdo->prepare(
        "DELETE FROM codigos_primeiro_acesso_paciente WHERE id_paciente = :id"
    );
    $stmtExcluirCodigo->execute([':id' => $paciente['id']]);

    $stmtLog = $pdo->prepare(
        "INSERT INTO logs_auditoria
            (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
         VALUES
            (:id, 'PRIMEIRO_ACESSO_PACIENTE', 'eventos_termos_paciente', UTC_TIMESTAMP(), :descricao, :ip, 'INFO')"
    );
    $stmtLog->execute([
        ':id' => $paciente['id'],
        ':descricao' => 'Conta ativada e aceite do Termo de Uso e Serviço versão 1.0 registrado.',
        ':ip' => $ip,
    ]);

    $pdo->commit();
    echo json_encode([
        "success" => true,
        "message" => "Acesso ativado. Agora você pode entrar com seu e-mail e a senha criada."
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Patient first access failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível concluir a solicitação. Tente novamente mais tarde."
    ]);
}
