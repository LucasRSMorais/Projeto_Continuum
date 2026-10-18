/* Cria o banco de dados principal do projeto, caso ele ainda não exista. */
create database IF NOT EXISTS railway
	CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

/* Seleciona o banco que receberá as tabelas e os dados abaixo. */
USE railway;

-- MÉDICO
-- Entidade responsável pelo cadastro e autenticação do profissional médico.
CREATE TABLE medicos (
    id INT NOT NULL AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    crm_numero VARCHAR(10) NOT NULL,
    crm_uf VARCHAR(2) NOT NULL,
    cargo ENUM('Interno', 'Residente R1', 'Residente R2','Residente R3','Residente R4','Residente R5','Residente R+','Preceptor', 'Staff', 'outro') NOT NULL DEFAULT 'medico',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    autenticacao BOOLEAN NOT NULL DEFAULT FALSE,
    ultimo_acesso DATETIME NULL,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
	UNIQUE KEY uq_medicos_email (email),
    UNIQUE KEY uq_medicos_crm_uf (crm_numero, crm_uf)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSTITUIÇÃO
-- Representa a instituição de saúde relacionada ao sistema.
CREATE TABLE instituicoes (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    cnpj VARCHAR(14) NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- SETOR
-- Representa os setores existentes na instituição e o responsável pelo
-- plantão.
CREATE TABLE setores (
    id INT NOT NULL AUTO_INCREMENT,
    tipo ENUM(
        'Clínica Geral',
        'Cirúrgica',
        'Obstétrica e Perinatal',
        'Ortopédica',
        'Oncológica',
        'Cardiologia',
        'Neurologia',
        'Infectologia',
        'Isolamento'
    ) NOT NULL,
    responsavel_plantao INT NULL,
    class_risco ENUM('baixo', 'medio', 'alto') NOT NULL,
    PRIMARY KEY (id),
    -- FK indicada no MER: responsável pelo plantão é um Médico.
    CONSTRAINT fk_setores_responsavel_plantao
        FOREIGN KEY (responsavel_plantao)
        REFERENCES medicos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- Enfermaria
-- Representa uma enfermaria vinculada a um setor e a um médico responsável.
CREATE TABLE enfermarias (
    id INT NOT NULL AUTO_INCREMENT,
    id_setor INT NULL,
    medico_responsavel INT NULL,
    quantidade_leitos INT NOT NULL,
    leitos_disponiveis INT NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',

    PRIMARY KEY (id),

    CONSTRAINT fk_enfermarias_setor
        FOREIGN KEY (id_setor)
        REFERENCES setores(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_enfermarias_medico
        FOREIGN KEY (medico_responsavel)
        REFERENCES medicos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. LEITO
-- Representa os leitos pertencentes a uma enfermaria.
CREATE TABLE leitos (
    id INT NOT NULL AUTO_INCREMENT,
    id_enfermaria INT NULL,
    numero VARCHAR(20) NOT NULL,
    status ENUM('disponivel', 'ocupado', 'sujo', 'em_higienizacao', 'manutencao', 'bloqueado') NOT NULL DEFAULT 'bloqueado',

    PRIMARY KEY (id),

    CONSTRAINT fk_leitos_enfermaria
        FOREIGN KEY (id_enfermaria)
        REFERENCES enfermarias(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. PACIENTE
-- Entidade do titular dos dados pessoais e clínicos.
CREATE TABLE pacientes (
    id INT NOT NULL AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    cpf VARCHAR(11) NULL,
    data_nascimento DATE NULL,
    sexo ENUM('masculino', 'feminino') NOT NULL,
    etnia ENUM(
        'branca',
        'preta',
        'parda',
        'amarela',
        'indigena',
        'nao_informada',
        'outra'
    ) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
	alergias TEXT NULL,
    telefone VARCHAR(20) NULL,
    aceite_termo_uso ENUM('sim', 'nao') NOT NULL DEFAULT 'nao',
    endereco INT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pacientes_email (email)
) ENGINE=InnoDB;

-- 7. ENDEREÇO
-- Endereço relacionado ao paciente.
CREATE TABLE enderecos (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NULL,
    cep VARCHAR(8) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id),
    CONSTRAINT fk_enderecos_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;
-- Completa a segunda FK indicada no MER entre Paciente e Endereço.
-- O diagrama apresenta FKs nos dois lados; por isso ambas são mantidas.
ALTER TABLE pacientes
    ADD CONSTRAINT fk_pacientes_endereco
        FOREIGN KEY (endereco)
        REFERENCES enderecos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;


-- 8. ALERGIAS
-- Catálogo de alergias.
-- O MER apresenta gravidade como BOOLEAN. Como gravidade representa uma
-- classificação (e não apenas verdadeiro/falso), ela é convertida para ENUM.
CREATE TABLE alergias (
    id INT NOT NULL AUTO_INCREMENT,
    tipo VARCHAR(100) NOT NULL,
    gravidade ENUM('baixa', 'media', 'alta') NOT NULL,
    contra_medida VARCHAR(500) NOT NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB;


-- 9. ALERGIAS-PACIENTE
-- Tabela associativa entre Paciente e Alergias.
CREATE TABLE alergias_pacientes (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_alergias INT NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_alergias_pacientes_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_alergias_pacientes_alergia
        FOREIGN KEY (id_alergias)
        REFERENCES alergias(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;


-- 10. EVENTO-ASSINATURA
-- Registra a assinatura/aceite associada a um evento e a um paciente.
CREATE TABLE eventos_assinaturas (
    id INT NOT NULL AUTO_INCREMENT,
    id_evento INT NULL,
    id_paciente INT NULL,
    nome_evento VARCHAR(250) NOT NULL,
    tipo ENUM(
		'autorizacao_cirurgia',
		'autorizacao_procedimento',
		'autorizacao_medicacao',
		'autorizacao_tratamento',
		'autorizacao_tratamento_especial',
		'consentimento_informado',
		'consentimento_anestesia',
		'consentimento_transfusao',
		'consentimento_exame',
		'consentimento_coleta_material',
		'consentimento_internacao',
		'consentimento_compartilhamento_dados',
		'termo_responsabilidade',
		'termo_recusa_tratamento',
		'termo_recusa_procedimento',
		'termo_alta',
		'termo_alta_a_pedido',
		'termo_acompanhante',
		'autorizacao_imagem',
		'autorizacao_pesquisa',
		'outro'
    ) NOT NULL,
    finalidade VARCHAR(500) NOT NULL,
    data_aceite DATETIME NULL,
    data_revogacao DATETIME NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id),
    CONSTRAINT fk_eventos_assinaturas_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;


-- 11. EVENTO ADVERSO
-- Registra eventos adversos associados a pacientes e médicos.
-- permanece TEXT, também conforme o MER.
CREATE TABLE eventos_adversos (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_medico INT NOT NULL,
    tipo_evento ENUM(
        'pronto_socorro',
        'ambulatorio',
        'centro_cirurgico',
        'uti',
        'internacao',
		'queda',
		'movimentacao_leito',
		'identificacao_incorreta',
		'medicamento',
		'alergia',
		'infeccao',
		'procedimento',
		'cirurgia',
		'dispositivo',
		'lesao_pressao',
		'alimentacao',
		'oxigenacao',
		'transfusao',
		'exame_diagnostico',
		'comunicacao',
		'deterioracao_clinica',
		'documentacao',
		'contencao',
		'fuga',
		'acidente',
		'atraso_atendimento',
		'atraso_tratamento',
		'outro'
    ) NOT NULL,
    grau_dano ENUM('baixo', 'medio', 'alto', 'sem_dano') NOT NULL,
    sinais_vitais VARCHAR(1000) NULL,
    descricao_fato TEXT NOT NULL,
    data_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_investigacao ENUM(
        'pendente',
        'em_investigacao',
        'concluido'
    ) NOT NULL,
    id_assinatura INT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_eventos_adversos_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_eventos_adversos_medico
        FOREIGN KEY (id_medico)
        REFERENCES medicos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
    -- A FK de id_assinatura é adicionada depois da criação de
    -- eventos_assinaturas para evitar dependência circular.
) ENGINE=InnoDB;

-- Completa a FK de id_assinatura indicada no MER.
ALTER TABLE eventos_adversos
    ADD CONSTRAINT fk_eventos_adversos_assinatura
        FOREIGN KEY (id_assinatura)
        REFERENCES eventos_assinaturas(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

-- Completa a FK de id_evento indicada no MER.
ALTER TABLE eventos_assinaturas
    ADD CONSTRAINT fk_eventos_assinaturas_evento
        FOREIGN KEY (id_evento)
        REFERENCES eventos_adversos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

-- 12. MEDICAÇÃO
-- Catálogo de medicamentos.
CREATE TABLE medicacoes (
    id INT NOT NULL AUTO_INCREMENT,
    principio_ativo VARCHAR(255) NOT NULL,
    nome_comercial VARCHAR(255) NULL,
    tipo VARCHAR(100) NULL,
    dosagem VARCHAR(100) NULL,

    PRIMARY KEY (id)
) ENGINE=InnoDB;


-- 13. MEDICAÇÃO-EVENTO
-- Relaciona medicamentos aos eventos e registra os dados de administração.
CREATE TABLE medicacoes_eventos (
    id INT NOT NULL AUTO_INCREMENT,
    id_medicamento INT NOT NULL,
    id_evento INT NOT NULL,
    dose_administrada VARCHAR(100) NULL,
    concentracao VARCHAR(100) NULL,
    via_administrada ENUM(
        'oral',
        'intravenosa',
        'intramuscular',
        'subcutanea',
        'topica',
        'inalatoria',
        'outra'
    ) NULL,
    frequencia VARCHAR(100) NULL,
    diluicao VARCHAR(255) NULL,
    tempo_infusao VARCHAR(100) NULL,
    local_aplicacao VARCHAR(150) NULL,
    data_aplicacao DATETIME NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_medicacoes_eventos_medicamento
        FOREIGN KEY (id_medicamento)
        REFERENCES medicacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_medicacoes_eventos_evento
        FOREIGN KEY (id_evento)
        REFERENCES eventos_adversos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- 14. PACIENTE-ENFERMARIA
-- Registra a alocação e a movimentação de um paciente em uma enfermaria e
-- leito, podendo relacioná-la a um evento.
CREATE TABLE pacientes_enfermarias (
    id INT NOT NULL AUTO_INCREMENT,
    id_enfermaria INT NOT NULL,
    id_paciente INT NOT NULL,
    id_evento INT NOT NULL,
    id_leito INT NOT NULL,
    movimentacao_leito VARCHAR(500) NOT NULL,
    ultima_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_pacientes_enfermarias_enfermaria
        FOREIGN KEY (id_enfermaria)
        REFERENCES enfermarias(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_enfermarias_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_enfermarias_evento
        FOREIGN KEY (id_evento)
        REFERENCES eventos_adversos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_pacientes_enfermarias_leito
        FOREIGN KEY (id_leito)
        REFERENCES leitos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 15. LOG DE AUDITORIA
-- Registra ações realizadas no sistema.
CREATE TABLE logs_auditoria (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NULL,
    acao_realizada VARCHAR(100) NOT NULL,
    tabela_afetada VARCHAR(100) NOT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    descricao VARCHAR(1000) NULL,
    ip_address VARCHAR(45) NULL,
    severidade ENUM(
        'INFO',
        'WARNING',
        'ERROR',
        'CRITICAL',
        'SECURITY'
    ) NOT NULL DEFAULT 'INFO',

    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- 16. LOG DE ALERTAS
-- Registra alertas relacionados a ações e eventos de segurança.
CREATE TABLE logs_alertas (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NULL,
    acao_realizada VARCHAR(100) NOT NULL,
    severidade ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    mensagem VARCHAR(1000) NOT NULL,
    ip_address VARCHAR(45) NULL,

    -- O MER apresenta criado_em como STRING. Como a solicitação é seguir
    -- estritamente o MER, o campo permanece VARCHAR nesta versão.
    criado_em VARCHAR(30) NOT NULL,

    status ENUM('open', 'acknowledged', 'resolved') NOT NULL DEFAULT 'open',

    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- FIM DO SCRIPT DE CRIAÇÔES

-- INCIO DA DEFINIÇÃO DAS REGRAS DE NEGOCIO
CREATE UNIQUE INDEX uq_medicos_email
    ON medicos (email);

-- CRM é único por estado (UF).
CREATE UNIQUE INDEX uq_medicos_crm_uf
    ON medicos (crm_numero, crm_uf);

-- CNPJ identifica uma instituição.
CREATE UNIQUE INDEX uq_instituicoes_cnpj
    ON instituicoes (cnpj);

-- CPF identifica um paciente. Como o campo é NULL, registros sem CPF podem
-- coexistir no MySQL.
CREATE UNIQUE INDEX uq_pacientes_cpf
    ON pacientes (cpf);

-- Evita dois registros para o mesmo leito dentro da mesma enfermaria.
CREATE UNIQUE INDEX uq_leitos_enfermaria_numero
    ON leitos (id_enfermaria, numero);

-- Evita o mesmo vínculo paciente/alergia ser cadastrado duas vezes.
CREATE UNIQUE INDEX uq_alergias_pacientes
    ON alergias_pacientes (id_paciente, id_alergias);

-- Pelo MER, um evento possui uma assinatura correspondente.
-- NULL continua permitido para assinatura ainda não vinculada.
CREATE UNIQUE INDEX uq_eventos_assinaturas_evento
    ON eventos_assinaturas (id_evento);

-- Índices para consultas pelos relacionamentos mais utilizados.
CREATE INDEX idx_setores_responsavel_plantao
    ON setores (responsavel_plantao);

CREATE INDEX idx_enfermarias_setor
    ON enfermarias (id_setor);

CREATE INDEX idx_enfermarias_medico
    ON enfermarias (medico_responsavel);

CREATE INDEX idx_leitos_enfermaria
    ON leitos (id_enfermaria);

CREATE INDEX idx_enderecos_paciente
    ON enderecos (id_paciente);

CREATE INDEX idx_eventos_adversos_paciente
    ON eventos_adversos (id_paciente);

CREATE INDEX idx_eventos_adversos_medico
    ON eventos_adversos (id_medico);

CREATE INDEX idx_medicacoes_eventos_evento
    ON medicacoes_eventos (id_evento);

CREATE INDEX idx_medicacoes_eventos_medicamento
    ON medicacoes_eventos (id_medicamento);

CREATE INDEX idx_pacientes_enfermarias_paciente
    ON pacientes_enfermarias (id_paciente);

CREATE INDEX idx_pacientes_enfermarias_enfermaria
    ON pacientes_enfermarias (id_enfermaria);

CREATE INDEX idx_pacientes_enfermarias_leito
    ON pacientes_enfermarias (id_leito);

CREATE INDEX idx_logs_auditoria_usuario_data
    ON logs_auditoria (id_usuario, data_hora);

CREATE INDEX idx_logs_auditoria_tabela_registro
    ON logs_auditoria (tabela_afetada, id, data_hora);

CREATE INDEX idx_logs_alertas_usuario_status
    ON logs_alertas (id_usuario, status);

CREATE INDEX idx_logs_alertas_severidade
    ON logs_alertas (severidade);

-- Regras de consistência para capacidade da enfermaria.
ALTER TABLE enfermarias
    ADD CONSTRAINT ck_enfermarias_quantidade
        CHECK (quantidade_leitos >= 0),
    ADD CONSTRAINT ck_enfermarias_disponiveis
        CHECK (leitos_disponiveis >= 0 AND leitos_disponiveis <= quantidade_leitos);

-- O número do CRM não pode ficar vazio.
ALTER TABLE medicos
    ADD CONSTRAINT ck_medicos_crm_nao_vazio
        CHECK (CHAR_LENGTH(TRIM(crm_numero)) >= 4);

-- Campos textuais de identificação não podem ficar apenas com espaços.
ALTER TABLE medicos
    ADD CONSTRAINT ck_medicos_nome_nao_vazio
        CHECK (CHAR_LENGTH(TRIM(nome_completo)) > 0);

ALTER TABLE pacientes
    ADD CONSTRAINT ck_pacientes_nome_nao_vazio
        CHECK (CHAR_LENGTH(TRIM(nome_completo)) > 0);

-- FIM DAS REGRAS DE NEGÓCIO

-- TIGGERS DE VALIDAÇÃO E REGRAS DE NEGÓCIO

DELIMITER $$

-- ----------------------------------------------------------------------------
-- 18.1. Médico: normaliza a UF do CRM e valida o formato básico.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_medicos_bi_validacao$$
CREATE TRIGGER trg_medicos_bi_validacao
BEFORE INSERT ON medicos
FOR EACH ROW
BEGIN
    SET NEW.crm_uf = UPPER(TRIM(NEW.crm_uf));
    SET NEW.email = LOWER(TRIM(NEW.email));
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.crm_numero = TRIM(NEW.crm_numero);

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome do medico nao pode ficar vazio.';
    END IF;

    IF NEW.email NOT REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'E-mail do medico invalido.';
    END IF;

    IF NEW.crm_numero NOT REGEXP '^[0-9]{4,10}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CRM deve conter de 4 a 10 digitos.';
    END IF;

    IF NEW.crm_uf NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'UF do CRM deve conter exatamente 2 letras.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_medicos_bu_validacao$$
CREATE TRIGGER trg_medicos_bu_validacao
BEFORE UPDATE ON medicos
FOR EACH ROW
BEGIN
    SET NEW.crm_uf = UPPER(TRIM(NEW.crm_uf));
    SET NEW.email = LOWER(TRIM(NEW.email));
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.crm_numero = TRIM(NEW.crm_numero);

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome do medico nao pode ficar vazio.';
    END IF;

    IF NEW.email NOT REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'E-mail do medico invalido.';
    END IF;

    IF NEW.crm_numero NOT REGEXP '^[0-9]{4,10}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CRM deve conter de 4 a 10 digitos.';
    END IF;

    IF NEW.crm_uf NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'UF do CRM deve conter exatamente 2 letras.';
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.2. Paciente: normaliza e valida CPF e nome.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_pacientes_bi_validacao$$
CREATE TRIGGER trg_pacientes_bi_validacao
BEFORE INSERT ON pacientes
FOR EACH ROW
BEGIN
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.cpf = NULLIF(TRIM(NEW.cpf), '');
    SET NEW.telefone = NULLIF(TRIM(NEW.telefone), '');

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome do paciente nao pode ficar vazio.';
    END IF;


    IF NEW.cpf IS NOT NULL AND NEW.cpf NOT REGEXP '^[0-9]{11}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CPF deve conter exatamente 11 digitos, sem mascara.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_pacientes_bu_validacao$$
CREATE TRIGGER trg_pacientes_bu_validacao
BEFORE UPDATE ON pacientes
FOR EACH ROW
BEGIN
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.cpf = NULLIF(TRIM(NEW.cpf), '');
    SET NEW.telefone = NULLIF(TRIM(NEW.telefone), '');

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome do paciente nao pode ficar vazio.';
    END IF;


    IF NEW.cpf IS NOT NULL AND NEW.cpf NOT REGEXP '^[0-9]{11}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CPF deve conter exatamente 11 digitos, sem mascara.';
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.3. Instituição: valida CNPJ quando informado.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_instituicoes_bi_validacao$$
CREATE TRIGGER trg_instituicoes_bi_validacao
BEFORE INSERT ON instituicoes
FOR EACH ROW
BEGIN
    SET NEW.nome = TRIM(NEW.nome);
    SET NEW.cnpj = NULLIF(TRIM(NEW.cnpj), '');

    IF NEW.nome = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome da instituicao nao pode ficar vazio.';
    END IF;

    IF NEW.cnpj IS NOT NULL AND NEW.cnpj NOT REGEXP '^[0-9]{14}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CNPJ deve conter exatamente 14 digitos, sem mascara.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_instituicoes_bu_validacao$$
CREATE TRIGGER trg_instituicoes_bu_validacao
BEFORE UPDATE ON instituicoes
FOR EACH ROW
BEGIN
    SET NEW.nome = TRIM(NEW.nome);
    SET NEW.cnpj = NULLIF(TRIM(NEW.cnpj), '');

    IF NEW.nome = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nome da instituicao nao pode ficar vazio.';
    END IF;

    IF NEW.cnpj IS NOT NULL AND NEW.cnpj NOT REGEXP '^[0-9]{14}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CNPJ deve conter exatamente 14 digitos, sem mascara.';
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.4. Endereço: valida CEP e UF/estado.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_enderecos_bi_validacao$$
CREATE TRIGGER trg_enderecos_bi_validacao
BEFORE INSERT ON enderecos
FOR EACH ROW
BEGIN
    SET NEW.cep = TRIM(NEW.cep);
    SET NEW.cidade = TRIM(NEW.cidade);
    SET NEW.estado = UPPER(TRIM(NEW.estado));
    SET NEW.pais = TRIM(NEW.pais);

    IF NEW.cep NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CEP deve conter 8 digitos, sem mascara.';
    END IF;

    IF NEW.cidade = '' OR NEW.pais = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cidade e pais sao obrigatorios.';
    END IF;

    IF NEW.estado NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Estado deve ser informado com 2 letras.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_enderecos_bu_validacao$$
CREATE TRIGGER trg_enderecos_bu_validacao
BEFORE UPDATE ON enderecos
FOR EACH ROW
BEGIN
    SET NEW.cep = TRIM(NEW.cep);
    SET NEW.cidade = TRIM(NEW.cidade);
    SET NEW.estado = UPPER(TRIM(NEW.estado));
    SET NEW.pais = TRIM(NEW.pais);

    IF NEW.cep NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'CEP deve conter 8 digitos, sem mascara.';
    END IF;

    IF NEW.cidade = '' OR NEW.pais = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cidade e pais sao obrigatorios.';
    END IF;

    IF NEW.estado NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Estado deve ser informado com 2 letras.';
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.5. Enfermaria: valida capacidade antes de gravar.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_enfermarias_bi_validacao$$
CREATE TRIGGER trg_enfermarias_bi_validacao
BEFORE INSERT ON enfermarias
FOR EACH ROW
BEGIN
    IF NEW.quantidade_leitos < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Quantidade de leitos nao pode ser negativa.';
    END IF;

    IF NEW.leitos_disponiveis < 0 OR NEW.leitos_disponiveis > NEW.quantidade_leitos THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Leitos disponiveis deve estar entre 0 e a capacidade total.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_enfermarias_bu_validacao$$
CREATE TRIGGER trg_enfermarias_bu_validacao
BEFORE UPDATE ON enfermarias
FOR EACH ROW
BEGIN
    IF NEW.quantidade_leitos < 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Quantidade de leitos nao pode ser negativa.';
    END IF;

    IF NEW.leitos_disponiveis < 0 OR NEW.leitos_disponiveis > NEW.quantidade_leitos THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Leitos disponiveis deve estar entre 0 e a capacidade total.';
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.6. Evento-Assinatura: mantém coerência entre status e datas.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_eventos_assinaturas_bi_validacao$$
CREATE TRIGGER trg_eventos_assinaturas_bi_validacao
BEFORE INSERT ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF NEW.status = 'ativo' AND NEW.data_aceite IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Assinatura ativa precisa possuir data de aceite.';
    END IF;

    IF NEW.status = 'inativo' AND NEW.data_revogacao IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Assinatura inativa precisa possuir data de revogacao.';
    END IF;

    IF NEW.data_aceite IS NULL AND NEW.data_revogacao IS NOT NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nao e permitido revogar uma assinatura sem aceite.';
    END IF;

    IF NEW.data_aceite IS NOT NULL
       AND NEW.data_revogacao IS NOT NULL
       AND NEW.data_revogacao < NEW.data_aceite THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Data de revogacao nao pode ser anterior ao aceite.';
    END IF;

    IF NEW.id_evento IS NOT NULL AND NEW.id_paciente IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1
              FROM eventos_adversos ea
             WHERE ea.id = NEW.id_evento
               AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Evento e paciente da assinatura nao correspondem.';
        END IF;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_eventos_assinaturas_bu_validacao$$
CREATE TRIGGER trg_eventos_assinaturas_bu_validacao
BEFORE UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF NEW.status = 'ativo' AND NEW.data_aceite IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Assinatura ativa precisa possuir data de aceite.';
    END IF;

    IF NEW.status = 'inativo' AND NEW.data_revogacao IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Assinatura inativa precisa possuir data de revogacao.';
    END IF;

    IF NEW.data_aceite IS NULL AND NEW.data_revogacao IS NOT NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nao e permitido revogar uma assinatura sem aceite.';
    END IF;

    IF NEW.data_aceite IS NOT NULL
       AND NEW.data_revogacao IS NOT NULL
       AND NEW.data_revogacao < NEW.data_aceite THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Data de revogacao nao pode ser anterior ao aceite.';
    END IF;

    IF NEW.id_evento IS NOT NULL AND NEW.id_paciente IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1
              FROM eventos_adversos ea
             WHERE ea.id = NEW.id_evento
               AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Evento e paciente da assinatura nao correspondem.';
        END IF;
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.7. Evento Adverso: impede registro por médico inativo e mantém a
--      associação entre evento, paciente e assinatura.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_eventos_adversos_bi_validacao$$
CREATE TRIGGER trg_eventos_adversos_bi_validacao
BEFORE INSERT ON eventos_adversos
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM medicos m
         WHERE m.id = NEW.id_medico
           AND m.status = 'ativo'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Somente medico ativo pode registrar evento adverso.';
    END IF;

    IF NEW.descricao_fato IS NULL OR TRIM(NEW.descricao_fato) = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Descricao do fato e obrigatoria.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_eventos_adversos_bu_validacao$$
CREATE TRIGGER trg_eventos_adversos_bu_validacao
BEFORE UPDATE ON eventos_adversos
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM medicos m
         WHERE m.id = NEW.id_medico
           AND m.status = 'ativo'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Somente medico ativo pode manter o evento responsavel.';
    END IF;

    IF NEW.descricao_fato IS NULL OR TRIM(NEW.descricao_fato) = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Descricao do fato e obrigatoria.';
    END IF;

    IF NEW.id_assinatura IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1
              FROM eventos_assinaturas es
             WHERE es.id = NEW.id_assinatura
               AND es.id_evento = NEW.id
               AND es.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'A assinatura informada nao pertence ao evento e paciente.';
        END IF;
    END IF;
END$$

-- ----------------------------------------------------------------------------
-- 18.8. Paciente-Enfermaria: garante que o leito realmente pertença à
--      enfermaria e que o evento pertença ao paciente informado.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_pacientes_enfermarias_bi_validacao$$
CREATE TRIGGER trg_pacientes_enfermarias_bi_validacao
BEFORE INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM leitos l
         WHERE l.id = NEW.id_leito
           AND l.id_enfermaria = NEW.id_enfermaria
           AND l.status = 'ativo'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou esta inativo.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
          FROM eventos_adversos ea
         WHERE ea.id = NEW.id_evento
           AND ea.id_paciente = NEW.id_paciente
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
    END IF;

    IF NEW.movimentacao_leito IS NULL OR TRIM(NEW.movimentacao_leito) = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Movimentacao do leito deve ser informada.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_pacientes_enfermarias_bu_validacao$$
CREATE TRIGGER trg_pacientes_enfermarias_bu_validacao
BEFORE UPDATE ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM leitos l
         WHERE l.id = NEW.id_leito
           AND l.id_enfermaria = NEW.id_enfermaria
           AND l.status = 'ativo'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou esta inativo.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
          FROM eventos_adversos ea
         WHERE ea.id = NEW.id_evento
           AND ea.id_paciente = NEW.id_paciente
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
    END IF;

    IF NEW.movimentacao_leito IS NULL OR TRIM(NEW.movimentacao_leito) = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Movimentacao do leito deve ser informada.';
    END IF;
END$$

-- ============================================================================
-- 19. TRIGGERS DE AUDITORIA
-- ============================================================================
-- O backend pode definir, na MESMA conexão MySQL usada pela operação:
--
--   SET @continuum_usuario_id = 1;
--   SET @continuum_ip_address = '192.168.0.10';
--
-- O trigger utilizará essas variáveis para registrar o autor e o IP.
-- Caso não sejam definidas, id_usuario e ip_address permanecerão NULL.
--
-- Nunca são gravadas senhas em texto ou conteúdo completo de dados clínicos
-- dentro do log automático.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 19.1. Pacientes
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_pacientes_ai_auditoria$$
CREATE TRIGGER trg_pacientes_ai_auditoria
AFTER INSERT ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'pacientes', CURRENT_TIMESTAMP,
         CONCAT('Cadastro de paciente ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_pacientes_au_auditoria$$
CREATE TRIGGER trg_pacientes_au_auditoria
AFTER UPDATE ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'pacientes', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de paciente ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_pacientes_ad_auditoria$$
CREATE TRIGGER trg_pacientes_ad_auditoria
AFTER DELETE ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'DELETE', 'pacientes', CURRENT_TIMESTAMP,
         CONCAT('Exclusao de paciente ID ', OLD.id), @continuum_ip_address, 'WARNING');
END$$

-- ----------------------------------------------------------------------------
-- 19.2. Médico: alterações de cadastro são auditadas; alterações de senha e
--      autenticacao recebem severidade SECURITY.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_medicos_ai_auditoria$$
CREATE TRIGGER trg_medicos_ai_auditoria
AFTER INSERT ON medicos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'medicos', CURRENT_TIMESTAMP,
         CONCAT('Cadastro de medico ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_medicos_au_auditoria$$
CREATE TRIGGER trg_medicos_au_auditoria
AFTER UPDATE ON medicos
FOR EACH ROW
BEGIN
    IF NOT (OLD.senha_hash <=> NEW.senha_hash) THEN
        INSERT INTO logs_auditoria
            (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES
            (@continuum_usuario_id, 'ALTERACAO_SENHA', 'medicos', CURRENT_TIMESTAMP,
             CONCAT('Alteracao do hash de senha do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;

    IF NOT (OLD.autenticacao <=> NEW.autenticacao) THEN
        INSERT INTO logs_auditoria
            (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES
            (@continuum_usuario_id, 'ALTERACAO_AUTENTICACAO', 'medicos', CURRENT_TIMESTAMP,
             CONCAT('Alteracao de autenticacao do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;

    IF NOT (OLD.status <=> NEW.status) THEN
        INSERT INTO logs_auditoria
            (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES
            (@continuum_usuario_id, 'ALTERACAO_STATUS', 'medicos', CURRENT_TIMESTAMP,
             CONCAT('Alteracao de status do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;

    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'medicos', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de medico ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

-- ----------------------------------------------------------------------------
-- 19.3. Evento adverso
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_eventos_adversos_ai_auditoria$$
CREATE TRIGGER trg_eventos_adversos_ai_auditoria
AFTER INSERT ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'eventos_adversos', CURRENT_TIMESTAMP,
         CONCAT('Criacao de evento adverso ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_eventos_adversos_au_auditoria$$
CREATE TRIGGER trg_eventos_adversos_au_auditoria
AFTER UPDATE ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'eventos_adversos', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de evento adverso ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_eventos_adversos_ad_auditoria$$
CREATE TRIGGER trg_eventos_adversos_ad_auditoria
AFTER DELETE ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'DELETE', 'eventos_adversos', CURRENT_TIMESTAMP,
         CONCAT('Exclusao de evento adverso ID ', OLD.id), @continuum_ip_address, 'WARNING');
END$$

-- ----------------------------------------------------------------------------
-- 19.4. Assinatura/aceite
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_eventos_assinaturas_ai_auditoria$$
CREATE TRIGGER trg_eventos_assinaturas_ai_auditoria
AFTER INSERT ON eventos_assinaturas
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'eventos_assinaturas', CURRENT_TIMESTAMP,
         CONCAT('Registro de assinatura ID ', NEW.id), @continuum_ip_address, 'SECURITY');
END$$

DROP TRIGGER IF EXISTS trg_eventos_assinaturas_au_auditoria$$
CREATE TRIGGER trg_eventos_assinaturas_au_auditoria
AFTER UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'eventos_assinaturas', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de assinatura ID ', NEW.id), @continuum_ip_address, 'SECURITY');
END$$

-- ----------------------------------------------------------------------------
-- 19.5. Administração de medicamentos
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_medicacoes_eventos_ai_auditoria$$
CREATE TRIGGER trg_medicacoes_eventos_ai_auditoria
AFTER INSERT ON medicacoes_eventos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'medicacoes_eventos', CURRENT_TIMESTAMP,
         CONCAT('Registro de medicacao vinculada ao evento ID ', NEW.id_evento), @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_medicacoes_eventos_au_auditoria$$
CREATE TRIGGER trg_medicacoes_eventos_au_auditoria
AFTER UPDATE ON medicacoes_eventos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'medicacoes_eventos', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de medicacao vinculada ao evento ID ', NEW.id_evento), @continuum_ip_address, 'INFO');
END$$

-- ----------------------------------------------------------------------------
-- 19.6. Movimentação/alocação de paciente
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_pacientes_enfermarias_ai_auditoria$$
CREATE TRIGGER trg_pacientes_enfermarias_ai_auditoria
AFTER INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'INSERT', 'pacientes_enfermarias', CURRENT_TIMESTAMP,
         CONCAT('Movimentacao do paciente ID ', NEW.id_paciente, ' para leito ID ', NEW.id_leito),
         @continuum_ip_address, 'INFO');
END$$

DROP TRIGGER IF EXISTS trg_pacientes_enfermarias_au_auditoria$$
CREATE TRIGGER trg_pacientes_enfermarias_au_auditoria
AFTER UPDATE ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria
        (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES
        (@continuum_usuario_id, 'UPDATE', 'pacientes_enfermarias', CURRENT_TIMESTAMP,
         CONCAT('Atualizacao de movimentacao do paciente ID ', NEW.id_paciente),
         @continuum_ip_address, 'INFO');
END$$

-- ----------------------------------------------------------------------------
-- 19.7. Revogação de assinatura gera alerta de segurança.
-- ----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_eventos_assinaturas_au_alerta$$
CREATE TRIGGER trg_eventos_assinaturas_au_alerta
AFTER UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF OLD.status <> 'inativo' AND NEW.status = 'inativo' THEN
        INSERT INTO logs_alertas
            (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em, status)
        VALUES
            (@continuum_usuario_id,
             'REVOGACAO_ASSINATURA',
             'HIGH',
             CONCAT('Assinatura/aceite ID ', NEW.id, ' foi revogado.'),
             @continuum_ip_address,
             DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'),
             'open');
    END IF;
END$$

DELIMITER ;


-- INICIO DO SCRIPT DE ABASTECIMENTO

-- Define o contexto de auditoria da conexão que está executando o seed.
SET @continuum_usuario_id = 1;
SET @continuum_ip_address = '127.0.0.1';

-- ----------------------------------------------------------------------------
-- 20.1. Médico
-- ----------------------------------------------------------------------------
INSERT INTO medicos
    (id, nome_completo, email, senha_hash, crm_numero, crm_uf, cargo,
     status, autenticacao, ultimo_acesso)
VALUES
    (1,
     'Medico Teste Continuum',
     'medico.teste@continuum.local',
     '$argon2id$v=19$m=65536,t=3,p=2$TzQ4SExCbVhvRXROaXNjaw$+0J4imDcsmNf0F/U8ENzL4zjwtkB2wlgY/maecb39kg',
     '123456',
     'SP',
     'medico',
     'ativo',
     TRUE,
     NULL);

-- ----------------------------------------------------------------------------
-- 20.2. Instituição
-- ----------------------------------------------------------------------------
INSERT INTO instituicoes
    (id, nome, cnpj, status)
VALUES
    (1,
     'Instituicao de Teste Continuum',
     '12345678000100',
     'ativo');

-- ----------------------------------------------------------------------------
-- 20.3. Setor
-- ----------------------------------------------------------------------------
INSERT INTO setores
    (id, tipo, responsavel_plantao, class_risco)
VALUES
    (1,
     'Cardiologia',
     1,
     'alto');

-- ----------------------------------------------------------------------------
-- 20.4. Enfermaria
-- ----------------------------------------------------------------------------
INSERT INTO enfermarias
    (id, id_setor, medico_responsavel, quantidade_leitos, leitos_disponiveis, status)
VALUES
    (1,
     1,
     1,
     10,
     9,
     'ativo');

-- ----------------------------------------------------------------------------
-- 20.5. Leitos
-- ----------------------------------------------------------------------------
INSERT INTO leitos
    (id, id_enfermaria, numero, status)
VALUES
    (1, 1, '101', 'ativo'),
    (2, 1, '102', 'ativo');

-- ----------------------------------------------------------------------------
-- 20.6. Paciente
-- ----------------------------------------------------------------------------
INSERT INTO pacientes
    (id, nome_completo, cpf, data_nascimento, sexo, etnia,
     senha_hash, telefone, aceite_termo_uso, endereco)
VALUES
    (1,
     'Paciente Teste Continuum',
     '52998224725',
     '2000-05-15',
     'nao_informado',
     'nao_informada',
     '$argon2id$v=19$m=65536,t=3,p=2$a20zT0p5SEVrZlBQT2lNSQ$rbzZ6BVqibMsONI9YGfLOP3YNylOhp77pRj8xCck3Gk',
     '(11) 99999-0000',
     'sim',
     NULL);

-- ----------------------------------------------------------------------------
-- 20.7. Endereço
-- ----------------------------------------------------------------------------
INSERT INTO enderecos
    (id, id_paciente, cep, cidade, estado, pais, status)
VALUES
    (1,
     1,
     '08900000',
     'Guararema',
     'SP',
     'Brasil',
     'ativo');

-- Atualiza o vínculo FK do endereço do paciente.
UPDATE pacientes
   SET endereco = 1
 WHERE id = 1;

-- ----------------------------------------------------------------------------
-- 20.8. Alergia
-- ----------------------------------------------------------------------------
INSERT INTO alergias
    (id, tipo, gravidade, contra_medida)
VALUES
    (1,
     'choque anafilatico',
     'alta',
     'Evitar administracao de medicamentos da classe das penicilinas.');

-- ----------------------------------------------------------------------------
-- 20.9. Relação Alergia-Paciente
-- ----------------------------------------------------------------------------
INSERT INTO alergias_pacientes
    (id, id_paciente, id_alergias)
VALUES
    (1, 1, 1);

-- ----------------------------------------------------------------------------
-- 20.10. Evento adverso
-- ----------------------------------------------------------------------------
-- id_assinatura permanece NULL neste primeiro momento porque a tabela de
-- assinatura possui FK para o evento. Depois da criação da assinatura,
-- fazemos o vínculo circular por UPDATE.
INSERT INTO eventos_adversos
    (id, id_paciente, id_medico, tipo_evento, grau_dano, sinais_vitais,
     descricao_fato, data_evento, status_investigacao, id_assinatura)
VALUES
    (1,
     1,
     1,
     'medicamento',
     'baixo',
     'PA 120x80 mmHg; FC 82 bpm; FR 18 irpm; SpO2 98%',
     'Registro ficticio para teste de fluxo de evento adverso.',
     CURRENT_TIMESTAMP,
     'pendente',
     NULL);

-- ----------------------------------------------------------------------------
-- 20.11. Evento-Assinatura
-- ----------------------------------------------------------------------------
INSERT INTO eventos_assinaturas
    (id, id_evento, id_paciente, nome_evento, tipo, finalidade,
     data_aceite, data_revogacao, status)
VALUES
    (1,
     1,
     1,
     'Termo de ciencia do evento adverso',
     'exame_diagnostico',
     'Registrar o aceite relacionado ao tratamento das informacoes do evento.',
     CURRENT_TIMESTAMP,
     NULL,
     'ativo');

-- Completa a referência circular prevista no MER.
UPDATE eventos_adversos
   SET id_assinatura = 1
 WHERE id = 1;

-- ----------------------------------------------------------------------------
-- 20.12. Medicação
-- ----------------------------------------------------------------------------
INSERT INTO medicacoes
    (id, principio_ativo, nome_comercial, tipo, dosagem)
VALUES
    (1,
     'Paracetamol',
     'Paracetamol Teste',
     'Analgesico',
     '500 mg');

-- ----------------------------------------------------------------------------
-- 20.13. Medicação-Evento
-- ----------------------------------------------------------------------------
INSERT INTO medicacoes_eventos
    (id, id_medicamento, id_evento, dose_administrada, concentracao,
     via_administrada, frequencia, diluicao, tempo_infusao,
     local_aplicacao, data_aplicacao)
VALUES
    (1,
     1,
     1,
     '500 mg',
     '500 mg/comprimido',
     'oral',
     'dose unica',
     NULL,
     NULL,
     NULL,
     CURRENT_TIMESTAMP);

-- ----------------------------------------------------------------------------
-- 20.14. Paciente-Enfermaria
-- ----------------------------------------------------------------------------
INSERT INTO pacientes_enfermarias
    (id, id_enfermaria, id_paciente, id_evento, id_leito,
     movimentacao_leito, ultima_atualizacao)
VALUES
    (1,
     1,
     1,
     1,
     1,
     'Admissao ficticia do paciente no leito 101.',
     CURRENT_TIMESTAMP);

-- ----------------------------------------------------------------------------
-- 20.15. Log de alerta manual de exemplo
-- ----------------------------------------------------------------------------
INSERT INTO logs_alertas
    (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em, status)
VALUES
    (1,
     'TESTE_SEGURANCA',
     'MEDIUM',
     'Alerta ficticio utilizado para validar o fluxo de monitoramento.',
     '127.0.0.1',
     DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'),
     'open');