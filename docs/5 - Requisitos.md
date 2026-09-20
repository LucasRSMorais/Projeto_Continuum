# Documentação Técnica do Sistema de Log

## 1. Introdução

O sistema de log do projeto Continuum foi implementado como mecanismo de auditoria, rastreio operacional e suporte à investigação de incidentes de segurança. Ele registra eventos sensíveis do backend, especialmente no fluxo de autenticação, verificação em duas etapas, recuperação de senha, cadastro de usuários e pacientes, revogação de consentimento e ações críticas relacionadas a segurança da informação.

A implementação atual foi ajustada para seguir uma arquitetura mais consistente com o contexto da aplicação em produção e com a proposta do modelo de banco de dados: os eventos são persistidos em tabelas do MySQL, com estrutura adequada à auditoria e à retenção de dados. A abordagem foi desenhada para evitar perda de histórico em ambientes voláteis, como contêineres do Railway, em comparação com a gravação local em arquivo.

Este documento descreve a estrutura implementada, os componentes envolvidos, a lógica de auditoria e os mecanismos de alerta e retenção, além de reservar espaços específicos para evidências de funcionamento que deverão ser anexadas posteriormente.

## 2. Objetivo

O principal objetivo do sistema de log é garantir:

- rastreabilidade das ações executadas pelo sistema e pelos usuários;
- apoio à auditoria interna e à investigação de incidentes;
- diagnóstico de erros e falhas operacionais;
- identificação de tentativas indevidas de acesso;
- registro histórico de eventos críticos, como falhas de autenticação, 2FA e alterações de credenciais;
- retenção controlada de registros de segurança e auditoria.

Além disso, o módulo oferece base para monitoramento operacional, análise forense e cumprimento mínimo de requisitos de segurança da informação.

## 3. Escopo

Os eventos registrados cobrem ações do backend PHP, com foco em:

- login de usuários e pacientes;
- autenticação em duas etapas (2FA);
- recuperação de senha;
- cadastro de usuários e pacientes;
- revogação de consentimento;
- validação de sessão e expiração de código;
- falhas de autenticação e eventos de risco;
- alertas de segurança gerados por múltiplas falhas em sequência.

A solução atual é orientada para auditoria e segurança, e não para uma plataforma de observabilidade completa, mas responde bem ao contexto acadêmico e funcional do projeto.

## 4. Arquitetura Implementada

A arquitetura atual do sistema de log foi definida em torno de persistência em banco de dados, com suporte a alertas e política de retenção automática.

### 4.1 Registro central de auditoria

A rotina principal de registro está em [apps/Server/api/registrarLog.php](apps/Server/api/registrarLog.php). Ela recebe os seguintes dados:

- usuário associado ao evento;
- ação executada;
- descrição do evento;
- tabela afetada;
- endereço IP;
- user-agent;
- identificador da requisição;
- severidade do evento.

A função executa um INSERT parametrizado na tabela de auditoria, evitando concatenação direta de strings e reduzindo riscos de injeção SQL.

### 4.2 Tabela principal: logs_auditoria

A tabela principal foi implementada em [apps/Server/sql/logs_auditoria.sql](apps/Server/sql/logs_auditoria.sql). Ela registra todos os eventos relevantes do sistema e possui os seguintes campos principais:

- id
- usuario_id
- acao_realizada
- tabela_afetada
- data_hora
- descricao
- ip_address
- user_agent
- request_id
- severity

A estrutura foi pensada para permitir:

- consulta por usuário;
- consulta por ação;
- consulta por faixa temporal;
- classificação por gravidade do evento;
- rastreio do IP e do navegador associado à atividade.

Os índices criados incluem busca por usuário, ação, data e severidade.

### 4.3 Tabela de alertas: logs_alertas

Também em [apps/Server/sql/logs_auditoria.sql](apps/Server/sql/logs_auditoria.sql), existe a tabela logs_alertas, que recebe eventos críticos ou suspeitos. Ela registra:

- usuario_id
- acao_realizada
- severidade
- mensagem
- ip_address
- created_at
- status

Essa tabela foi criada para separar eventos de auditoria básica dos alertas de segurança, permitindo resposta mais rápida a ocorrências como múltiplas falhas de login ou tentativas repetidas de 2FA.

### 4.4 Trigger de alerta para falhas repetidas

A solução implementada inclui um trigger chamado trg_alerta_login_falha. Esse trigger é disparado após cada inserção em logs_auditoria e verifica se a mesma ação crítica ocorreu pelo menos três vezes em 15 minutos para o mesmo usuário.

Quando isso acontece, o sistema insere um registro em logs_alertas com severidade high. Esse mecanismo ajuda a detectar tentativas de brute force, ações suspeitas e padrões de risco antes que o incidente se agrave.

### 4.5 Política de retenção dos logs

A retenção foi implementada por eventos agendados do MySQL em [apps/Server/sql/logs_auditoria.sql](apps/Server/sql/logs_auditoria.sql):

- ev_remover_logs_antigos
- ev_remover_alertas_antigas

A política adotada é:

- auditoria: remoção automática após 90 dias;
- alertas: remoção automática após 180 dias.

Essa estratégia reduz o acúmulo de dados, evita armazenamento indefinido e melhora a governança dos dados no ambiente do Railway.

### 4.6 Logger de segurança e mascaramento

O arquivo [apps/Server/config/logger.php](apps/Server/config/logger.php) implementa a classe SecurityLogger, que foi pensada para reduzir exposição de dados sensíveis. Ela inclui métodos para:

- mascarar e-mail;
- redigir texto sensível;
- escrever eventos em tabela de segurança;
- registrar ocorrências de segurança com severidade apropriada.

Embora a implementação principal da aplicação tenha migrado para o modelo de logs em banco, esse arquivo representa a base de preparação para padronização de eventos de segurança e minimização de dados.

## 5. Fluxo de uso do sistema

Os eventos de auditoria são disparados em pontos estratégicos do backend. Os principais fluxos cobertos pela implementação atual são:

- tentativa de login com usuário inexistente;
- login com senha inválida;
- início do processo de 2FA;
- falha na validação do código 2FA;
- sucesso da validação do código 2FA;
- login concluído com sucesso;
- alteração de senha;
- cadastro de usuários;
- cadastro de pacientes;
- revogação de consentimento;
- início de recuperação de senha;
- falhas de validação associadas a autenticação e senha.

Esses registros permitem rastrear não apenas o que foi executado, mas também em que contexto e por qual usuário a ação ocorreu.

## 6. Aplicação prática no backend

### 6.1 Login

No fluxo de login, o sistema registra eventos de falha quando:

- o e-mail não existe;
- a senha é inválida;
- o processo de 2FA falha;
- o usuário é bloqueado por múltiplas tentativas.

Em caso de sucesso, o sistema registra o início do processo de verificação em duas etapas e, posteriormente, grava o evento de login concluído.

### 6.2 2FA

No fluxo de verificação em duas etapas, o backend registra:

- falha de validação do código;
- sucesso de validação do código;
- confirmação do login final.

Esse comportamento é importante para diferenciar tentativa suspeita de autenticação bem-sucedida.

### 6.3 Recuperação de senha

Na funcionalidade de recuperação ou atualização de senha, são gravados eventos como:

- início da recuperação;
- falha de validação da nova senha;
- alteração de senha com sucesso.

Esse tipo de evento é especialmente relevante para auditoria de ações sensíveis.

### 6.4 Cadastro e consentimento

Os cadastros de usuários e pacientes também geram registros de auditoria, assim como a revogação de consentimento. Esses eventos são relevantes para rastreabilidade, documentação e exercício de controle interno.

## 7. Segurança e proteção de dados

A implementação atual considera princípios básicos de segurança da informação:

- uso de consultas parametrizadas em SQL;
- tratamento de dados de sessão para contexto de autenticação;
- gravação de IP e user-agent para rastreabilidade;
- severidade explícita por evento;
- minimização de dados em mensagens de log;
- separação entre registro de auditoria e alertas de segurança;
- retenção programada para reduzir acúmulo de dados sensíveis.

Além disso, os eventos sensíveis não expõem diretamente a senha em texto puro e o sistema adota mecanismos de hash e validação segura para senhas e códigos temporários.

## 8. Limitações e pontos de melhoria

A implementação atual já oferece uma base funcional, mas ainda há oportunidades de melhoria:

1. Consolidar a padronização de nomes de ações
   - algumas rotas ainda usam variações de nomenclatura e podem ser revisadas para manter uma taxonomia única.

2. Avaliar centralização de log de erros de infraestrutura
   - o sistema atual é orientado a auditoria de segurança e ainda pode receber integração com logs de aplicação mais completos.

3. Definir nível de retenção em produção
   - a retenção atual é apropriada para protótipo e projeto acadêmico, porém deve ser revisada conforme regra de negócio e conformidade.

4. Ampliação de governança
   - recomenda-se registrar responsável, origem da solicitação e critério de classificação dos eventos em ambientes mais exigentes.

5. Monitoramento operacional
   - a solução atual é adequada para auditoria, mas pode evoluir para alertas e dashboards mais robustos.

## 9. Evidências de funcionamento

Nesta seção, os dados de prova devem ser registrados para comprovar que o sistema está funcionando conforme a arquitetura implementada.

### 9.1 Evidência de criação da estrutura de auditoria

- Local reservado para captura da tabela logs_auditoria em execução.
- Exemplo de referência esperada: [inserir link, screenshot, export do banco ou print do MySQL Workbench].

### 9.2 Evidência de alertas de segurança

- Local reservado para captura dos registros em logs_alertas após tentativas repetidas.
- Exemplo de referência esperada: [inserir link, evidência do banco ou print do trigger disparando].

### 9.3 Evidência de retenção automática

- Local reservado para registro do evento agendado e da política de exclusão.
- Exemplo de referência esperada: [inserir print do SHOW EVENTS, evidência de cronograma ou log da execução].

### 9.4 Evidência de eventos de login e 2FA

- Local reservado para imagens ou prints das entradas em logs_auditoria relacionadas a LOGIN_FALHA, LOGIN_2FA, 2FA_SUCESSO e LOGIN_SUCESSO.
- Exemplo de referência esperada: [inserir link do banco, export, print ou evidência do endpoint em execução].

### 9.5 Evidência final de funcionamento

- Local reservado para relatório final de validação do sistema.
- Exemplo de referência esperada: [inserir link do arquivo de evidência, vídeo, print final ou relatório de testes].

## 10. Considerações finais

A implementação de logs do projeto Continuum está alinhada com o objetivo de manter rastreabilidade e auditoria das ações mais sensíveis do sistema. A solução adotada combina persistência em banco, alertas por trigger e retenção automatizada por eventos do MySQL, criando uma base mais robusta do que logs locais em arquivo para ambientes de execução em container.

A estrutura atual atende ao contexto do projeto, especialmente no que diz respeito a segurança, auditoria e governança mínima de registros. Ainda assim, a solução pode evoluir para um modelo mais sofisticado de monitoramento, observabilidade e conformidade, conforme o sistema cresça e passe por exigências mais rigorosas de segurança operacional.

## 11. Referências do projeto

Os principais módulos relacionados à implementação do sistema de log podem ser consultados em:

- [apps/Server/api/registrarLog.php](apps/Server/api/registrarLog.php)
- [apps/Server/config/logger.php](apps/Server/config/logger.php)
- [apps/Server/sql/logs_auditoria.sql](apps/Server/sql/logs_auditoria.sql)
- [apps/Server/api/login.php](apps/Server/api/login.php)
- [apps/Server/api/verify-2fa.php](apps/Server/api/verify-2fa.php)
- [apps/Server/api/verificar.php](apps/Server/api/verificar.php)
- [apps/Server/api/verificar2FA_paciente.php](apps/Server/api/verificar2FA_paciente.php)
- [apps/Server/api/senha.php](apps/Server/api/senha.php)
- [apps/Server/api/register.php](apps/Server/api/register.php)
- [apps/Server/api/register_paciente.php](apps/Server/api/register_paciente.php)
- [apps/Server/api/revoga_conta.php](apps/Server/api/revoga_conta.php)