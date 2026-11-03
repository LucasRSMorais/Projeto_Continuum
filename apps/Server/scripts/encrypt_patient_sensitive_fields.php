<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/patient_data_encryption.php';

try {
    getPatientDataEncryptionKey();
    $pdo->beginTransaction();
    $patients = $pdo->query('SELECT id, sexo, etnia FROM pacientes')->fetchAll();
    $update = $pdo->prepare(
        'UPDATE pacientes SET sexo = :sexo, etnia = :etnia WHERE id = :id'
    );
    $updatedCount = 0;

    foreach ($patients as $patient) {
        foreach (['sexo', 'etnia'] as $field) {
            if (str_starts_with((string) $patient[$field], 'hmac:v1:')) {
                throw new RuntimeException(
                    "Patient {$field} for record {$patient['id']} is an irreversible hash and cannot be restored."
                );
            }
        }

        $sexo = isPatientSensitiveValueEncrypted($patient['sexo'])
            ? $patient['sexo']
            : encryptPatientSensitiveValue($patient['sexo']);
        $etnia = isPatientSensitiveValueEncrypted($patient['etnia'])
            ? $patient['etnia']
            : encryptPatientSensitiveValue($patient['etnia']);

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
    fwrite(STDOUT, "Encrypted patient sensitive fields for {$updatedCount} patient(s).\n");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Patient sensitive data migration failed: ' . $e->getMessage());
    fwrite(STDERR, "Failed to encrypt patient sensitive fields. Check the server log.\n");
    exit(1);
}
