<?php

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../../config/cors.php";
applyCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

http_response_code(410);
echo json_encode([
    "success" => false,
    "message" => "O cadastro público de pacientes foi desativado. Use o primeiro acesso para ativar uma conta provisionada pela instituição."
]);
