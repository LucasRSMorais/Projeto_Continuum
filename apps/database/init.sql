/* Cria o banco de dados principal do projeto, caso ele ainda não exista. */
CREATE DATABASE IF NOT EXISTS railway
	CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

/* Seleciona o banco que receberá as tabelas e os dados abaixo. */
USE railway;

-- ============================================================================
-- 1. CRIAÇÃO DAS TABELAS
-- ============================================================================

-- INSTITUIÇÃO
CREATE TABLE instituicoes (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    cnpj VARCHAR(14) NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- MÉDICO
CREATE TABLE medicos (
    id INT NOT NULL AUTO_INCREMENT,
    id_instituicao INT NULL,
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    crm_numero VARCHAR(10) NOT NULL,
    crm_uf VARCHAR(2) NOT NULL,
    especialidade VARCHAR(100) NULL,
    cargo ENUM('medico', 'Interno', 'Residente R1', 'Residente R2','Residente R3','Residente R4','Residente R5','Residente R+','Preceptor', 'Staff', 'outro') NOT NULL DEFAULT 'medico',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    autenticacao BOOLEAN NOT NULL DEFAULT FALSE,
    ultimo_acesso DATETIME NULL,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
	UNIQUE KEY uq_medicos_email (email),
    UNIQUE KEY uq_medicos_crm_uf (crm_numero, crm_uf),
    CONSTRAINT fk_medicos_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SETOR
CREATE TABLE setores (
    id INT NOT NULL AUTO_INCREMENT,
    id_instituicao INT NULL,
    tipo ENUM(
        'Clínica Geral',
        'Cirúrgica',
        'Obstétrica e Perinatal',
        'Ortopédica',
        'Oncológica',
        'Cardiologia',
        'Neurologia',
        'Infectologia',
        'Isolamento',
        'Enfermaria'
    ) NOT NULL,
    nome VARCHAR(100) NULL, -- Inserido com base nos dados do seed
    responsavel_plantao INT NULL,
    class_risco ENUM('baixo', 'medio', 'alto') NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_setores_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_setores_responsavel_plantao
        FOREIGN KEY (responsavel_plantao)
        REFERENCES medicos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- ENFERMARIA
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

-- LEITO
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

-- PACIENTE
CREATE TABLE pacientes (
    id INT NOT NULL AUTO_INCREMENT,
    nome_completo VARCHAR(150) NOT NULL,
    email VARCHAR(254) NULL, -- Inserido com base nos dados do seed
    cpf VARCHAR(11) NULL,
    data_nascimento DATE NULL,
    sexo VARCHAR(80) NOT NULL,
    etnia VARCHAR(80) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
	alergias TEXT NULL,
    telefone VARCHAR(20) NULL,
    aceite_termo_uso ENUM('sim', 'nao') NOT NULL DEFAULT 'nao',
    endereco INT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pacientes_email (email)
) ENGINE=InnoDB;

-- ENDEREÇO
CREATE TABLE enderecos (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NULL,
    cep VARCHAR(8) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    pais VARCHAR(100) NOT NULL DEFAULT 'Brasil',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id),
    CONSTRAINT fk_enderecos_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE pacientes
    ADD CONSTRAINT fk_pacientes_endereco
        FOREIGN KEY (endereco)
        REFERENCES enderecos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

-- ALERGIAS
CREATE TABLE alergias (
    id INT NOT NULL AUTO_INCREMENT,
    tipo VARCHAR(150) NOT NULL,
    gravidade ENUM('baixa', 'media', 'alta') NOT NULL,
    contra_medida VARCHAR(500) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ALERGIAS-PACIENTE
CREATE TABLE alergias_pacientes (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_alergia INT NOT NULL, -- Corrigido nome conforme seed
    PRIMARY KEY (id),
    CONSTRAINT fk_alergias_pacientes_paciente
        FOREIGN KEY (id_paciente)
        REFERENCES pacientes(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_alergias_pacientes_alergia
        FOREIGN KEY (id_alergia)
        REFERENCES alergias(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- EVENTO-ASSINATURA
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

-- EVENTO ADVERSO
CREATE TABLE eventos_adversos (
    id INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NULL,
    id_medico INT NOT NULL,
    id_setor INT NULL,
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
		'outro',
        'passagem_plantao'
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
        ON DELETE RESTRICT,
    CONSTRAINT fk_eventos_adversos_setor
        FOREIGN KEY (id_setor)
        REFERENCES setores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

ALTER TABLE eventos_adversos
    ADD CONSTRAINT fk_eventos_adversos_assinatura
        FOREIGN KEY (id_assinatura)
        REFERENCES eventos_assinaturas(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

ALTER TABLE eventos_assinaturas
    ADD CONSTRAINT fk_eventos_assinaturas_evento
        FOREIGN KEY (id_evento)
        REFERENCES eventos_adversos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;

-- PLANTÕES
CREATE TABLE plantoes (
    id INT NOT NULL AUTO_INCREMENT,
    id_medico INT NULL,
    id_setor INT NOT NULL,
    id_evento_adverso INT NULL,
    descricao_fato TEXT NULL,
    data_ocorrido DATETIME NULL,
    plantao ENUM('diurno', 'noturno') NOT NULL,
    inicio_em DATETIME NOT NULL,
    fim_em DATETIME NOT NULL,
    status ENUM('aberto', 'encerrado') NOT NULL DEFAULT 'aberto',
    assumido_em DATETIME NULL,
    encerrado_em DATETIME NULL,
    ip VARCHAR(45) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_plantoes_setor_inicio (id_setor, inicio_em),
    KEY idx_plantoes_medico_status (id_medico, status),
    KEY idx_plantoes_setor_status_inicio (id_setor, status, inicio_em),
    CONSTRAINT fk_plantoes_medico
        FOREIGN KEY (id_medico)
        REFERENCES medicos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plantoes_setor
        FOREIGN KEY (id_setor)
        REFERENCES setores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_plantoes_evento_adverso
        FOREIGN KEY (id_evento_adverso)
        REFERENCES eventos_adversos(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT ck_plantoes_periodo CHECK (fim_em > inicio_em)
) ENGINE=InnoDB;

-- MEDICAÇÃO
CREATE TABLE medicacoes (
    id INT NOT NULL AUTO_INCREMENT,
    principio_ativo VARCHAR(255) NOT NULL,
    nome_comercial VARCHAR(255) NULL,
    tipo VARCHAR(100) NULL,
    dosagem VARCHAR(100) NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- MEDICAÇÃO-EVENTO
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

-- PACIENTE-ENFERMARIA
CREATE TABLE pacientes_enfermarias (
    id INT NOT NULL AUTO_INCREMENT,
    id_enfermaria INT NOT NULL,
    id_paciente INT NOT NULL,
    id_evento INT NULL,
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

-- FILA ADMISSÕES
CREATE TABLE fila_admissoes (
    id_fila INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_enfermaria INT NOT NULL,
    origem ENUM(
        'pronto_socorro',
        'centro_cirurgico',
        'rpa',
        'uti',
        'transferencia_interna',
        'transferencia_externa',
        'ambulatorio',
        'admissao_direta'
    ) NOT NULL,
    gravidade ENUM('baixa', 'media', 'alta') NOT NULL DEFAULT 'media',
    status ENUM('aguardando', 'admitido', 'cancelado') NOT NULL DEFAULT 'aguardando',
    data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_fila),
    CONSTRAINT fk_fila_admissoes_paciente
        FOREIGN KEY (id_paciente) REFERENCES pacientes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_fila_admissoes_enfermaria
        FOREIGN KEY (id_enfermaria) REFERENCES enfermarias(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- LOG DE AUDITORIA
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

-- LOG DE ALERTAS
CREATE TABLE logs_alertas (
    id INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NULL,
    acao_realizada VARCHAR(100) NOT NULL,
    severidade ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    mensagem VARCHAR(1000) NOT NULL,
    ip_address VARCHAR(45) NULL,
    criado_em VARCHAR(30) NOT NULL,
    status ENUM('open', 'acknowledged', 'resolved') NOT NULL DEFAULT 'open',
    PRIMARY KEY (id)
) ENGINE=InnoDB;


-- ============================================================================
-- 2. REGRAS DE NEGOCIO, ÍNDICES E CONSTRAINTS
-- ============================================================================
CREATE UNIQUE INDEX uq_instituicoes_cnpj ON instituicoes (cnpj);
CREATE UNIQUE INDEX uq_pacientes_cpf ON pacientes (cpf);
CREATE UNIQUE INDEX uq_leitos_enfermaria_numero ON leitos (id_enfermaria, numero);
CREATE UNIQUE INDEX uq_alergias_pacientes ON alergias_pacientes (id_paciente, id_alergia);
CREATE UNIQUE INDEX uq_eventos_assinaturas_evento ON eventos_assinaturas (id_evento);

CREATE INDEX idx_setores_responsavel_plantao ON setores (responsavel_plantao);
CREATE INDEX idx_enfermarias_setor ON enfermarias (id_setor);
CREATE INDEX idx_enfermarias_medico ON enfermarias (medico_responsavel);
CREATE INDEX idx_leitos_enfermaria ON leitos (id_enfermaria);
CREATE INDEX idx_enderecos_paciente ON enderecos (id_paciente);
CREATE INDEX idx_eventos_adversos_paciente ON eventos_adversos (id_paciente);
CREATE INDEX idx_eventos_adversos_medico ON eventos_adversos (id_medico);
CREATE INDEX idx_eventos_adversos_setor ON eventos_adversos (id_setor);
CREATE INDEX idx_medicacoes_eventos_evento ON medicacoes_eventos (id_evento);
CREATE INDEX idx_medicacoes_eventos_medicamento ON medicacoes_eventos (id_medicamento);
CREATE INDEX idx_pacientes_enfermarias_paciente ON pacientes_enfermarias (id_paciente);
CREATE INDEX idx_pacientes_enfermarias_enfermaria ON pacientes_enfermarias (id_enfermaria);
CREATE INDEX idx_pacientes_enfermarias_leito ON pacientes_enfermarias (id_leito);
CREATE INDEX idx_logs_auditoria_usuario_data ON logs_auditoria (id_usuario, data_hora);
CREATE INDEX idx_logs_auditoria_tabela_registro ON logs_auditoria (tabela_afetada, id, data_hora);
CREATE INDEX idx_logs_alertas_usuario_status ON logs_alertas (id_usuario, status);
CREATE INDEX idx_logs_alertas_severidade ON logs_alertas (severidade);

ALTER TABLE enfermarias
    ADD CONSTRAINT ck_enfermarias_quantidade CHECK (quantidade_leitos >= 0),
    ADD CONSTRAINT ck_enfermarias_disponiveis CHECK (leitos_disponiveis >= 0 AND leitos_disponiveis <= quantidade_leitos);

ALTER TABLE medicos
    ADD CONSTRAINT ck_medicos_crm_nao_vazio CHECK (CHAR_LENGTH(TRIM(crm_numero)) >= 4),
    ADD CONSTRAINT ck_medicos_nome_nao_vazio CHECK (CHAR_LENGTH(TRIM(nome_completo)) > 0);

ALTER TABLE pacientes
    ADD CONSTRAINT ck_pacientes_nome_nao_vazio CHECK (CHAR_LENGTH(TRIM(nome_completo)) > 0);


-- ============================================================================
-- 3. VIEWS DO DASHBOARD
-- ============================================================================
CREATE OR REPLACE VIEW vw_dash_mapa_leitos AS 
SELECT      
    e.id_setor, 
    l.id_enfermaria, 
    l.numero AS leito_numero, 
    l.status AS status_leito,     
    p.nome_completo AS paciente_nome,     
    EXISTS (         
        SELECT 1 FROM alergias_pacientes ap         
        JOIN alergias a ON ap.id_alergia = a.id
        WHERE ap.id_paciente = p.id AND a.gravidade = 'alta'     
    ) AS alerta_alergia_grave 
FROM leitos l 
JOIN enfermarias e ON l.id_enfermaria = e.id 
LEFT JOIN pacientes_enfermarias pe ON l.id = pe.id_leito 
LEFT JOIN pacientes p ON pe.id_paciente = p.id;

CREATE OR REPLACE VIEW vw_dash_eventos_criticos AS
SELECT 
    ea.id AS evento_id, e.id_setor, ea.data_evento, ea.tipo_evento,
    p.nome_completo AS paciente_nome, l.numero AS leito_numero, ea.status_investigacao
FROM eventos_adversos ea
JOIN pacientes p ON ea.id_paciente = p.id
LEFT JOIN pacientes_enfermarias pe ON p.id = pe.id_paciente
LEFT JOIN leitos l ON pe.id_leito = l.id
LEFT JOIN enfermarias e ON l.id_enfermaria = e.id
WHERE ea.grau_dano = 'alto'
  AND (ea.status_investigacao IN ('pendente', 'em_investigacao')
       OR (ea.status_investigacao = 'concluido' AND ea.data_evento >= NOW() - INTERVAL 24 HOUR));

CREATE OR REPLACE VIEW vw_dash_fila_admissoes AS
SELECT 
    fa.id_fila AS fila_id, 
    e.id_setor, 
    p.nome_completo AS paciente_nome,
    fa.origem, 
    fa.gravidade, 
    TIMESTAMPDIFF(MINUTE, fa.data_solicitacao, NOW()) AS tempo_espera_minutos
FROM fila_admissoes fa
JOIN pacientes p ON fa.id_paciente = p.id
JOIN enfermarias e ON fa.id_enfermaria = e.id
WHERE fa.status = 'aguardando';

CREATE OR REPLACE VIEW vw_dash_altas_pendentes AS
SELECT 
    ea.id AS assinatura_id, e.id_setor, p.nome_completo AS paciente_nome,
    l.numero AS leito_numero, ea.nome_evento AS descricao_alta
FROM eventos_assinaturas ea
JOIN pacientes p ON ea.id_paciente = p.id
JOIN pacientes_enfermarias pe ON p.id = pe.id_paciente
JOIN leitos l ON pe.id_leito = l.id
JOIN enfermarias e ON l.id_enfermaria = e.id
WHERE ea.tipo = 'termo_alta' AND ea.status = 'ativo' AND ea.data_revogacao IS NULL
  AND pe.id = (SELECT MAX(id) FROM pacientes_enfermarias WHERE id_paciente = p.id);

CREATE OR REPLACE VIEW vw_dash_passagens_plantao AS
SELECT
    p.id AS plantao_id,
    p.id_setor,
    s.nome AS setor_nome,
    p.id_medico,
    m.nome_completo AS medico_nome,
    p.plantao,
    p.inicio_em,
    p.fim_em,
    p.assumido_em,
    p.encerrado_em,
    p.data_ocorrido,
    p.descricao_fato,
    p.ip,
    ea.id AS evento_adverso_id,
    ea.tipo_evento,
    ea.data_evento
FROM plantoes p
JOIN setores s ON s.id = p.id_setor
JOIN medicos m ON m.id = p.id_medico
JOIN eventos_adversos ea ON ea.id = p.id_evento_adverso
WHERE p.status = 'encerrado'
  AND ea.tipo_evento = 'passagem_plantao';

-- ============================================================================
-- 4. TIGGERS DE VALIDAÇÃO E AUDITORIA
-- ============================================================================
DELIMITER $$

-- Medicos
CREATE TRIGGER trg_medicos_bi_validacao
BEFORE INSERT ON medicos
FOR EACH ROW
BEGIN
    SET NEW.crm_uf = UPPER(TRIM(NEW.crm_uf));
    SET NEW.email = LOWER(TRIM(NEW.email));
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.crm_numero = TRIM(NEW.crm_numero);

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome do medico nao pode ficar vazio.';
    END IF;
    IF NEW.email NOT REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'E-mail do medico invalido.';
    END IF;
    IF NEW.crm_numero NOT REGEXP '^[0-9]{4,10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CRM deve conter de 4 a 10 digitos.';
    END IF;
    IF NEW.crm_uf NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'UF do CRM deve conter exatamente 2 letras.';
    END IF;
END$$

CREATE TRIGGER trg_medicos_bu_validacao
BEFORE UPDATE ON medicos
FOR EACH ROW
BEGIN
    SET NEW.crm_uf = UPPER(TRIM(NEW.crm_uf));
    SET NEW.email = LOWER(TRIM(NEW.email));
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.crm_numero = TRIM(NEW.crm_numero);

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome do medico nao pode ficar vazio.';
    END IF;
    IF NEW.email NOT REGEXP '^[^[:space:]@]+@[^[:space:]@]+\\.[^[:space:]@]+$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'E-mail do medico invalido.';
    END IF;
    IF NEW.crm_numero NOT REGEXP '^[0-9]{4,10}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CRM deve conter de 4 a 10 digitos.';
    END IF;
    IF NEW.crm_uf NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'UF do CRM deve conter exatamente 2 letras.';
    END IF;
END$$

-- Pacientes
CREATE TRIGGER trg_pacientes_bi_validacao
BEFORE INSERT ON pacientes
FOR EACH ROW
BEGIN
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.cpf = NULLIF(TRIM(NEW.cpf), '');
    SET NEW.telefone = NULLIF(TRIM(NEW.telefone), '');

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome do paciente nao pode ficar vazio.';
    END IF;
    IF NEW.cpf IS NOT NULL AND NEW.cpf NOT REGEXP '^[0-9]{11}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CPF deve conter exatamente 11 digitos, sem mascara.';
    END IF;
END$$

CREATE TRIGGER trg_pacientes_bu_validacao
BEFORE UPDATE ON pacientes
FOR EACH ROW
BEGIN
    SET NEW.nome_completo = TRIM(NEW.nome_completo);
    SET NEW.cpf = NULLIF(TRIM(NEW.cpf), '');
    SET NEW.telefone = NULLIF(TRIM(NEW.telefone), '');

    IF NEW.nome_completo = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome do paciente nao pode ficar vazio.';
    END IF;
    IF NEW.cpf IS NOT NULL AND NEW.cpf NOT REGEXP '^[0-9]{11}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CPF deve conter exatamente 11 digitos, sem mascara.';
    END IF;
END$$

-- Instituições
CREATE TRIGGER trg_instituicoes_bi_validacao
BEFORE INSERT ON instituicoes
FOR EACH ROW
BEGIN
    SET NEW.nome = TRIM(NEW.nome);
    SET NEW.cnpj = NULLIF(TRIM(NEW.cnpj), '');
    IF NEW.nome = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome da instituicao nao pode ficar vazio.';
    END IF;
    IF NEW.cnpj IS NOT NULL AND NEW.cnpj NOT REGEXP '^[0-9]{14}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CNPJ deve conter exatamente 14 digitos, sem mascara.';
    END IF;
END$$

CREATE TRIGGER trg_instituicoes_bu_validacao
BEFORE UPDATE ON instituicoes
FOR EACH ROW
BEGIN
    SET NEW.nome = TRIM(NEW.nome);
    SET NEW.cnpj = NULLIF(TRIM(NEW.cnpj), '');
    IF NEW.nome = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome da instituicao nao pode ficar vazio.';
    END IF;
    IF NEW.cnpj IS NOT NULL AND NEW.cnpj NOT REGEXP '^[0-9]{14}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CNPJ deve conter exatamente 14 digitos, sem mascara.';
    END IF;
END$$

-- Endereços
CREATE TRIGGER trg_enderecos_bi_validacao
BEFORE INSERT ON enderecos
FOR EACH ROW
BEGIN
    SET NEW.cep = TRIM(NEW.cep);
    SET NEW.cidade = TRIM(NEW.cidade);
    SET NEW.estado = UPPER(TRIM(NEW.estado));
    SET NEW.pais = TRIM(NEW.pais);

    IF NEW.cep NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CEP deve conter 8 digitos, sem mascara.';
    END IF;
    IF NEW.cidade = '' OR NEW.pais = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cidade e pais sao obrigatorios.';
    END IF;
    IF NEW.estado NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estado deve ser informado com 2 letras.';
    END IF;
END$$

CREATE TRIGGER trg_enderecos_bu_validacao
BEFORE UPDATE ON enderecos
FOR EACH ROW
BEGIN
    SET NEW.cep = TRIM(NEW.cep);
    SET NEW.cidade = TRIM(NEW.cidade);
    SET NEW.estado = UPPER(TRIM(NEW.estado));
    SET NEW.pais = TRIM(NEW.pais);

    IF NEW.cep NOT REGEXP '^[0-9]{8}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'CEP deve conter 8 digitos, sem mascara.';
    END IF;
    IF NEW.cidade = '' OR NEW.pais = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cidade e pais sao obrigatorios.';
    END IF;
    IF NEW.estado NOT REGEXP '^[A-Z]{2}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Estado deve ser informado com 2 letras.';
    END IF;
END$$

-- Enfermarias
CREATE TRIGGER trg_enfermarias_bi_validacao
BEFORE INSERT ON enfermarias
FOR EACH ROW
BEGIN
    IF NEW.quantidade_leitos < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantidade de leitos nao pode ser negativa.';
    END IF;
    IF NEW.leitos_disponiveis < 0 OR NEW.leitos_disponiveis > NEW.quantidade_leitos THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Leitos disponiveis deve estar entre 0 e a capacidade total.';
    END IF;
END$$

CREATE TRIGGER trg_enfermarias_bu_validacao
BEFORE UPDATE ON enfermarias
FOR EACH ROW
BEGIN
    IF NEW.quantidade_leitos < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantidade de leitos nao pode ser negativa.';
    END IF;
    IF NEW.leitos_disponiveis < 0 OR NEW.leitos_disponiveis > NEW.quantidade_leitos THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Leitos disponiveis deve estar entre 0 e a capacidade total.';
    END IF;
END$$

-- Eventos Assinaturas
CREATE TRIGGER trg_eventos_assinaturas_bi_validacao
BEFORE INSERT ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF NEW.status = 'ativo' AND NEW.data_aceite IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Assinatura ativa precisa possuir data de aceite.';
    END IF;
    IF NEW.status = 'inativo' AND NEW.data_revogacao IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Assinatura inativa precisa possuir data de revogacao.';
    END IF;
    IF NEW.data_aceite IS NULL AND NEW.data_revogacao IS NOT NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nao e permitido revogar uma assinatura sem aceite.';
    END IF;
    IF NEW.data_aceite IS NOT NULL AND NEW.data_revogacao IS NOT NULL AND NEW.data_revogacao < NEW.data_aceite THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Data de revogacao nao pode ser anterior ao aceite.';
    END IF;
    IF NEW.id_evento IS NOT NULL AND NEW.id_paciente IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM eventos_adversos ea WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Evento e paciente da assinatura nao correspondem.';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_eventos_assinaturas_bu_validacao
BEFORE UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF NEW.status = 'ativo' AND NEW.data_aceite IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Assinatura ativa precisa possuir data de aceite.';
    END IF;
    IF NEW.status = 'inativo' AND NEW.data_revogacao IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Assinatura inativa precisa possuir data de revogacao.';
    END IF;
    IF NEW.data_aceite IS NULL AND NEW.data_revogacao IS NOT NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nao e permitido revogar uma assinatura sem aceite.';
    END IF;
    IF NEW.data_aceite IS NOT NULL AND NEW.data_revogacao IS NOT NULL AND NEW.data_revogacao < NEW.data_aceite THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Data de revogacao nao pode ser anterior ao aceite.';
    END IF;
    IF NEW.id_evento IS NOT NULL AND NEW.id_paciente IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM eventos_adversos ea WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Evento e paciente da assinatura nao correspondem.';
        END IF;
    END IF;
END$$

-- Eventos Adversos
CREATE TRIGGER trg_eventos_adversos_bi_validacao
BEFORE INSERT ON eventos_adversos
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM medicos m WHERE m.id = NEW.id_medico AND m.status = 'ativo') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Somente medico ativo pode registrar evento adverso.';
    END IF;
    IF NEW.descricao_fato IS NULL OR TRIM(NEW.descricao_fato) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Descricao do fato e obrigatoria.';
    END IF;
END$$

CREATE TRIGGER trg_eventos_adversos_bu_validacao
BEFORE UPDATE ON eventos_adversos
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM medicos m WHERE m.id = NEW.id_medico AND m.status = 'ativo') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Somente medico ativo pode manter o evento responsavel.';
    END IF;
    IF NEW.descricao_fato IS NULL OR TRIM(NEW.descricao_fato) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Descricao do fato e obrigatoria.';
    END IF;
    IF NEW.id_assinatura IS NOT NULL THEN
        IF NOT EXISTS (SELECT 1 FROM eventos_assinaturas es WHERE es.id = NEW.id_assinatura AND es.id_evento = NEW.id AND es.id_paciente = NEW.id_paciente) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A assinatura informada nao pertence ao evento e paciente.';
        END IF;
    END IF;
END$$

-- Pacientes Enfermarias
CREATE TRIGGER trg_pacientes_enfermarias_bi_validacao
BEFORE INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM leitos l 
        WHERE l.id = NEW.id_leito 
          AND l.id_enfermaria = NEW.id_enfermaria 
          AND l.status IN ('disponivel', 'ocupado')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou nao esta disponivel.';
    END IF;

    IF NEW.id_evento IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM eventos_adversos ea 
            WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
        END IF;
    END IF;

    IF NEW.movimentacao_leito IS NULL OR TRIM(NEW.movimentacao_leito) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimentacao do leito deve ser informada.';
    END IF;
END$$

CREATE TRIGGER trg_pacientes_enfermarias_bu_validacao
BEFORE UPDATE ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM leitos l 
        WHERE l.id = NEW.id_leito 
          AND l.id_enfermaria = NEW.id_enfermaria 
          AND l.status IN ('disponivel', 'ocupado')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou nao esta disponivel.';
    END IF;

    IF NEW.id_evento IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM eventos_adversos ea 
            WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
        END IF;
    END IF;

    IF NEW.movimentacao_leito IS NULL OR TRIM(NEW.movimentacao_leito) = '' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimentacao do leito deve ser informada.';
    END IF;
END$$

-- Atualiza a fila de admissões quando um paciente é inserido no leito
CREATE TRIGGER trg_pacientes_enfermarias_ai_atualiza_fila
AFTER INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    UPDATE fila_admissoes 
    SET status = 'admitido' 
    WHERE id_paciente = NEW.id_paciente 
      AND id_enfermaria = NEW.id_enfermaria 
      AND status = 'aguardando';
END$$

-- Auditoria: Pacientes
CREATE TRIGGER trg_pacientes_ai_auditoria
AFTER INSERT ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'pacientes', CURRENT_TIMESTAMP, CONCAT('Cadastro de paciente ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_pacientes_au_auditoria
AFTER UPDATE ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'pacientes', CURRENT_TIMESTAMP, CONCAT('Atualizacao de paciente ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_pacientes_ad_auditoria
AFTER DELETE ON pacientes
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'DELETE', 'pacientes', CURRENT_TIMESTAMP, CONCAT('Exclusao de paciente ID ', OLD.id), @continuum_ip_address, 'WARNING');
END$$

-- Auditoria: Medicos
CREATE TRIGGER trg_medicos_ai_auditoria
AFTER INSERT ON medicos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'medicos', CURRENT_TIMESTAMP, CONCAT('Cadastro de medico ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_medicos_au_auditoria
AFTER UPDATE ON medicos
FOR EACH ROW
BEGIN
    IF NOT (OLD.senha_hash <=> NEW.senha_hash) THEN
        INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES (@continuum_usuario_id, 'ALTERACAO_SENHA', 'medicos', CURRENT_TIMESTAMP, CONCAT('Alteracao do hash de senha do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;
    IF NOT (OLD.autenticacao <=> NEW.autenticacao) THEN
        INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES (@continuum_usuario_id, 'ALTERACAO_AUTENTICACAO', 'medicos', CURRENT_TIMESTAMP, CONCAT('Alteracao de autenticacao do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;
    IF NOT (OLD.status <=> NEW.status) THEN
        INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
        VALUES (@continuum_usuario_id, 'ALTERACAO_STATUS', 'medicos', CURRENT_TIMESTAMP, CONCAT('Alteracao de status do medico ID ', NEW.id), @continuum_ip_address, 'SECURITY');
    END IF;
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'medicos', CURRENT_TIMESTAMP, CONCAT('Atualizacao de medico ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

-- Auditoria: Eventos Adversos
CREATE TRIGGER trg_eventos_adversos_ai_auditoria
AFTER INSERT ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'eventos_adversos', CURRENT_TIMESTAMP, CONCAT('Criacao de evento adverso ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_eventos_adversos_au_auditoria
AFTER UPDATE ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'eventos_adversos', CURRENT_TIMESTAMP, CONCAT('Atualizacao de evento adverso ID ', NEW.id), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_eventos_adversos_ad_auditoria
AFTER DELETE ON eventos_adversos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'DELETE', 'eventos_adversos', CURRENT_TIMESTAMP, CONCAT('Exclusao de evento adverso ID ', OLD.id), @continuum_ip_address, 'WARNING');
END$$

-- Auditoria: Eventos Assinaturas
CREATE TRIGGER trg_eventos_assinaturas_ai_auditoria
AFTER INSERT ON eventos_assinaturas
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'eventos_assinaturas', CURRENT_TIMESTAMP, CONCAT('Registro de assinatura ID ', NEW.id), @continuum_ip_address, 'SECURITY');
END$$

CREATE TRIGGER trg_eventos_assinaturas_au_auditoria
AFTER UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'eventos_assinaturas', CURRENT_TIMESTAMP, CONCAT('Atualizacao de assinatura ID ', NEW.id), @continuum_ip_address, 'SECURITY');
END$$

CREATE TRIGGER trg_eventos_assinaturas_au_alerta
AFTER UPDATE ON eventos_assinaturas
FOR EACH ROW
BEGIN
    IF OLD.status <> 'inativo' AND NEW.status = 'inativo' THEN
        INSERT INTO logs_alertas (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em, status)
        VALUES (@continuum_usuario_id, 'REVOGACAO_ASSINATURA', 'HIGH', CONCAT('Assinatura/aceite ID ', NEW.id, ' foi revogado.'), @continuum_ip_address, DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open');
    END IF;
END$$

-- Auditoria: Medicacoes Eventos
CREATE TRIGGER trg_medicacoes_eventos_ai_auditoria
AFTER INSERT ON medicacoes_eventos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'medicacoes_eventos', CURRENT_TIMESTAMP, CONCAT('Registro de medicacao vinculada ao evento ID ', NEW.id_evento), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_medicacoes_eventos_au_auditoria
AFTER UPDATE ON medicacoes_eventos
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'medicacoes_eventos', CURRENT_TIMESTAMP, CONCAT('Atualizacao de medicacao vinculada ao evento ID ', NEW.id_evento), @continuum_ip_address, 'INFO');
END$$

-- Auditoria: Pacientes Enfermarias
CREATE TRIGGER trg_pacientes_enfermarias_ai_auditoria
AFTER INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'INSERT', 'pacientes_enfermarias', CURRENT_TIMESTAMP, CONCAT('Movimentacao do paciente ID ', NEW.id_paciente, ' para leito ID ', NEW.id_leito), @continuum_ip_address, 'INFO');
END$$

CREATE TRIGGER trg_pacientes_enfermarias_au_auditoria
AFTER UPDATE ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    INSERT INTO logs_auditoria (id_usuario, acao_realizada, tabela_afetada, data_hora, descricao, ip_address, severidade)
    VALUES (@continuum_usuario_id, 'UPDATE', 'pacientes_enfermarias', CURRENT_TIMESTAMP, CONCAT('Atualizacao de movimentacao do paciente ID ', NEW.id_paciente), @continuum_ip_address, 'INFO');
END$$

DELIMITER ;


-- ============================================================================
-- 5. ABASTECIMENTO (SEED)
-- ============================================================================
SET @continuum_usuario_id = 1;
SET @continuum_ip_address = '127.0.0.1';

INSERT INTO instituicoes (nome, cnpj, status) VALUES
('Hospital Central São Lucas', '12345678000100', 'ativo');

INSERT INTO medicos (id_instituicao, nome_completo, email, senha_hash, crm_numero, crm_uf, cargo, status, autenticacao) VALUES
(1, 'Dr. Roberto Almeida', 'roberto@continuum.local', 'hash', '10001', 'SP', 'Staff', 'ativo', 1),
(1, 'Dra. Ana Costa', 'ana@continuum.local', 'hash', '10002', 'SP', 'Staff', 'ativo', 1),
(1, 'Dr. Carlos Mendes', 'carlos@continuum.local', 'hash', '10003', 'SP', 'Staff', 'ativo', 1),
(1, 'Dra. Fernanda Lima', 'fernanda@continuum.local', 'hash', '10004', 'SP', 'Staff', 'ativo', 1),
(1, 'Dr. João Pereira', 'joao@continuum.local', 'hash', '10005', 'SP', 'Preceptor', 'ativo', 1),
(1, 'Dra. Mariana Silva', 'mariana@continuum.local', 'hash', '10006', 'SP', 'Preceptor', 'ativo', 1),
(1, 'Dr. Paulo Santos', 'paulo@continuum.local', 'hash', '10007', 'SP', 'Residente R3', 'ativo', 1),
(1, 'Dra. Camila Rocha', 'camila@continuum.local', 'hash', '10008', 'SP', 'Residente R3', 'ativo', 1),
(1, 'Dr. Lucas Martins', 'lucas@continuum.local', 'hash', '10009', 'SP', 'Residente R2', 'ativo', 1),
(1, 'Dra. Beatriz Sousa', 'beatriz@continuum.local', 'hash', '10010', 'SP', 'Residente R2', 'ativo', 1),
(1, 'Dr. Diego Ferreira', 'diego@continuum.local', 'hash', '10011', 'SP', 'Residente R1', 'ativo', 1),
(1, 'Dra. Amanda Alves', 'amanda@continuum.local', 'hash', '10012', 'SP', 'Residente R1', 'ativo', 1),
(1, 'Dr. Thiago Gomes', 'thiago@continuum.local', 'hash', '10013', 'SP', 'Interno', 'ativo', 1),
(1, 'Dra. Letícia Carvalho', 'leticia@continuum.local', 'hash', '10014', 'SP', 'Interno', 'ativo', 1),
(1, 'Dr. Rodrigo Castro', 'rodrigo@continuum.local', 'hash', '10015', 'SP', 'Staff', 'ativo', 1),
(1, 'Dra. Juliana Ribeiro', 'juliana@continuum.local', 'hash', '10016', 'SP', 'Staff', 'ativo', 1),
(1, 'Dr. Marcelo Pinto', 'marcelo@continuum.local', 'hash', '10017', 'SP', 'Staff', 'ativo', 1),
(1, 'Dra. Vanessa Melo', 'vanessa@continuum.local', 'hash', '10018', 'SP', 'Residente R+','ativo', 1),
(1, 'Dr. Bruno Nunes', 'bruno@continuum.local', 'hash', '10019', 'SP', 'Interno', 'ativo', 1),
(1, 'Dra. Carolina Dias', 'carolina@continuum.local', 'hash', '10020', 'SP', 'Interno', 'ativo', 1);

INSERT INTO setores (id_instituicao, nome, tipo, responsavel_plantao, class_risco) VALUES
(1, 'Setor Clínica Geral', 'Clínica Geral', 1, 'baixo'),
(1, 'Setor Cirúrgico', 'Cirúrgica', 2, 'medio'),
(1, 'Setor Obstétrico', 'Obstétrica e Perinatal', 3, 'medio'),
(1, 'Setor de Cardiologia', 'Cardiologia', 4, 'alto'),
(1, 'Setor de Neurologia', 'Neurologia', 5, 'alto'),
(1, 'Setor de Isolamento', 'Isolamento', 6, 'alto'),
(1, 'Enfermaria', 'Enfermaria', 7, 'baixo');

INSERT INTO enfermarias (id_setor, medico_responsavel, quantidade_leitos, leitos_disponiveis, status) VALUES
(1, 1, 20, 20, 'ativo'),
(2, 2, 20, 20, 'ativo'),
(3, 3, 10, 10, 'ativo'),
(4, 4, 10, 10, 'ativo'),
(5, 5, 10, 10, 'ativo'),
(6, 6, 5, 5, 'ativo'),
(7, 7, 10, 10, 'ativo');

INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(1, 'CG-101', 'disponivel'), (1, 'CG-102', 'disponivel'), (1, 'CG-103', 'disponivel'), (1, 'CG-104', 'disponivel'), (1, 'CG-105', 'disponivel'),
(1, 'CG-106', 'disponivel'), (1, 'CG-107', 'disponivel'), (1, 'CG-108', 'disponivel'), (1, 'CG-109', 'disponivel'), (1, 'CG-110', 'disponivel'),
(1, 'CG-111', 'disponivel'), (1, 'CG-112', 'disponivel'), (1, 'CG-113', 'disponivel'), (1, 'CG-114', 'disponivel'), (1, 'CG-115', 'disponivel'),
(1, 'CG-116', 'disponivel'), (1, 'CG-117', 'disponivel'), (1, 'CG-118', 'disponivel'), (1, 'CG-119', 'disponivel'), (1, 'CG-120', 'disponivel'),
(2, 'CC-201', 'disponivel'), (2, 'CC-202', 'disponivel'), (2, 'CC-203', 'disponivel'), (2, 'CC-204', 'disponivel'), (2, 'CC-205', 'disponivel'),
(2, 'CC-206', 'disponivel'), (2, 'CC-207', 'disponivel'), (2, 'CC-208', 'disponivel'), (2, 'CC-209', 'disponivel'), (2, 'CC-210', 'disponivel'),
(2, 'CC-211', 'disponivel'), (2, 'CC-212', 'disponivel'), (2, 'CC-213', 'disponivel'), (2, 'CC-214', 'disponivel'), (2, 'CC-215', 'disponivel'),
(2, 'CC-216', 'disponivel'), (2, 'CC-217', 'disponivel'), (2, 'CC-218', 'disponivel'), (2, 'CC-219', 'disponivel'), (2, 'CC-220', 'disponivel'),
(3, 'OB-301', 'disponivel'), (3, 'OB-302', 'disponivel'), (3, 'OB-303', 'disponivel'), (3, 'OB-304', 'disponivel'), (3, 'OB-305', 'disponivel'),
(3, 'OB-306', 'disponivel'), (3, 'OB-307', 'disponivel'), (3, 'OB-308', 'disponivel'), (3, 'OB-309', 'disponivel'), (3, 'OB-310', 'disponivel'),
(4, 'UC-401', 'disponivel'), (4, 'UC-402', 'disponivel'), (4, 'UC-403', 'disponivel'), (4, 'UC-404', 'disponivel'), (4, 'UC-405', 'disponivel'),
(4, 'UC-406', 'disponivel'), (4, 'UC-407', 'disponivel'), (4, 'UC-408', 'disponivel'), (4, 'UC-409', 'disponivel'), (4, 'UC-410', 'disponivel'),
(5, 'UN-501', 'disponivel'), (5, 'UN-502', 'disponivel'), (5, 'UN-503', 'disponivel'), (5, 'UN-504', 'disponivel'), (5, 'UN-505', 'disponivel'),
(5, 'UN-506', 'disponivel'), (5, 'UN-507', 'disponivel'), (5, 'UN-508', 'disponivel'), (5, 'UN-509', 'disponivel'), (5, 'UN-510', 'disponivel'),
(6, 'IS-601', 'disponivel'), (6, 'IS-602', 'disponivel'), (6, 'IS-603', 'disponivel'), (6, 'IS-604', 'disponivel'), (6, 'IS-605', 'disponivel'),
(7, 'ENF-701', 'disponivel'), (7, 'ENF-702', 'disponivel'), (7, 'ENF-703', 'disponivel'), (7, 'ENF-704', 'disponivel'), (7, 'ENF-705', 'disponivel'),
(7, 'ENF-706', 'disponivel'), (7, 'ENF-707', 'disponivel'), (7, 'ENF-708', 'disponivel'), (7, 'ENF-709', 'disponivel'), (7, 'ENF-710', 'disponivel');

INSERT INTO pacientes (nome_completo, email, cpf, data_nascimento, sexo, etnia, senha_hash, telefone, aceite_termo_uso) VALUES
('Alice Moreira', 'alice.moreira@continuum.local', '10111111111', '1950-01-10', 'feminino', 'branca', 'hash', '11900000001', 'sim'),
('Bruno Fonseca', 'bruno.fonseca@continuum.local', '10222222222', '1962-02-15', 'masculino', 'parda', 'hash', '11900000002', 'sim'),
('Carla Dias', 'carla.dias@continuum.local', '10333333333', '1975-03-20', 'feminino', 'preta', 'hash', '11900000003', 'sim'),
('Daniel Castro', 'daniel.castro@continuum.local', '10444444444', '1988-04-25', 'masculino', 'branca', 'hash', '11900000004', 'sim'),
('Elisa Ribeiro', 'elisa.ribeiro@continuum.local', '10555555555', '1995-05-30', 'feminino', 'amarela', 'hash', '11900000005', 'sim'),
('Fábio Santos', 'fabio.santos@continuum.local', '10666666666', '2001-06-05', 'masculino', 'parda', 'hash', '11900000006', 'sim'),
('Gabriela Lima', 'gabriela.lima@continuum.local', '10777777777', '1945-07-10', 'feminino', 'branca', 'hash', '11900000007', 'sim'),
('Hugo Martins', 'hugo.martins@continuum.local', '10888888888', '1958-08-15', 'masculino', 'preta', 'hash', '11900000008', 'sim'),
('Isabela Alves', 'isabela.alves@continuum.local', '10999999999', '1969-09-20', 'feminino', 'branca', 'hash', '11900000009', 'sim'),
('João Silva', 'joao.silva@continuum.local', '11000000000', '1982-10-25', 'masculino', 'parda', 'hash', '11900000010', 'sim'),
('Karen Souza', 'karen.souza@continuum.local', '11111111101', '1990-11-30', 'feminino', 'amarela', 'hash', '11900000011', 'sim'),
('Lucas Oliveira', 'lucas.oliveira@continuum.local', '11222222202', '2005-12-05', 'masculino', 'branca', 'hash', '11900000012', 'sim'),
('Mariana Costa', 'mariana.costa@continuum.local', '11333333303', '1938-01-12', 'feminino', 'parda', 'hash', '11900000013', 'sim'),
('Nicolas Ferreira', 'nicolas.ferreira@continuum.local', '11444444404', '1949-02-18', 'masculino', 'preta', 'hash', '11900000014', 'sim'),
('Olívia Gomes', 'olivia.gomes@continuum.local', '11555555505', '1960-03-24', 'feminino', 'branca', 'hash', '11900000015', 'sim'),
('Pedro Mendes', 'pedro.mendes@continuum.local', '11666666606', '1971-04-29', 'masculino', 'parda', 'hash', '11900000016', 'sim'),
('Quintina Ramos', 'quintina.ramos@continuum.local', '11777777707', '1984-05-04', 'feminino', 'amarela', 'hash', '11900000017', 'sim'),
('Rafael Pinto', 'rafael.pinto@continuum.local', '11888888808', '1997-06-09', 'masculino', 'branca', 'hash', '11900000018', 'sim'),
('Sofia Carvalho', 'sofia.carvalho@continuum.local', '11999999909', '2010-07-14', 'feminino', 'parda', 'hash', '11900000019', 'sim'),
('Tiago Barbosa', 'tiago.barbosa@continuum.local', '12000000010', '1942-08-19', 'masculino', 'preta', 'hash', '11900000020', 'sim'),
('Úrsula Rocha', 'ursula.rocha@continuum.local', '12111111111', '1953-09-24', 'feminino', 'branca', 'hash', '11900000021', 'sim'),
('Victor Cardoso', 'victor.cardoso@continuum.local', '12222222212', '1964-10-29', 'masculino', 'parda', 'hash', '11900000022', 'sim'),
('Wanessa Teixeira', 'wanessa.teixeira@continuum.local', '12333333313', '1977-11-03', 'feminino', 'amarela', 'hash', '11900000023', 'sim'),
('Xavier Moura', 'xavier.moura@continuum.local', '12444444414', '1989-12-08', 'masculino', 'branca', 'hash', '11900000024', 'sim'),
('Yasmin Nunes', 'yasmin.nunes@continuum.local', '12555555515', '2002-01-13', 'feminino', 'parda', 'hash', '11900000025', 'sim'),
('Zeca Melo', 'zeca.melo@continuum.local', '12666666616', '1935-02-18', 'masculino', 'preta', 'hash', '11900000026', 'sim'),
('Amanda Farias', 'amanda.farias@continuum.local', '12777777717', '1948-03-25', 'feminino', 'branca', 'hash', '11900000027', 'sim'),
('Breno Pires', 'breno.pires@continuum.local', '12888888818', '1959-04-30', 'masculino', 'parda', 'hash', '11900000028', 'sim'),
('Camila Moraes', 'camila.moraes@continuum.local', '12999999919', '1970-05-05', 'feminino', 'amarela', 'hash', '11900000029', 'sim'),
('Diego Viana', 'diego.viana@continuum.local', '13000000020', '1983-06-10', 'masculino', 'branca', 'hash', '11900000030', 'sim'),
('Eliane Neves', 'eliane.neves@continuum.local', '13111111121', '1994-07-15', 'feminino', 'parda', 'hash', '11900000031', 'sim'),
('Fernando Monteiro', 'fernando.monteiro@continuum.local', '13222222222', '2008-08-20', 'masculino', 'preta', 'hash', '11900000032', 'sim'),
('Gisele Freitas', 'gisele.freitas@continuum.local', '13333333323', '1940-09-25', 'feminino', 'branca', 'hash', '11900000033', 'sim'),
('Heitor Silveira', 'heitor.silveira@continuum.local', '13444444424', '1951-10-30', 'masculino', 'parda', 'hash', '11900000034', 'sim'),
('Ingrid Peixoto', 'ingrid.peixoto@continuum.local', '13555555525', '1963-11-04', 'feminino', 'amarela', 'hash', '11900000035', 'sim'),
('Julio Faria', 'julio.faria@continuum.local', '13666666626', '1976-12-09', 'masculino', 'branca', 'hash', '11900000036', 'sim'),
('Kelly Dantas', 'kelly.dantas@continuum.local', '13777777727', '1987-01-14', 'feminino', 'parda', 'hash', '11900000037', 'sim'),
('Leandro Barros', 'leandro.barros@continuum.local', '13888888828', '1999-02-19', 'masculino', 'preta', 'hash', '11900000038', 'sim'),
('Márcia Correia', 'marcia.correia@continuum.local', '13999999929', '1937-03-26', 'feminino', 'branca', 'hash', '11900000039', 'sim'),
('Natan Araújo', 'natan.araujo@continuum.local', '14000000030', '1954-04-01', 'masculino', 'parda', 'hash', '11900000040', 'sim'),
('Odete Marques', 'odete.marques@continuum.local', '14111111131', '1967-05-06', 'feminino', 'amarela', 'hash', '11900000041', 'sim'),
('Pablo Guedes', 'pablo.guedes@continuum.local', '14222222232', '1980-06-11', 'masculino', 'branca', 'hash', '11900000042', 'sim'),
('Quésia Lemos', 'quesia.lemos@continuum.local', '14333333333', '1993-07-16', 'feminino', 'parda', 'hash', '11900000043', 'sim'),
('Renato Cruz', 'renato.cruz@continuum.local', '14444444434', '2004-08-21', 'masculino', 'preta', 'hash', '11900000044', 'sim'),
('Sandra Reis', 'sandra.reis@continuum.local', '14555555535', '1943-09-26', 'feminino', 'branca', 'hash', '11900000045', 'sim'),
('Túlio Nogueira', 'tulio.nogueira@continuum.local', '14666666636', '1956-10-31', 'masculino', 'parda', 'hash', '11900000046', 'sim'),
('Ubirajara Canto', 'ubirajara.canto@continuum.local', '14777777737', '1968-11-05', 'masculino', 'amarela', 'hash', '11900000047', 'sim'),
('Vera Lúcia', 'vera.lucia@continuum.local', '14888888838', '1981-12-10', 'feminino', 'branca', 'hash', '11900000048', 'sim'),
('Wagner Teles', 'wagner.teles@continuum.local', '14999999939', '1995-01-15', 'masculino', 'parda', 'hash', '11900000049', 'sim'),
('Xuxa Meneghel', 'xuxa.meneghel@continuum.local', '15000000040', '1963-03-27', 'feminino', 'branca', 'hash', '11900000050', 'sim');

INSERT INTO enderecos (id_paciente, cep, cidade, estado, status)
SELECT id, '08900000', 'Guararema', 'SP', 'ativo' FROM pacientes;

INSERT INTO alergias (tipo, gravidade, contra_medida) VALUES
('Penicilina', 'alta', 'Evitar Beta-lactâmicos, usar Macrolídeos.'),
('Dipirona', 'alta', 'Substituir por Paracetamol ou Ibuprofeno.'),
('Iodo (Contraste)', 'alta', 'Evitar exames com contraste iodado. Usar preparo antialérgico.'),
('Látex', 'alta', 'Utilizar luvas e materiais free-latex.'),
('AINEs (Anti-inflamatórios)', 'media', 'Evitar Diclofenaco/Ibuprofeno. Usar Corticoides se necessário.'),
('Frutos do Mar', 'alta', 'Risco de Anafilaxia. Dieta restrita.'),
('Amendoim', 'alta', 'Risco Anafilático. Evitar traços alimentares.'),
('Ovos', 'media', 'Cuidado com vacinas cultivadas em ovos (ex: Febre Amarela).'),
('Leite (APLV)', 'media', 'Dieta isenta de proteína do leite de vaca.'),
('Cefalosporinas', 'alta', 'Risco de reação cruzada com penicilina.'),
('Macrolídeos', 'media', 'Substituir antibiótico por outra classe.'),
('Clorexidina', 'alta', 'Usar PVPI ou Álcool 70% para assepsia.'),
('Neomicina', 'baixa', 'Evitar pomadas tópicas com neomicina.'),
('Corticoides', 'media', 'Avaliar classe estrutural antes de prescrever.'),
('AAS (Aspirina)', 'alta', 'Risco de broncoespasmo. Substituir antiagregante.'),
('Plasil (Metoclopramida)', 'media', 'Usar Ondansetrona ou Bromoprida para ânsea.'),
('Insulina Animal', 'media', 'Usar apenas Insulinas Humanas/Análogas.'),
('Picada de Abelha', 'alta', 'Manter Epinefrina SOS.'),
('Poeira / Ácaros', 'baixa', 'Manter ambiente higienizado e arejado.'),
('Mofo', 'baixa', 'Controle ambiental.'),
('Pêlo de Gato', 'baixa', 'Antialérgicos SOS.'),
('Pêlo de Cachorro', 'baixa', 'Antialérgicos SOS.'),
('Soja', 'media', 'Dieta restrita.'),
('Glúten (Doença Celíaca)', 'alta', 'Dieta rigorosamente sem glúten.'),
('Corantes Artificiais', 'baixa', 'Preferir medicações sem corante tartrazina.'),
('Sulfonamidas (Sulfa)', 'alta', 'Evitar Bactrim (Sulfametoxazol+Trimetoprima).'),
('Morfina / Opióides', 'alta', 'Risco de depressão respiratória aguda. Usar alternativas.'),
('Tramadol', 'media', 'Pode causar náusea severa e rash cutâneo.'),
('Gelo / Frio (Urticária)', 'baixa', 'Evitar compressas de gelo.'),
('Fita Adesiva / Micropore', 'baixa', 'Usar ataduras ou fitas de silicone.');

INSERT INTO alergias_pacientes (id_paciente, id_alergia) VALUES
(1, 1), (1, 5), (2, 2), (3, 3), (3, 4), (4, 15), (5, 6), (6, 7), (8, 10), (10, 26),
(12, 1), (15, 27), (18, 12), (20, 2), (22, 16), (25, 28), (28, 4), (30, 1), (32, 24), (35, 8),
(40, 9), (42, 1), (45, 2), (48, 11), (50, 26), (7, 18), (14, 19), (21, 20), (33, 21), (38, 22);

INSERT INTO medicacoes (principio_ativo, nome_comercial, tipo, dosagem) VALUES
('Dipirona', 'Novalgina', 'Analgésico/Antitérmico', '1g/2mL'),
('Paracetamol', 'Tylenol', 'Analgésico/Antitérmico', '750mg'),
('Ibuprofeno', 'Alivium', 'AINE', '600mg'),
('Cetoprofeno', 'Profenid', 'AINE', '100mg IV'),
('Tramadol', 'Tramal', 'Analgésico Opióide', '50mg/mL'),
('Morfina', 'Dimorf', 'Analgésico Opióide Forte', '10mg/mL'),
('Fentanil', 'Fentanil', 'Anestésico/Opióide', '50mcg/mL'),
('Midazolam', 'Dormonid', 'Sedativo/Benzodiazepínico', '15mg/3mL'),
('Propofol', 'Diprivan', 'Anestésico Geral', '10mg/mL'),
('Ondansetrona', 'Vonau', 'Antiemético', '4mg/2mL'),
('Metoclopramida', 'Plasil', 'Antiemético', '10mg/2mL'),
('Omeprazol', 'Losec', 'Inibidor de Bomba de Prótons', '40mg IV'),
('Pantoprazol', 'Pantocal', 'Inibidor de Bomba de Prótons', '40mg IV'),
('Cefazolina', 'Kefazol', 'Antibiótico', '1g IV'),
('Ceftriaxona', 'Rocefin', 'Antibiótico', '1g IV'),
('Meropenem', 'Meronem', 'Antibiótico Amplo Espectro', '1g IV'),
('Vancomicina', 'Vancocina', 'Antibiótico', '500mg IV'),
('Piperacilina+Tazobactam', 'Tazocin', 'Antibiótico', '4.5g IV'),
('Azitromicina', 'Zitromax', 'Antibiótico', '500mg'),
('Clindamicina', 'Dalacin', 'Antibiótico', '600mg IV'),
('Amoxicilina+Clavulanato', 'Clavulin', 'Antibiótico', '875/125mg'),
('Ciprofloxacino', 'Cipro', 'Antibiótico', '400mg IV'),
('Levofloxacino', 'Tavanic', 'Antibiótico', '500mg IV'),
('Fluconazol', 'Zoltec', 'Antifúngico', '200mg IV'),
('Heparina', 'Liquemine', 'Anticoagulante', '5000 UI/mL'),
('Enoxaparina', 'Clexane', 'Anticoagulante', '40mg/0.4mL SC'),
('AAS', 'Aspirina', 'Antiagregante Plaquetário', '100mg'),
('Clopidogrel', 'Plavix', 'Antiagregante Plaquetário', '75mg'),
('Amiodarona', 'Ancoron', 'Antiarrítmico', '150mg/3mL IV'),
('Noradrenalina', 'Levophed', 'Vasopressor', '4mg/4mL'),
('Adrenalina', 'Epinefrina', 'Simpatomimético', '1mg/mL'),
('Dobutamina', 'Dobutrex', 'Inotrópico', '250mg/20mL'),
('Furosemida', 'Lasix', 'Diurético', '20mg/2mL IV'),
('Hidrocortisona', 'Solu-Cortef', 'Corticosteroide', '500mg IV'),
('Dexametasona', 'Decadron', 'Corticosteroide', '4mg/mL'),
('Prednisona', 'Meticorten', 'Corticosteroide', '20mg'),
('Insulina Regular', 'Novolin R', 'Hipoglicemiante', '100 UI/mL'),
('Insulina NPH', 'Novolin N', 'Hipoglicemiante', '100 UI/mL'),
('Losartana', 'Aradois', 'Anti-hipertensivo', '50mg'),
('Amlodipina', 'Pressat', 'Anti-hipertensivo', '5mg');

INSERT INTO fila_admissoes (id_paciente, id_enfermaria, origem, gravidade, status, data_solicitacao) VALUES
(46, 1, 'pronto_socorro', 'media', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR)),
(47, 2, 'ambulatorio', 'baixa', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 HOUR)),
(48, 4, 'centro_cirurgico', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)),
(49, 5, 'pronto_socorro', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR)),
(50, 6, 'transferencia_externa', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR));

INSERT INTO pacientes_enfermarias (id_enfermaria, id_paciente, id_evento, id_leito, movimentacao_leito) VALUES 
(1, 1, NULL, 1, 'Admissão'), (1, 2, NULL, 2, 'Admissão'), 
(1, 3, NULL, 3, 'Admissão'), (1, 4, NULL, 4, 'Admissão'), 
(1, 5, NULL, 5, 'Admissão'), (1, 6, NULL, 6, 'Admissão'), 
(1, 7, NULL, 7, 'Admissão'), (1, 8, NULL, 8, 'Admissão'), 
(1, 9, NULL, 9, 'Admissão'), (1, 10, NULL, 10, 'Admissão'), 
(1, 11, NULL, 11, 'Admissão'), (1, 12, NULL, 12, 'Admissão'), 
(1, 13, NULL, 13, 'Admissão'), (1, 14, NULL, 14, 'Admissão'), 
(1, 15, NULL, 15, 'Admissão'),
(2, 16, NULL, 21, 'Admissão Pós-Op'), (2, 17, NULL, 22, 'Admissão'), 
(2, 18, NULL, 23, 'Admissão'), (2, 19, NULL, 24, 'Admissão'), 
(2, 20, NULL, 25, 'Admissão'), (2, 21, NULL, 26, 'Admissão'), 
(2, 22, NULL, 27, 'Admissão'), (2, 23, NULL, 28, 'Admissão'), 
(2, 24, NULL, 29, 'Admissão'), (2, 25, NULL, 30, 'Admissão'),
(3, 27, NULL, 41, 'Admissão Parto'), (3, 29, NULL, 42, 'Admissão'), 
(3, 31, NULL, 43, 'Admissão'), (3, 33, NULL, 44, 'Admissão'), 
(3, 35, NULL, 45, 'Admissão'),
(4, 36, NULL, 51, 'Transferência para UTI'), (4, 37, NULL, 52, 'Admissão PS'), 
(4, 38, NULL, 53, 'Admissão Infarto'), (4, 39, NULL, 54, 'Admissão ICC'), 
(4, 40, NULL, 55, 'Admissão Arritmia'),
(5, 41, NULL, 61, 'Admissão AVC'), (5, 42, NULL, 62, 'Admissão TCE'), 
(5, 43, NULL, 63, 'Admissão Pós-Op'),
(6, 44, NULL, 71, 'Admissão Tuberculose'), (6, 45, NULL, 72, 'Admissão KPC'),
(7, 26, NULL, 76, 'Admissão de demonstração'), (7, 28, NULL, 77, 'Admissão de demonstração'),
(7, 30, NULL, 78, 'Admissão de demonstração'), (7, 32, NULL, 79, 'Admissão de demonstração');

UPDATE leitos SET status = 'ocupado' WHERE id IN (
  1,2,3,4,5,6,7,8,9,10,11,12,13,14,15, 
  21,22,23,24,25,26,27,28,29,30,
  41,42,43,44,45,
  51,52,53,54,55,
  61,62,63,
  71,72,76,77,78,79
);

UPDATE enfermarias
SET leitos_disponiveis = (
  SELECT COUNT(*) FROM leitos
  WHERE leitos.id_enfermaria = enfermarias.id AND leitos.status = 'disponivel'
)
WHERE id = 7;

INSERT INTO eventos_adversos (id_paciente, id_medico, tipo_evento, grau_dano, descricao_fato, data_evento, status_investigacao, id_assinatura) VALUES
(1, 1, 'medicamento', 'medio', 'Administração de dose incorreta de antibiótico. Corrigido a tempo, leve rash.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY), 'concluido', NULL),
(2, 2, 'queda', 'alto', 'Paciente escorregou no banheiro. Trauma craniano leve.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR), 'pendente', NULL),
(3, 3, 'lesao_pressao', 'baixo', 'Hiperemia em região sacral detectada no banho.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY), 'concluido', NULL),
(4, 4, 'dispositivo', 'alto', 'Paciente agitado sacou o tubo orotraqueal. Reintubado.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 HOUR), 'em_investigacao', NULL),
(5, 5, 'infeccao', 'medio', 'Sinais de flebite no acesso venoso periférico.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY), 'concluido', NULL),
(6, 6, 'identificacao_incorreta', 'sem_dano', 'Pulseira trocada, identificado antes da medicação.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 DAY), 'concluido', NULL),
(7, 7, 'cirurgia', 'alto', 'Sangramento excessivo no pós-op imediato.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 8 HOUR), 'pendente', NULL),
(8, 8, 'transfusao', 'alto', 'Reação transfusional febril não hemolítica.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY), 'concluido', NULL),
(9, 9, 'queda', 'medio', 'Queda da própria altura no corredor, escoriações.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 HOUR), 'pendente', NULL),
(10, 10, 'medicamento', 'alto', 'Anafilaxia após início de infusão de Cefazolina.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY), 'em_investigacao', NULL),
(11, 1, 'lesao_pressao', 'baixo', 'Estágio 1 em calcanhar.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 48 HOUR), 'concluido', NULL),
(12, 2, 'medicamento', 'sem_dano', 'Atraso de 2 horas na administração de antibiótico.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 50 HOUR), 'concluido', NULL),
(13, 3, 'infeccao', 'medio', 'Cultura de urina positiva após sondagem de demora.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 72 HOUR), 'concluido', NULL),
(14, 4, 'comunicacao', 'sem_dano', 'Falha na passagem de plantão sobre jejum do paciente.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 HOUR), 'pendente', NULL),
(15, 5, 'dispositivo', 'medio', 'Obstrução de cateter venoso central.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 14 HOUR), 'pendente', NULL),
(16, 6, 'queda', 'alto', 'Queda do leito por grade abaixada. Fratura de fêmur.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 20 HOUR), 'em_investigacao', NULL),
(17, 7, 'medicamento', 'alto', 'Paciente recebeu heparina em vez de insulina.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 22 HOUR), 'em_investigacao', NULL),
(18, 8, 'alimentacao', 'medio', 'Broncoaspiração leve durante dieta enteral.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 HOUR), 'concluido', NULL),
(19, 9, 'cirurgia', 'alto', 'Retenção de compressa cirúrgica (near miss).', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 40 HOUR), 'concluido', NULL),
(20, 10, 'documentacao', 'sem_dano', 'Prontuário trocado na prescrição.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR), 'pendente', NULL),
(21, 11, 'lesao_pressao', 'medio', 'Úlcera estágio 2 no trocânter.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 60 HOUR), 'concluido', NULL),
(22, 12, 'transfusao', 'baixo', 'Atraso na liberação da bolsa de sangue.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR), 'pendente', NULL),
(23, 13, 'exame_diagnostico', 'baixo', 'Perda de amostra de líquor pelo laboratório.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 HOUR), 'em_investigacao', NULL),
(24, 14, 'fuga', 'alto', 'Paciente evadiu-se da enfermaria psiquiátrica.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY), 'concluido', NULL),
(25, 15, 'medicamento', 'sem_dano', 'Dose pulada por recusa do paciente não notificada.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY), 'concluido', NULL),
(26, 16, 'infeccao', 'alto', 'Sepse foco pulmonar adquirida na UTI (PAV).', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY), 'concluido', NULL),
(27, 17, 'identificacao_incorreta', 'medio', 'Coleta de sangue no paciente errado.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 18 HOUR), 'concluido', NULL),
(28, 18, 'queda', 'baixo', 'Paciente escorregou da poltrona, sem lesões.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 HOUR), 'pendente', NULL),
(29, 19, 'comunicacao', 'alto', 'Resultado de potássio crítico não avisado ao plantonista.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 16 HOUR), 'em_investigacao', NULL),
(30, 20, 'lesao_pressao', 'baixo', 'Vermelhidão em orelha por cateter de O2.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 HOUR), 'pendente', NULL),
(31, 1, 'dispositivo', 'alto', 'Dreno torácico sacado acidentalmente.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 DAY), 'concluido', NULL),
(32, 2, 'medicamento', 'medio', 'Flebitite química após infusão de Amiodarona.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 DAY), 'concluido', NULL),
(33, 3, 'procedimento', 'alto', 'Pneumotórax iatrogênico após punção de acesso central.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 DAY), 'concluido', NULL),
(34, 4, 'infeccao', 'medio', 'Sutura cirúrgica com deiscência e secreção purulenta.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY), 'concluido', NULL),
(35, 5, 'transfusao', 'alto', 'TRALI (Lesão Pulmonar Aguda Associada à Transfusão).', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 DAY), 'concluido', NULL),
(36, 6, 'documentacao', 'sem_dano', 'Evolução médica colada em prontuário errado.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY), 'concluido', NULL),
(37, 7, 'alimentacao', 'medio', 'Dieta liberada para paciente que deveria estar em jejum.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR), 'pendente', NULL),
(38, 8, 'lesao_pressao', 'alto', 'Úlcera sacral estágio 3.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR), 'em_investigacao', NULL),
(39, 9, 'queda', 'medio', 'Queda ao tentar ir ao banheiro sozinho durante a noite.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 HOUR), 'pendente', NULL),
(40, 10, 'medicamento', 'sem_dano', 'Dipirona administrada em paciente alérgico (detectado no rash inicial).', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 HOUR), 'em_investigacao', NULL),
(41, 11, 'exame_diagnostico', 'medio', 'Queimadura leve na ressonância magnética devido a eletrodo.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR), 'em_investigacao', NULL),
(42, 12, 'comunicacao', 'alto', 'Atraso na transferência para UTI por falta de contato.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 6 HOUR), 'pendente', NULL),
(43, 13, 'dispositivo', 'baixo', 'Perda de acesso venoso periférico.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 HOUR), 'pendente', NULL),
(44, 14, 'fuga', 'medio', 'Paciente evadiu para fumar e retornou.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 8 HOUR), 'concluido', NULL),
(45, 15, 'infeccao', 'alto', 'Infeccao de corrente sanguinea associada a cateter (ICSB).', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 9 HOUR), 'em_investigacao', NULL),
(46, 16, 'lesao_pressao', 'baixo', 'Hiperemia occipital.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 10 HOUR), 'pendente', NULL),
(47, 17, 'medicamento', 'alto', 'Extravasamento de noradrenalina com necrose tecidual.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 11 HOUR), 'em_investigacao', NULL),
(48, 18, 'procedimento', 'medio', 'Lesão de uretra na passagem de sonda vesical.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 HOUR), 'pendente', NULL),
(49, 19, 'identificacao_incorreta', 'sem_dano', 'Dieta servida ao leito errado, não consumida.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 13 HOUR), 'concluido', NULL),
(50, 20, 'queda', 'alto', 'Queda de maca durante transporte. TCE leve.', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 14 HOUR), 'pendente', NULL);

INSERT INTO eventos_adversos (id_paciente, id_medico, tipo_evento, grau_dano, descricao_fato, data_evento, status_investigacao, id_assinatura)
SELECT id_paciente, id_medico, tipo_evento, grau_dano, CONCAT('Reincidência: ', descricao_fato), DATE_SUB(data_evento, INTERVAL 15 DAY), 'concluido', NULL
FROM eventos_adversos LIMIT 50;

INSERT INTO eventos_assinaturas (id_evento, id_paciente, nome_evento, tipo, finalidade, data_aceite, status) VALUES 
(1, 1, 'Termo de Alta do Leito 101', 'termo_alta', 'Alta hospitalar a pedido.', CURRENT_TIMESTAMP, 'ativo'), 
(3, 3, 'Termo de Alta do Leito 103', 'termo_alta', 'Alta médica por melhora.', CURRENT_TIMESTAMP, 'ativo'), 
(5, 5, 'Termo de Alta do Leito 105', 'termo_alta', 'Transferência de cuidado.', CURRENT_TIMESTAMP, 'ativo'), 
(10, 10, 'Termo de Consentimento para Cirurgia', 'autorizacao_cirurgia', 'Amputação de hálux.', CURRENT_TIMESTAMP, 'ativo');

INSERT INTO medicacoes_eventos (id_medicamento, id_evento, dose_administrada, via_administrada, frequencia) 
SELECT 1, id, '1g', 'intravenosa', '8/8h' FROM eventos_adversos ORDER BY id ASC LIMIT 1 OFFSET 0;

INSERT INTO medicacoes_eventos (id_medicamento, id_evento, dose_administrada, via_administrada, frequencia) 
SELECT 6, id, '4mg', 'intravenosa', 'SOS' FROM eventos_adversos ORDER BY id ASC LIMIT 1 OFFSET 1;

INSERT INTO medicacoes_eventos (id_medicamento, id_evento, dose_administrada, via_administrada, frequencia) 
SELECT 14, id, '1g', 'intravenosa', '12/12h' FROM eventos_adversos ORDER BY id ASC LIMIT 1 OFFSET 2;

INSERT INTO medicacoes_eventos (id_medicamento, id_evento, dose_administrada, via_administrada, frequencia) 
SELECT 25, id, '5000 UI', 'subcutanea', '12/12h' FROM eventos_adversos ORDER BY id ASC LIMIT 1 OFFSET 3;

INSERT INTO medicacoes_eventos (id_medicamento, id_evento, dose_administrada, via_administrada, frequencia) 
SELECT 30, id, '10mcg/min', 'intravenosa', 'Contínuo' FROM eventos_adversos ORDER BY id ASC LIMIT 1 OFFSET 4;

INSERT INTO logs_alertas (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em, status) VALUES
(1, 'SISTEMA_MONITORIZACAO', 'CRITICAL', 'Parada Cardiorrespiratória detectada no Leito UTI-401.', '10.0.0.5', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(2, 'LABORATORIO', 'HIGH', 'Potássio crítico (7.2 mEq/L) no Leito CG-112.', '10.0.0.12', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(3, 'FARMACIA', 'MEDIUM', 'Estoque de Fentanil abaixo da margem de segurança na UTI.', '10.0.0.8', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(4, 'SEGURANCA_TI', 'CRITICAL', 'Tentativa de acesso não autorizado aos prontuários.', '192.168.1.100', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open');