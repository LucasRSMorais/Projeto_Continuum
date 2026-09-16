<?php

// Arquivo responsável por conectar a API ao banco de dados MySQL.
// Ele cria o objeto $pdo, que será reutilizado pelas páginas que fazem consultas.
// Esse arquivo centraliza as configurações e evita duplicação de conexão.

if (!function_exists('loadDotenvFromProjectRoot')) {
    function loadDotenvFromProjectRoot(): void
    {
        $dotenvCandidates = [
            __DIR__ . '/../.env',
            dirname(__DIR__) . '/.env',
            dirname(__DIR__, 2) . '/.env',
        ];

        foreach ($dotenvCandidates as $dotenvFile) {
            if (!is_file($dotenvFile)) {
                continue;
            }

            $lines = file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {continue;}

                if (!str_contains($line, '=')) {continue;}

                [$key, $value] = array_map('trim', explode('=', $line, 2));
                $key = trim($key);
                $value = trim($value);

                if ($key === '') {
                    continue;
                }

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }

                if (getenv($key) === false) {
                    putenv($key . '=' . $value);
                }
            }
            return;
        }
    }
}

loadDotenvFromProjectRoot();

// Carrega as variáveis de conexão do ambiente.
// Quando não há valores definidos, usa configurações padrão para ambiente local.
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'continuum';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$caCert = null;
if (getenv('DB_SSL_CA')) {
    $candidate = getenv('DB_SSL_CA');
    if (file_exists($candidate)) {
        $caCert = $candidate;
    }
}

// DSN do PDO: define o driver, host, porta, banco e charset.
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

try {
    // Opções de conexão robustas para tratamento de erros e leitura padrão em arrays associativos.
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
        "message" => "Erro ao conectar ao banco de dados."
    ]);

    exit;
}