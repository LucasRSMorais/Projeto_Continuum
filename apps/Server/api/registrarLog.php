<?php

/**
 * Registra eventos de auditoria na tabela logs_auditoria.
 *
 * Essa função foi ajustada para refletir a estrutura criada no MySQL Workbench,
 * com suporte a dados de contexto como IP, user-agent, endpoint e severity.
 */
function registrarLog(
    $pdo,
    $usuario_id,
    $acao,
    $descricao,
    $tabelaAfetada = 'geral',
    $ipAddress = null,
    $userAgent = null,
    $requestId = null,
    $severity = 'INFO'
) {
    $dataHora = date('Y-m-d H:i:s');

    $sql = "
        INSERT INTO logs_auditoria
        (usuario_id, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, user_agent, request_id, severity)
        VALUES
        (:usuario_id, :acao_realizada, :tabela_afetada, :data_hora, :descricao, :ip_address, :user_agent, :request_id, :severity)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':acao_realizada' => $acao,
        ':tabela_afetada' => $tabelaAfetada,
        ':data_hora' => $dataHora,
        ':descricao' => $descricao,
        ':ip_address' => $ipAddress,
        ':user_agent' => $userAgent,
        ':request_id' => $requestId,
        ':severity' => $severity,
    ]);
}

