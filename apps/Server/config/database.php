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
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (!str_contains($line, '=')) {
                    continue;
                }

                [$key, $value] = array_map('trim', explode('=', $line, 2));
                $key = trim($key);
                $value = trim($value);

                if ($key === '') {
                    continue;
                }

                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
            return;
        }
    }
}

loadDotenvFromProjectRoot();

$readEnv = static function (string $key, $fallback = null) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        $value = $_ENV[$key] ?? $fallback;
    }

    return $value !== null ? trim((string) $value) : $fallback;
};

$isProduction = getenv('RAILWAY_ENVIRONMENT') !== false || getenv('RAILWAY_PUBLIC_DOMAIN') !== false || getenv('PORT') !== false;

// Em produção, nunca usamos localhost. Se as variáveis do banco não forem configuradas,
// a aplicação deve falhar de forma explícita para evitar erros silenciosos em runtime.
if ($isProduction) {
    $host = $readEnv('DB_HOST', 'mysql.railway.internal');
    $port = $readEnv('DB_PORT', '3306');
    $db   = $readEnv('DB_NAME', 'railway');
    $user = $readEnv('DB_USER', 'root');
    $pass = $readEnv('DB_PASS');
    $charset = $readEnv('DB_CHARSET', 'utf8mb4');

    if (!$host || !$port || !$db || !$user || !$pass) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Configuração do banco ausente em produção. Defina DB_HOST, DB_PORT, DB_NAME, DB_USER e DB_PASS no Railway."
        ]);
        exit;
    }
} else {
    // Ambiente local: usa valores seguros para desenvolvimento local.
    $host = $readEnv('DB_HOST', 'localhost');
    $port = $readEnv('DB_PORT', '3306');
    $db   = $readEnv('DB_NAME', 'continuum');
    $user = $readEnv('DB_USER', 'root');
    $pass = $readEnv('DB_PASS', '');
    $charset = $readEnv('DB_CHARSET', 'utf8mb4');
}

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