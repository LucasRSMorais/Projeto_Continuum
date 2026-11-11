-- Back up the database before applying this migration.
-- Adds the address fields used by the patient profile and address registration API.
SET @database_name = DATABASE();

SET @add_rua_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = @database_name
          AND table_name = 'enderecos'
          AND column_name = 'rua'
    ) = 0,
    'ALTER TABLE enderecos ADD COLUMN rua VARCHAR(150) NULL AFTER id_paciente',
    'SELECT 1'
);
PREPARE add_rua_statement FROM @add_rua_sql;
EXECUTE add_rua_statement;
DEALLOCATE PREPARE add_rua_statement;

SET @add_numero_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = @database_name
          AND table_name = 'enderecos'
          AND column_name = 'numero'
    ) = 0,
    'ALTER TABLE enderecos ADD COLUMN numero VARCHAR(20) NULL AFTER rua',
    'SELECT 1'
);
PREPARE add_numero_statement FROM @add_numero_sql;
EXECUTE add_numero_statement;
DEALLOCATE PREPARE add_numero_statement;
