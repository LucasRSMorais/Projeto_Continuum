USE railway;

ALTER TABLE medicos
    ADD COLUMN especialidade VARCHAR(100) NULL AFTER crm_uf;

ALTER TABLE setores
    MODIFY COLUMN tipo ENUM(
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
    ) NOT NULL;

INSERT INTO setores (id_instituicao, nome, tipo, responsavel_plantao, class_risco)
SELECT 1, 'Enfermaria', 'Enfermaria', 7, 'baixo'
WHERE NOT EXISTS (
    SELECT 1 FROM setores WHERE nome = 'Enfermaria'
);

INSERT INTO enfermarias (id_setor, medico_responsavel, quantidade_leitos, leitos_disponiveis, status)
SELECT s.id, 7, 10, 10, 'ativo'
FROM setores s
WHERE s.nome = 'Enfermaria'
  AND NOT EXISTS (
      SELECT 1 FROM enfermarias e WHERE e.id_setor = s.id
  );

INSERT INTO leitos (id_enfermaria, numero, status)
SELECT e.id, CONCAT('ENF-7', LPAD(numbers.n, 2, '0')), 'disponivel'
FROM enfermarias e
JOIN setores s ON s.id = e.id_setor
CROSS JOIN (
    SELECT 1 AS n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) AS numbers
WHERE s.nome = 'Enfermaria'
  AND NOT EXISTS (
      SELECT 1 FROM leitos l WHERE l.id_enfermaria = e.id
  );
