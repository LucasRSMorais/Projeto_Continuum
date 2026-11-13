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
    "message" => "Este endpoint de revogação por e-mail foi desativado. Use a tela autenticada de documentos e consentimentos."
]);
