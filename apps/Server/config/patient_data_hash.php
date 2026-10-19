<?php

function getPatientDataHashKey(): string
{
    $encodedKey = getenv('PATIENT_DATA_HASH_KEY');
    $key = $encodedKey === false ? false : base64_decode($encodedKey, true);

    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException(
            'PATIENT_DATA_HASH_KEY must contain a base64-encoded 32-byte key.'
        );
    }

    return $key;
}

function hashPatientSensitiveValue(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    return 'hmac:v1:' . hash_hmac('sha256', trim($value), getPatientDataHashKey());
}

function isPatientSensitiveValueHashed(?string $value): bool
{
    return $value !== null && preg_match('/^hmac:v1:[a-f0-9]{64}$/', $value) === 1;
}
