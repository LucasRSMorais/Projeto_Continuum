<?php

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
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

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

function applyCorsHeaders(): void
{
    $allowedOrigins = [
        'http://localhost:5173',
        'https://localhost:5173',
        'http://127.0.0.1:5173',
        'https://127.0.0.1:5173',
    ];

    $clientUrl = getenv('CLIENT_URL');
    if ($clientUrl) {
        $allowedOrigins[] = rtrim($clientUrl, '/');
    }

    $railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN');
    if ($railwayDomain) {
        $allowedOrigins[] = 'https://' . trim($railwayDomain, '/');
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $originToUse = null;

    foreach ($allowedOrigins as $allowedOrigin) {
        if ($origin !== '' && $origin === $allowedOrigin) {
            $originToUse = $origin;
            break;
        }
    }

    if ($originToUse === null && $origin !== '') {
        $originToUse = $origin;
    }

    if ($originToUse === null && $clientUrl) {
        $originToUse = rtrim($clientUrl, '/');
    }

    if ($originToUse === null) {
        $originToUse = 'http://localhost:5173';
    }

    header('Access-Control-Allow-Origin: ' . $originToUse);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
    header('Vary: Origin');
}
