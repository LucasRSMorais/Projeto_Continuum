-- Usuário mestre de teste (médico + paciente)
-- Email: testercontinuum@gmail.com
-- Senha (ambas as contas): TesterContinuum@2026
-- Aplique este script no banco do projeto (ex.: railway).
-- Idempotente: não duplica se o e-mail já existir em cada tabela.
-- Compatível com bases que ainda não possuem a coluna pacientes.acesso_ativado
-- (migration 20261113_patient_terms_first_access.sql). Se ela existir, o usuário
-- é criado/atualizado como acesso ativado para login direto no ambiente de teste.

START TRANSACTION;

-- Conta profissional (acesso à área /Medico/*)
-- Cargo Staff para alto nível de permissão dentro do perfil profissional.
INSERT INTO medicos (
    id_instituicao,
    nome_completo,
    email,
    senha_hash,
    crm_numero,
    crm_uf,
    especialidade,
    cargo,
    status,
    autenticacao
)
SELECT
    1,
    'Usuário Mestre Tester',
    'testercontinuum@gmail.com',
    '$argon2id$v=19$m=65536,t=3,p=2$ZXEyWk9QOS5nTkdka0VZMg$LusmKgyCwSn3ipMWa5TkJQAHSWAeB9tjgTqVaK2ytVg',
    '99999',
    'SP',
    'Administração/Testes',
    'Staff',
    'ativo',
    1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM medicos WHERE email = 'testercontinuum@gmail.com'
);

-- Conta paciente (acesso à área /Paciente/*)
-- sexo/etnia em texto plano são aceitos pelo fallback de descriptografia do sistema.
INSERT INTO pacientes (
    nome_completo,
    email,
    cpf,
    data_nascimento,
    sexo,
    etnia,
    senha_hash,
    telefone,
    aceite_termo_uso
)
SELECT
    'Usuário Mestre Tester',
    'testercontinuum@gmail.com',
    '00000000999',
    '1990-01-01',
    'masculino',
    'nao_informar',
    '$argon2id$v=19$m=65536,t=3,p=2$OXl0SDdPUWRVNmZoZnhacw$LO6uOQIwmDYIaWRlHXOiW2SYWp70uxEfjXg6U0n3RTc',
    '11999999999',
    'sim'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM pacientes WHERE email = 'testercontinuum@gmail.com'
);

-- Se a coluna existir (base com fluxo de primeiro acesso/termos), garante ativação.
SET @database_name = DATABASE();
SET @ativar_acesso_sql = IF(
    (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = @database_name
          AND table_name = 'pacientes'
          AND column_name = 'acesso_ativado'
    ) = 1,
    'UPDATE pacientes SET acesso_ativado = 1 WHERE email = ''testercontinuum@gmail.com''',
    'SELECT 1'
);
PREPARE ativar_acesso_statement FROM @ativar_acesso_sql;
EXECUTE ativar_acesso_statement;
DEALLOCATE PREPARE ativar_acesso_statement;

COMMIT;

-- Logins:
-- Médico:   http://localhost:<porta-client>/  (ou rota de login médico) com testercontinuum@gmail.com / TesterContinuum@2026
-- Paciente: http://localhost:<porta-client>/Paciente/login com testercontinuum@gmail.com / TesterContinuum@2026
-- Observação: a autenticação médica e a do paciente são fluxos/sessões separados; faça login no portal correspondente à área desejada.
-- Recomendado: aplicar também apps/database/migrations/20261113_patient_terms_first_access.sql se sua base ainda não tiver as tabelas/colunas de termos e primeiro acesso.
