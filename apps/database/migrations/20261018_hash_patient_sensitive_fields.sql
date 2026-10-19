USE railway;

-- Run this after 20261018_prepare_patient_sensitive_hash_fields.sql.
-- Back up the database, set PATIENT_DATA_HASH_KEY, and run:
-- php apps/Server/scripts/hash_patient_sensitive_fields.php
-- If values were encrypted by the earlier AES setup, also set
-- PATIENT_DATA_ENCRYPTION_KEY temporarily while running that script.
-- Run this migration only after the conversion script succeeds.
ALTER TABLE pacientes
    MODIFY COLUMN sexo VARCHAR(80) NOT NULL,
    MODIFY COLUMN etnia VARCHAR(80) NOT NULL;
