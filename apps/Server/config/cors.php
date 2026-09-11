<?php

function applyCorsHeaders(): void
{
    $allowedOrigins = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
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
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}
