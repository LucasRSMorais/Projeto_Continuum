USE railway;

ALTER TABLE eventos_adversos
    MODIFY COLUMN id_paciente INT NULL,
    ADD COLUMN id_setor INT NULL AFTER id_medico,
    MODIFY COLUMN tipo_evento ENUM(
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
    ) NOT NULL;

ALTER TABLE eventos_adversos
    ADD CONSTRAINT fk_eventos_adversos_setor
        FOREIGN KEY (id_setor)
        REFERENCES setores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    ADD KEY idx_eventos_adversos_setor (id_setor);

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
