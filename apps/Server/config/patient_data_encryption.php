<?php

function getPatientDataEncryptionKey(): string
{
    if (!function_exists('openssl_encrypt') || !function_exists('openssl_decrypt')) {
        throw new RuntimeException('The OpenSSL extension is required for patient data encryption.');
    }

    $encodedKey = getenv('PATIENT_DATA_ENCRYPTION_KEY');
    $key = $encodedKey === false ? false : base64_decode($encodedKey, true);

    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException(
            'PATIENT_DATA_ENCRYPTION_KEY must contain a base64-encoded 32-byte key.'
        );
    }

    return $key;
}

function encryptPatientSensitiveValue(?string $value): ?string
{
    if ($value === null || $value === '' || str_starts_with($value, 'enc:v1:')) {
        return $value;
    }

    $key = getPatientDataEncryptionKey();
    $nonce = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt(
        $value,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        '',
        16
    );

    if ($ciphertext === false || strlen($tag) !== 16) {
        throw new RuntimeException('Could not encrypt patient sensitive data.');
    }

    return 'enc:v1:' . base64_encode($nonce . $tag . $ciphertext);
}

function decryptPatientSensitiveValue(?string $value): ?string
{
    if ($value === null || $value === '' || !str_starts_with($value, 'enc:v1:')) {
        return $value;
    }

    $payload = base64_decode(substr($value, 7), true);
    if ($payload === false || strlen($payload) < 28) {
        throw new RuntimeException('Stored patient sensitive data is invalid.');
    }

    $key = getPatientDataEncryptionKey();
    $nonce = substr($payload, 0, 12);
    $tag = substr($payload, 12, 16);
    $ciphertext = substr($payload, 28);
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $nonce,
        $tag
    );

    if ($plaintext === false) {
        throw new RuntimeException('Could not decrypt patient sensitive data.');
    }

    return $plaintext;
}
