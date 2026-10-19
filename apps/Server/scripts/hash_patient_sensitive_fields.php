<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/patient_data_hash.php';

function decryptLegacyPatientValue(string $value): string
{
    if (!str_starts_with($value, 'enc:v1:')) {
        return $value;
    }

    if (!function_exists('openssl_decrypt')) {
        throw new RuntimeException('The OpenSSL extension is required to convert previously encrypted data.');
    }

    $encodedKey = getenv('PATIENT_DATA_ENCRYPTION_KEY');
    $key = $encodedKey === false ? false : base64_decode($encodedKey, true);
    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException(
            'Set the previous PATIENT_DATA_ENCRYPTION_KEY to convert encrypted records.'
        );
    }

    $payload = base64_decode(substr($value, 7), true);
    if ($payload === false || strlen($payload) < 28) {
        throw new RuntimeException('A previously encrypted patient value is invalid.');
    }

    $plaintext = openssl_decrypt(
        substr($payload, 28),
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        substr($payload, 0, 12),
        substr($payload, 12, 16)
    );

    if ($plaintext === false) {
        throw new RuntimeException('A previously encrypted patient value could not be decrypted.');
    }

    return $plaintext;
}

try {
    getPatientDataHashKey();
    $pdo->beginTransaction();
    $patients = $pdo->query('SELECT id, sexo, etnia FROM pacientes')->fetchAll();
    $update = $pdo->prepare(
        'UPDATE pacientes SET sexo = :sexo, etnia = :etnia WHERE id = :id'
    );
    $updatedCount = 0;

    foreach ($patients as $patient) {
        $sexo = (string) $patient['sexo'];
        $etnia = (string) $patient['etnia'];

        if (!isPatientSensitiveValueHashed($sexo)) {
            $sexo = hashPatientSensitiveValue(decryptLegacyPatientValue($sexo));
        }
        if (!isPatientSensitiveValueHashed($etnia)) {
            $etnia = hashPatientSensitiveValue(decryptLegacyPatientValue($etnia));
        }

        if ($sexo === $patient['sexo'] && $etnia === $patient['etnia']) {
            continue;
        }

        $update->execute([
            ':sexo' => $sexo,
            ':etnia' => $etnia,
            ':id' => $patient['id'],
        ]);
        $updatedCount++;
    }

    $pdo->commit();
    fwrite(STDOUT, "Hashed patient sensitive fields for {$updatedCount} patient(s).\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Patient sensitive data hashing migration failed: ' . $e->getMessage());
    fwrite(STDERR, "Failed to hash patient sensitive fields. Check the server log.\n");
    exit(1);
}
