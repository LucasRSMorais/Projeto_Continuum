USE railway;

-- 1.1 Criação da tabela transacional para a fila de espera
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

-- 1.2 Gatilho (Trigger) para automatizar a baixa na fila de admissões
-- Quando um paciente for inserido na tabela 'pacientes_enfermarias', a fila é atualizada.
DELIMITER $$
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
DELIMITER ;


-- 2.1 View para o Mapa Rápido de Leitos (Inclui alerta de alergias severas)
CREATE OR REPLACE VIEW vw_dash_mapa_leitos AS 
SELECT      
    e.id_setor, 
    l.id_enfermaria, 
    l.numero AS leito_numero, 
    l.status AS status_leito,     
    p.nome_completo AS paciente_nome,     
    EXISTS (         
        SELECT 1 FROM alergias_pacientes ap         
        JOIN alergias a ON ap.id_alergia = a.id   -- Ajustado para o singular aqui
        WHERE ap.id_paciente = p.id AND a.gravidade = 'alta'     
    ) AS alerta_alergia_grave 
FROM leitos l 
JOIN enfermarias e ON l.id_enfermaria = e.id 
LEFT JOIN pacientes_enfermarias pe ON l.id = pe.id_leito 
LEFT JOIN pacientes p ON pe.id_paciente = p.id;

-- 2.2 View para Eventos Adversos Críticos
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

-- 2.3 View para a Fila de Admissões
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

-- 2.4 View para Altas Pendentes (Libertação de Leitos)
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



-- 3. Criação da tabela de Instituições
CREATE TABLE instituicoes (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(200) NOT NULL,
    cnpj VARCHAR(14) NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    PRIMARY KEY (id)
) ENGINE=InnoDB;


-- 1. Adicionar a relação na tabela 'setores'
ALTER TABLE setores
    ADD COLUMN id_instituicao INT NULL AFTER id,
    ADD CONSTRAINT fk_setores_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;

-- 2. Adicionar a relação na tabela 'medicos'
ALTER TABLE medicos
    ADD COLUMN id_instituicao INT NULL AFTER id,
    ADD CONSTRAINT fk_medicos_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES instituicoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;