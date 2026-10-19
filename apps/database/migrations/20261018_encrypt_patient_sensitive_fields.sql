USE railway;

ALTER TABLE pacientes
    MODIFY COLUMN sexo VARCHAR(512) NOT NULL,
    MODIFY COLUMN etnia VARCHAR(512) NOT NULL;

-- After applying this schema migration, run:
-- php apps/Server/scripts/encrypt_patient_sensitive_fields.php
