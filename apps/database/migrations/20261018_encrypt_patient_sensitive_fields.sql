-- Back up the database before applying this migration.
-- Select the same database configured by DB_NAME for the backend before running
-- this file. Do not assume the database is named "railway".
-- Encrypted ciphertext cannot be stored in ENUM columns. The registration API
-- enforces the allowed sexo/etnia choices before encrypting them.
ALTER TABLE pacientes
    MODIFY COLUMN sexo VARCHAR(512) NOT NULL,
    MODIFY COLUMN etnia VARCHAR(512) NOT NULL;

-- After applying this schema migration, run:
-- php apps/Server/scripts/encrypt_patient_sensitive_fields.php
