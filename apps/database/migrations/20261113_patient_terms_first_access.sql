-- Back up the database before applying this migration.
-- Adds first-access activation and an append-only patient terms history.

SET @database_name = DATABASE();

SET @allow_empty_password_sql = IF(
    (
        SELECT is_nullable
        FROM information_schema.columns
        WHERE table_schema = @database_name
          AND table_name = 'pacientes'
          AND column_name = 'senha_hash'
    ) = 'NO',
    'ALTER TABLE pacientes MODIFY COLUMN senha_hash VARCHAR(255) NULL',
    'SELECT 1'
);
PREPARE allow_empty_password_statement FROM @allow_empty_password_sql;
EXECUTE allow_empty_password_statement;
DEALLOCATE PREPARE allow_empty_password_statement;

SET @add_acesso_ativado_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = @database_name
          AND table_name = 'pacientes'
          AND column_name = 'acesso_ativado'
    ) = 0,
    'ALTER TABLE pacientes ADD COLUMN acesso_ativado TINYINT(1) NOT NULL DEFAULT 0 AFTER aceite_termo_uso',
    'SELECT 1'
);
PREPARE add_acesso_ativado_statement FROM @add_acesso_ativado_sql;
EXECUTE add_acesso_ativado_statement;
DEALLOCATE PREPARE add_acesso_ativado_statement;

CREATE TABLE IF NOT EXISTS eventos_termos_paciente (
    id BIGINT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    documento VARCHAR(64) NOT NULL,
    versao VARCHAR(20) NOT NULL,
    acao ENUM('aceite', 'revogacao') NOT NULL,
    hash_documento CHAR(64) NOT NULL,
    data_hora_utc DATETIME(6) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    PRIMARY KEY (id),
    KEY idx_eventos_termos_paciente_documento (id_paciente, documento, id),
    CONSTRAINT fk_eventos_termos_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS codigos_primeiro_acesso_paciente (
    id_paciente INT NOT NULL,
    codigo_hash VARCHAR(255) NOT NULL,
    solicitado_em_utc DATETIME(6) NOT NULL,
    expira_em_utc DATETIME(6) NOT NULL,
    tentativas TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id_paciente),
    CONSTRAINT fk_codigos_primeiro_acesso_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

DELIMITER $$

DROP TRIGGER IF EXISTS trg_eventos_termos_paciente_bu_immutavel$$
CREATE TRIGGER trg_eventos_termos_paciente_bu_immutavel
BEFORE UPDATE ON eventos_termos_paciente
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Eventos de termos sao imutaveis; registre um novo evento.';
END$$

DROP TRIGGER IF EXISTS trg_eventos_termos_paciente_bd_immutavel$$
CREATE TRIGGER trg_eventos_termos_paciente_bd_immutavel
BEFORE DELETE ON eventos_termos_paciente
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Eventos de termos nao podem ser excluidos.';
END$$

DELIMITER ;
