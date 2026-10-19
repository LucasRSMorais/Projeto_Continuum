USE railway;

ALTER TABLE pacientes
    MODIFY COLUMN sexo VARCHAR(512) NOT NULL,
    MODIFY COLUMN etnia VARCHAR(512) NOT NULL;

-- After this migration, run:
-- php apps/Server/scripts/hash_patient_sensitive_fields.php
-- Then apply 20261018_hash_patient_sensitive_fields.sql.
