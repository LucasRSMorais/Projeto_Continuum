<?php
require_once __DIR__ . '/../../config/cors.php';
applyCorsHeaders();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, OPTIONS');
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido.'
    ]);
    exit;
}

session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'None'
]);
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão expirada. Entre novamente.'
    ]);
    exit;
}

if (!isset($_SESSION['perfil'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso permitido apenas a profissionais da equipe.'
    ]);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$idSetor = filter_input(INPUT_GET, 'id_setor', FILTER_VALIDATE_INT);
if ($idSetor === false || $idSetor === null || $idSetor < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'O setor informado é inválido.'
    ]);
    exit;
}

try {
    $occupancyQuery = $pdo->prepare("
        SELECT COUNT(l.id) AS total_beds,
               COALESCE(SUM(l.status = 'ocupado'), 0) AS occupied_beds
        FROM enfermarias e
        JOIN leitos l ON l.id_enfermaria = e.id
        WHERE e.id_setor = :id_setor
          AND e.status = 'ativo'
          AND l.status <> 'bloqueado'
    ");
    $occupancyQuery->execute(['id_setor' => $idSetor]);
    $occupancy = $occupancyQuery->fetch();
    $totalBeds = (int) $occupancy['total_beds'];
    $occupiedBeds = (int) $occupancy['occupied_beds'];

    $eventsQuery = $pdo->prepare("
        SELECT COUNT(ea.id) AS total
        FROM eventos_adversos ea
        WHERE ea.data_evento >= NOW() - INTERVAL 12 HOUR
          AND EXISTS (
              SELECT 1
              FROM pacientes_enfermarias pe
              JOIN enfermarias e ON e.id = pe.id_enfermaria
              WHERE pe.id_paciente = ea.id_paciente
                AND pe.id = (
                    SELECT MAX(current_pe.id)
                    FROM pacientes_enfermarias current_pe
                    WHERE current_pe.id_paciente = ea.id_paciente
                )
                AND e.id_setor = :id_setor
          )
    ");
    $eventsQuery->execute(['id_setor' => $idSetor]);
    $adverseEvents = (int) $eventsQuery->fetchColumn();

    $alertsQuery = $pdo->query("
        SELECT COUNT(*) AS total
        FROM logs_alertas
        WHERE severidade = 'CRITICAL'
          AND status <> 'resolved'
          AND STR_TO_DATE(criado_em, '%Y-%m-%d %H:%i:%s') >= CURDATE()
          AND STR_TO_DATE(criado_em, '%Y-%m-%d %H:%i:%s') < CURDATE() + INTERVAL 1 DAY
    ");
    $criticalAlerts = (int) $alertsQuery->fetchColumn();

    $highRiskQuery = $pdo->prepare("
        SELECT COUNT(DISTINCT ea.id_paciente) AS total
        FROM eventos_adversos ea
        WHERE ea.grau_dano = 'alto'
          AND ea.status_investigacao IN ('pendente', 'em_investigacao')
          AND EXISTS (
              SELECT 1
              FROM pacientes_enfermarias pe
              JOIN enfermarias e ON e.id = pe.id_enfermaria
              WHERE pe.id_paciente = ea.id_paciente
                AND pe.id = (
                    SELECT MAX(current_pe.id)
                    FROM pacientes_enfermarias current_pe
                    WHERE current_pe.id_paciente = ea.id_paciente
                )
                AND e.id_setor = :id_setor
          )
    ");
    $highRiskQuery->execute(['id_setor' => $idSetor]);
    $highRiskPatients = (int) $highRiskQuery->fetchColumn();

    echo json_encode([
        'success' => true,
        'data' => [
            'occupancy' => [
                'percentage' => $totalBeds > 0 ? (int) round($occupiedBeds / $totalBeds * 100) : 0,
                'occupied' => $occupiedBeds,
                'total' => $totalBeds
            ],
            'adverseEventsLastShift' => $adverseEvents,
            'criticalAlertsToday' => $criticalAlerts,
            'highRiskPatients' => $highRiskPatients
        ]
    ]);
} catch (PDOException $exception) {
    error_log('Dashboard KPI query failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível consultar os KPIs do painel.'
    ]);
}
