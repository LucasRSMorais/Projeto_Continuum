<?php

// Arquivo responsável por conectar a API ao banco de dados MySQL.
// Ele cria o objeto $pdo, que será reutilizado pelas páginas que fazem consultas.

$host = getenv('DB_HOST') ?: 'mysql-16a584b0-continuum.a.aivencloud.com';
$port = getenv('DB_PORT') ?: '16138';
$db   = getenv('DB_NAME') ?: 'continuumdb';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASS') ?: 'DB_PASSWORD';

$caCandidates = [
    getenv('DB_SSL_CA') ?: '',
    __DIR__ . '/certs/ca.pem',
    dirname(__DIR__) . '/config/certs/ca.pem',
    dirname(__DIR__, 2) . '/config/certs/ca.pem'
];

$caCert = null;
foreach ($caCandidates as $candidate) {
    if ($candidate !== '' && file_exists($candidate)) {
        $caCert = $candidate;
        break;
    }
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if ($caCert !== null) {
        $pdoOptions[PDO::MYSQL_ATTR_SSL_CA] = $caCert;
        $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    // Cria a conexão com o banco usando PDO.
    $pdo = new PDO($dsn, $user, $pass, $pdoOptions);

} catch (PDOException $e) {
    // Se a conexão falhar, a API retorna erro 500 em JSON.
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erro ao conectar ao banco de dados.",
        "details" => $e->getMessage()
    ]);

    exit;
}