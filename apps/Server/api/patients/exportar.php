<?php

header("Content-Type: application/json; charset=UTF-8");

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, OPTIONS');
    echo json_encode([
        "success" => false,
        "message" => "Método não permitido."
    ]);
    exit;
}

if (
    !isset($_SESSION['usuario_id'])
    || !is_numeric($_SESSION['usuario_id'])
    || ($_SESSION['2fa_verificado'] ?? false) !== true
    || ($_SESSION['tipo_usuario'] ?? '') !== 'paciente'
) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Faça login novamente para exportar seus dados."
    ]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/patient_data_encryption.php";

$idPaciente = (int) $_SESSION['usuario_id'];

try {
    $stmt = $pdo->prepare(
        "SELECT nome_completo, email, cpf, telefone, data_nascimento,
                sexo, etnia, alergias, aceite_termo_uso, endereco
         FROM pacientes
         WHERE id = :id
         LIMIT 1"
    );
    $stmt->execute([
        'id' => $idPaciente
    ]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Paciente não encontrado."
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT rua, numero, cep, cidade, estado, pais, status
         FROM enderecos
         WHERE id_paciente = :id_paciente OR id = :endereco_id
         ORDER BY id DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente,
        'endereco_id' => $paciente['endereco']
    ]);
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT a.tipo, a.gravidade, a.contra_medida
         FROM alergias_pacientes ap
         INNER JOIN alergias a ON a.id = ap.id_alergia
         WHERE ap.id_paciente = :id_paciente
         ORDER BY a.tipo"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $alergias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT nome_evento, tipo, finalidade, data_aceite, data_revogacao, status
         FROM eventos_assinaturas
         WHERE id_paciente = :id_paciente
         ORDER BY data_aceite DESC, nome_evento"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $consentimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT documento, versao, acao, hash_documento, data_hora_utc,
                ip_address, user_agent
         FROM eventos_termos_paciente
         WHERE id_paciente = :id_paciente
         ORDER BY data_hora_utc DESC, id DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $historicoTermos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT tipo_evento, grau_dano, sinais_vitais, descricao_fato,
                data_evento, status_investigacao
         FROM eventos_adversos
         WHERE id_paciente = :id_paciente
         ORDER BY data_evento DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $eventosAdversos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT m.principio_ativo, m.nome_comercial, m.tipo, m.dosagem,
                me.dose_administrada, me.concentracao, me.via_administrada,
                me.frequencia, me.diluicao, me.tempo_infusao,
                me.local_aplicacao, me.data_aplicacao
         FROM medicacoes_eventos me
         INNER JOIN medicacoes m ON m.id = me.id_medicamento
         INNER JOIN eventos_adversos ea ON ea.id = me.id_evento
         WHERE ea.id_paciente = :id_paciente
         ORDER BY me.data_aplicacao DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $medicacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT pe.movimentacao_leito, pe.ultima_atualizacao,
                l.numero AS leito, e.status AS status_enfermaria,
                s.nome AS setor
         FROM pacientes_enfermarias pe
         INNER JOIN enfermarias e ON e.id = pe.id_enfermaria
         LEFT JOIN leitos l ON l.id = pe.id_leito
         LEFT JOIN setores s ON s.id = e.id_setor
         WHERE pe.id_paciente = :id_paciente
         ORDER BY pe.ultima_atualizacao DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $internacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT origem, gravidade, status, data_solicitacao
         FROM fila_admissoes
         WHERE id_paciente = :id_paciente
         ORDER BY data_solicitacao DESC"
    );
    $stmt->execute([
        'id_paciente' => $idPaciente
    ]);
    $admissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dadosExportados = [
        'exportado_em' => gmdate(DATE_ATOM),
        'paciente' => [
            'nome_completo' => $paciente['nome_completo'],
            'email' => $paciente['email'],
            'cpf' => $paciente['cpf'],
            'telefone' => $paciente['telefone'],
            'data_nascimento' => $paciente['data_nascimento'],
            'sexo' => decryptPatientSensitiveValue($paciente['sexo']),
            'etnia' => decryptPatientSensitiveValue($paciente['etnia']),
            'alergias_informadas' => $paciente['alergias'],
            'aceite_termo_uso' => $paciente['aceite_termo_uso']
        ],
        'enderecos' => $enderecos,
        'alergias' => $alergias,
        'consentimentos' => $consentimentos,
        'historico_termos_e_consentimentos' => $historicoTermos,
        'eventos_adversos' => $eventosAdversos,
        'medicacoes_administradas' => $medicacoes,
        'internacoes_e_movimentacoes' => $internacoes,
        'solicitacoes_de_admissao' => $admissoes
    ];

    $json = json_encode(
        $dadosExportados,
        JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
    );

    header('Content-Type: application/json; charset=UTF-8');
    header(
        'Content-Disposition: attachment; filename="meus-dados-continuum.json"'
    );
    header('Cache-Control: no-store, private');
    echo $json;
} catch (Throwable $error) {
    error_log('Patient data export failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Não foi possível exportar seus dados no momento."
    ]);
}
