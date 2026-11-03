<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, OPTIONS');
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "error" => "Método não permitido."
    ]);
    exit;
}

require_once __DIR__ . "/../../config/database.php";

try {
    $query = "SELECT COUNT(*) FROM plantoes WHERE status = 'encerrado'";
    $stmt = $pdo->query($query);

    echo json_encode([
        "success" => true,
        "kpi_value" => (int) $stmt->fetchColumn()
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Erro ao consultar os dados do banco."
    ]);
}