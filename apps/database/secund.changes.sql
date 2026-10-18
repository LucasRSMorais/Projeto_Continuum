USE railway;

-- ============================================================================
-- 1. LIMPEZA COMPLETA DO BANCO DE DADOS
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE fila_admissoes;
TRUNCATE TABLE logs_alertas;
TRUNCATE TABLE logs_auditoria;
TRUNCATE TABLE pacientes_enfermarias;
TRUNCATE TABLE medicacoes_eventos;
TRUNCATE TABLE eventos_assinaturas;
TRUNCATE TABLE eventos_adversos;
TRUNCATE TABLE alergias_pacientes;
TRUNCATE TABLE alergias;
TRUNCATE TABLE enderecos;
TRUNCATE TABLE pacientes;
TRUNCATE TABLE leitos;
TRUNCATE TABLE enfermarias;
TRUNCATE TABLE setores;
TRUNCATE TABLE medicos;
TRUNCATE TABLE instituicoes;
TRUNCATE TABLE medicacoes;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 2. ABASTECIMENTO (SEED)
-- ============================================================================
SET @continuum_usuario_id = 1;
SET @continuum_ip_address = '127.0.0.1';

-- ----------------------------------------------------------------------------
-- 2.1. INSTITUIÇÃO (Deve ser a primeira para fornecer o ID 1)
-- ----------------------------------------------------------------------------
INSERT INTO instituicoes (nome, cnpj, status) VALUES
('Hospital Central São Lucas', '12345678000100', 'ativo');

-- ----------------------------------------------------------------------------
-- 2.2. MÉDICOS (Atualizado com id_instituicao = 1)
-- ----------------------------------------------------------------------------
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

-- ----------------------------------------------------------------------------
-- 2.3. SETORES (Atualizado com id_instituicao = 1)
-- ----------------------------------------------------------------------------
INSERT INTO setores (id_instituicao, nome, tipo, responsavel_plantao, class_risco) VALUES
(1, 'Setor Clínica Geral', 'Clínica Geral', 1, 'baixo'),
(1, 'Setor Cirúrgico', 'Cirúrgica', 2, 'medio'),
(1, 'Setor Obstétrico', 'Obstétrica e Perinatal', 3, 'medio'),
(1, 'Setor de Cardiologia', 'Cardiologia', 4, 'alto'),
(1, 'Setor de Neurologia', 'Neurologia', 5, 'alto'),
(1, 'Setor de Isolamento', 'Isolamento', 6, 'alto');

-- ----------------------------------------------------------------------------
-- 2.4. ENFERMARIAS
-- ----------------------------------------------------------------------------
INSERT INTO enfermarias (id_setor, medico_responsavel, quantidade_leitos, leitos_disponiveis, status) VALUES
(1, 1, 20, 20, 'ativo'), -- Clínica Geral (20 leitos)
(2, 2, 20, 20, 'ativo'), -- Cirúrgica (20 leitos)
(3, 3, 10, 10, 'ativo'), -- Maternidade (10 leitos)
(4, 4, 10, 10, 'ativo'), -- UTI Cardio (10 leitos)
(5, 5, 10, 10, 'ativo'), -- UTI Neuro (10 leitos)
(6, 6, 5, 5, 'ativo');   -- Isolamento/Infecto (5 leitos)

-- ----------------------------------------------------------------------------
-- 2.5. LEITOS (75 Leitos no total para um hospital médio)
-- ----------------------------------------------------------------------------
-- Inserindo 20 leitos na Clínica Geral
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(1, 'CG-101', 'disponivel'), (1, 'CG-102', 'disponivel'), (1, 'CG-103', 'disponivel'), (1, 'CG-104', 'disponivel'), (1, 'CG-105', 'disponivel'),
(1, 'CG-106', 'disponivel'), (1, 'CG-107', 'disponivel'), (1, 'CG-108', 'disponivel'), (1, 'CG-109', 'disponivel'), (1, 'CG-110', 'disponivel'),
(1, 'CG-111', 'disponivel'), (1, 'CG-112', 'disponivel'), (1, 'CG-113', 'disponivel'), (1, 'CG-114', 'disponivel'), (1, 'CG-115', 'disponivel'),
(1, 'CG-116', 'disponivel'), (1, 'CG-117', 'disponivel'), (1, 'CG-118', 'disponivel'), (1, 'CG-119', 'disponivel'), (1, 'CG-120', 'disponivel');

-- Inserindo 20 leitos na Cirúrgica
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(2, 'CC-201', 'disponivel'), (2, 'CC-202', 'disponivel'), (2, 'CC-203', 'disponivel'), (2, 'CC-204', 'disponivel'), (2, 'CC-205', 'disponivel'),
(2, 'CC-206', 'disponivel'), (2, 'CC-207', 'disponivel'), (2, 'CC-208', 'disponivel'), (2, 'CC-209', 'disponivel'), (2, 'CC-210', 'disponivel'),
(2, 'CC-211', 'disponivel'), (2, 'CC-212', 'disponivel'), (2, 'CC-213', 'disponivel'), (2, 'CC-214', 'disponivel'), (2, 'CC-215', 'disponivel'),
(2, 'CC-216', 'disponivel'), (2, 'CC-217', 'disponivel'), (2, 'CC-218', 'disponivel'), (2, 'CC-219', 'disponivel'), (2, 'CC-220', 'disponivel');

-- Inserindo 10 leitos na Obstétrica
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(3, 'OB-301', 'disponivel'), (3, 'OB-302', 'disponivel'), (3, 'OB-303', 'disponivel'), (3, 'OB-304', 'disponivel'), (3, 'OB-305', 'disponivel'),
(3, 'OB-306', 'disponivel'), (3, 'OB-307', 'disponivel'), (3, 'OB-308', 'disponivel'), (3, 'OB-309', 'disponivel'), (3, 'OB-310', 'disponivel');

-- Inserindo 10 leitos na UTI Cardiológica
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(4, 'UC-401', 'disponivel'), (4, 'UC-402', 'disponivel'), (4, 'UC-403', 'disponivel'), (4, 'UC-404', 'disponivel'), (4, 'UC-405', 'disponivel'),
(4, 'UC-406', 'disponivel'), (4, 'UC-407', 'disponivel'), (4, 'UC-408', 'disponivel'), (4, 'UC-409', 'disponivel'), (4, 'UC-410', 'disponivel');

-- Inserindo 10 leitos na UTI Neurológica
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(5, 'UN-501', 'disponivel'), (5, 'UN-502', 'disponivel'), (5, 'UN-503', 'disponivel'), (5, 'UN-504', 'disponivel'), (5, 'UN-505', 'disponivel'),
(5, 'UN-506', 'disponivel'), (5, 'UN-507', 'disponivel'), (5, 'UN-508', 'disponivel'), (5, 'UN-509', 'disponivel'), (5, 'UN-510', 'disponivel');

-- Inserindo 5 leitos no Isolamento
INSERT INTO leitos (id_enfermaria, numero, status) VALUES
(6, 'IS-601', 'disponivel'), (6, 'IS-602', 'disponivel'), (6, 'IS-603', 'disponivel'), (6, 'IS-604', 'disponivel'), (6, 'IS-605', 'disponivel');

-- ----------------------------------------------------------------------------
-- 2.6. PACIENTES E ENDEREÇOS
-- ----------------------------------------------------------------------------
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

-- ----------------------------------------------------------------------------
-- 2.7. ALERGIAS
-- ----------------------------------------------------------------------------
ALTER TABLE alergias 
MODIFY tipo VARCHAR(150) NOT NULL;

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

-- ----------------------------------------------------------------------------
-- 2.8. MEDICAÇÕES
-- ----------------------------------------------------------------------------
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

-- ----------------------------------------------------------------------------
-- 2.9. FILA DE ADMISSÕES
-- ----------------------------------------------------------------------------
INSERT INTO fila_admissoes (id_paciente, id_enfermaria, origem, gravidade, status, data_solicitacao) VALUES
(46, 1, 'pronto_socorro', 'media', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 2 HOUR)),
(47, 2, 'ambulatorio', 'baixa', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 4 HOUR)),
(48, 4, 'centro_cirurgico', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 MINUTE)),
(49, 5, 'pronto_socorro', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR)),
(50, 6, 'transferencia_externa', 'alta', 'aguardando', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 HOUR));

-- ----------------------------------------------------------------------------
-- 2.10. PACIENTE-ENFERMARIA
-- ----------------------------------------------------------------------------
-- 1. Permite que uma admissão ocorra sem um evento adverso associado
ALTER TABLE pacientes_enfermarias MODIFY id_evento INT NULL;

-- 2. Atualiza os gatilhos (triggers) de validação para aceitar id_evento nulo 
-- e corrige a verificação do status dos leitos.
DELIMITER $$

DROP TRIGGER IF EXISTS trg_pacientes_enfermarias_bi_validacao$$
CREATE TRIGGER trg_pacientes_enfermarias_bi_validacao
BEFORE INSERT ON pacientes_enfermarias
FOR EACH ROW
BEGIN
    -- Correção: verifica se o leito está 'disponivel' ou 'ocupado', e não 'ativo'
    IF NOT EXISTS (
        SELECT 1 FROM leitos l 
        WHERE l.id = NEW.id_leito 
          AND l.id_enfermaria = NEW.id_enfermaria 
          AND l.status IN ('disponivel', 'ocupado')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou nao esta disponivel.';
    END IF;

    -- Correção: Só valida o evento adverso se ele realmente for informado
    IF NEW.id_evento IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM eventos_adversos ea 
            WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
        END IF;
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
        SELECT 1 FROM leitos l 
        WHERE l.id = NEW.id_leito 
          AND l.id_enfermaria = NEW.id_enfermaria 
          AND l.status IN ('disponivel', 'ocupado')
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O leito informado nao pertence a enfermaria ou nao esta disponivel.';
    END IF;

    IF NEW.id_evento IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM eventos_adversos ea 
            WHERE ea.id = NEW.id_evento AND ea.id_paciente = NEW.id_paciente
        ) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'O evento informado nao pertence ao paciente.';
        END IF;
    END IF;

    IF NEW.movimentacao_leito IS NULL OR TRIM(NEW.movimentacao_leito) = '' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Movimentacao do leito deve ser informada.';
    END IF;
END$$

DELIMITER ;

-- 3. Inserção dos pacientes nos leitos
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
(6, 44, NULL, 71, 'Admissão Tuberculose'), (6, 45, NULL, 72, 'Admissão KPC');

UPDATE leitos SET status = 'ocupado' WHERE id IN (
  1,2,3,4,5,6,7,8,9,10,11,12,13,14,15, 
  21,22,23,24,25,26,27,28,29,30,
  41,42,43,44,45,
  51,52,53,54,55,
  61,62,63,
  71,72
);

-- ----------------------------------------------------------------------------
-- 2.11. EVENTOS ADVERSOS (100 Registros)
-- ----------------------------------------------------------------------------
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

-- Inserindo os restantes 50 eventos para completar os 100
INSERT INTO eventos_adversos (id_paciente, id_medico, tipo_evento, grau_dano, descricao_fato, data_evento, status_investigacao, id_assinatura)
SELECT id_paciente, id_medico, tipo_evento, grau_dano, CONCAT('Reincidência: ', descricao_fato), DATE_SUB(data_evento, INTERVAL 15 DAY), 'concluido', NULL
FROM eventos_adversos LIMIT 50;

-- ----------------------------------------------------------------------------
-- 2.12. ASSINATURAS E MEDICAÇÕES-EVENTOS
-- ----------------------------------------------------------------------------
INSERT INTO eventos_assinaturas (id_evento, id_paciente, nome_evento, tipo, finalidade, data_aceite, status) VALUES 
(1, 1, 'Termo de Alta do Leito 101', 'termo_alta', 'Alta hospitalar a pedido.', CURRENT_TIMESTAMP, 'ativo'), 
(3, 3, 'Termo de Alta do Leito 103', 'termo_alta', 'Alta médica por melhora.', CURRENT_TIMESTAMP, 'ativo'), 
(5, 5, 'Termo de Alta do Leito 105', 'termo_alta', 'Transferência de cuidado.', CURRENT_TIMESTAMP, 'ativo'), 
(10, 10, 'Termo de Consentimento para Cirurgia', 'autorizacao_cirurgia', 'Amputação de hálux.', CURRENT_TIMESTAMP, 'ativo');

-- Garante a vinculação aos primeiros 5 eventos que realmente existem no seu banco
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
-- ----------------------------------------------------------------------------
-- 2.13. LOGS DE ALERTAS
-- ----------------------------------------------------------------------------
INSERT INTO logs_alertas (id_usuario, acao_realizada, severidade, mensagem, ip_address, criado_em, status) VALUES
(1, 'SISTEMA_MONITORIZACAO', 'CRITICAL', 'Parada Cardiorrespiratória detectada no Leito UTI-401.', '10.0.0.5', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(2, 'LABORATORIO', 'HIGH', 'Potássio crítico (7.2 mEq/L) no Leito CG-112.', '10.0.0.12', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(3, 'FARMACIA', 'MEDIUM', 'Estoque de Fentanil abaixo da margem de segurança na UTI.', '10.0.0.8', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open'),
(4, 'SEGURANCA_TI', 'CRITICAL', 'Tentativa de acesso não autorizado aos prontuários.', '192.168.1.100', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %H:%i:%s'), 'open');