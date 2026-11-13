<?php

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

if (
    isset($_SESSION['ultima_atividade'])
    && time() - $_SESSION['ultima_atividade'] > 30 * 60
) {
    $_SESSION = [];
    session_destroy();
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Sessão expirada."]);
    exit;
}

$_SESSION['ultima_atividade'] = time();

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (
    !isset($_SESSION['usuario_id'])
    || ($_SESSION['tipo_usuario'] ?? '') !== 'paciente'
    || ($_SESSION['2fa_verificado'] ?? false) !== true
) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Sessão de paciente necessária."]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../shared/catalogo_termos_paciente.php";
$documentos = catalogoTermosPaciente();

$idPaciente = (int) $_SESSION['usuario_id'];
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($metodo === 'GET') {
        $stmt = $pdo->prepare(
            "SELECT eventos.documento, eventos.versao, eventos.acao,
                    eventos.hash_documento, eventos.data_hora_utc
             FROM eventos_termos_paciente AS eventos
             INNER JOIN (
                 SELECT documento, MAX(id) AS ultimo_id
                 FROM eventos_termos_paciente
                 WHERE id_paciente = :id_paciente
                 GROUP BY documento
             ) AS ultimos ON ultimos.ultimo_id = eventos.id"
        );
        $stmt->execute([':id_paciente' => $idPaciente]);
        $registros = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $registro) {
            $registros[$registro['documento']] = [
                'versao' => $registro['versao'],
                'acao' => $registro['acao'],
                'hash' => $registro['hash_documento'],
                'data_hora_utc' => $registro['data_hora_utc'],
            ];
        }

        $resultado = [];
        foreach ($documentos as $chave => $documento) {
            $registro = $registros[$chave] ?? null;
            $resultado[] = [
                'chave' => $chave,
                'titulo' => $documento['titulo'],
                'url' => $documento['url'],
                'versao' => $registro['versao'] ?? $documento['versao'],
                'hash' => $registro['hash'] ?? $documento['hash'],
                'revogavel' => $documento['revogavel'],
                'aceitavel' => $documento['aceitavel'],
                'status' => $documento['informativo']
                    ? 'informativo'
                    : match ($registro['acao'] ?? null) {
                        'aceite' => 'aceito',
                        'revogacao' => 'revogado',
                        default => $documento['aceitavel']
                            ? 'sem_registro'
                            : 'pendente_documento',
                    },
                'data_hora_utc' => $registro['data_hora_utc'] ?? null,
            ];
        }

        echo json_encode(["success" => true, "documentos" => $resultado]);
        exit;
    }

    if ($metodo !== 'POST') {
        http_response_code(405);
        header('Allow: GET, POST, OPTIONS');
        echo json_encode(["success" => false, "message" => "Método não permitido."]);
        exit;
    }

    $dados = json_decode(file_get_contents("php://input"), true);
    $chave = is_array($dados) ? ($dados['documento'] ?? '') : '';
    $acao = is_array($dados) ? ($dados['acao'] ?? 'revogar') : '';
    if (!isset($documentos[$chave]) || !$documentos[$chave]['revogavel']) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Este documento não pode ser alterado por esta tela."]);
        exit;
    }
    if (!in_array($acao, ['aceitar', 'revogar'], true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Ação de consentimento inválida."]);
        exit;
    }
    if ($acao === 'aceitar' && !$documentos[$chave]['aceitavel']) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Este documento ainda contém campos de modelo pendentes e não pode ser aceito como autorização."
        ]);
        exit;
    }

    $pdo->beginTransaction();
    $stmtUltimo = $pdo->prepare(
        "SELECT versao, acao, hash_documento
         FROM eventos_termos_paciente
         WHERE id_paciente = :id_paciente AND documento = :documento
         ORDER BY id DESC
         LIMIT 1
         FOR UPDATE"
    );
    $stmtUltimo->execute([
        ':id_paciente' => $idPaciente,
        ':documento' => $chave,
    ]);
    $ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);
    $aceiteAtivo = $ultimo && $ultimo['acao'] === 'aceite';
    if (($acao === 'revogar' && !$aceiteAtivo) || ($acao === 'aceitar' && $aceiteAtivo)) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => $acao === 'revogar'
                ? "Não há um aceite ativo deste documento para revogar."
                : "Este consentimento já está ativo."
        ]);
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = null;
    }
    $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $evento = $acao === 'aceitar' ? 'aceite' : 'revogacao';
    $versaoEvento = $acao === 'aceitar'
        ? $documentos[$chave]['versao']
        : $ultimo['versao'];
    $hashEvento = $acao === 'aceitar'
        ? $documentos[$chave]['hash']
        : $ultimo['hash_documento'];
    $stmtEvento = $pdo->prepare(
        "INSERT INTO eventos_termos_paciente
            (id_paciente, documento, versao, acao, hash_documento, data_hora_utc, ip_address, user_agent)
         VALUES
            (:id_paciente, :documento, :versao, :acao, :hash, UTC_TIMESTAMP(6), :ip, :user_agent)"
    );
    $stmtEvento->execute([
        ':id_paciente' => $idPaciente,
        ':documento' => $chave,
        ':versao' => $versaoEvento,
        ':acao' => $evento,
        ':hash' => $hashEvento,
        ':ip' => $ip,
        ':user_agent' => $userAgent !== '' ? $userAgent : null,
    ]);

    $acaoAuditoria = $acao === 'aceitar' ? 'ACEITE_CONSENTIMENTO' : 'REVOGACAO_CONSENTIMENTO';
    if ($acao === 'revogar') {
        $stmtLogAlerta = $pdo->prepare(
            "INSERT INTO logs_alertas
                (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em)
             VALUES
                (:id_paciente, 'REVOGACAO_CONSENTIMENTO', 'HIGH', :mensagem, :ip,
                 DATE_FORMAT(UTC_TIMESTAMP(6), '%Y-%m-%dT%H:%i:%s.%fZ'))"
        );
        $stmtLogAlerta->execute([
            ':id_paciente' => $idPaciente,
            ':mensagem' => 'Paciente revogou o consentimento do documento ' . $chave . ' versão ' . $versaoEvento . '.',
            ':ip' => $ip,
        ]);
    }

    $stmtLog = $pdo->prepare(
        "INSERT INTO logs_auditoria
            (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
         VALUES
            (:id_paciente, :acao, 'eventos_termos_paciente',
             UTC_TIMESTAMP(), :descricao, :ip, :severidade)"
    );
    $stmtLog->execute([
        ':id_paciente' => $idPaciente,
        ':acao' => $acaoAuditoria,
        ':descricao' => ($acao === 'aceitar' ? 'Aceite' : 'Revogação') . ' registrada para ' . $chave . ' versão ' . $versaoEvento . '.',
        ':ip' => $ip,
        ':severidade' => $acao === 'aceitar' ? 'INFO' : 'SECURITY',
    ]);

    $pdo->commit();
    echo json_encode([
        "success" => true,
        "message" => $acao === 'aceitar'
            ? "Consentimento registrado com data e hora UTC."
            : "Revogação registrada com data e hora UTC."
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Patient consent operation failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível carregar ou atualizar seus consentimentos."
    ]);
}
