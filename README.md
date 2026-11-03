# Projeto Continuum - Políticas de Segurança da Informação

> **Projeto acadêmico desenvolvido no curso de Sistemas de Informação - UMC | 6º Semestre**

## 1. Descrição
O **Projeto Continuum** é uma solução tecnológica estruturada para otimizar o processo de passagem de plantão entre profissionais de saúde. O principal objetivo da ferramenta é **mitigar falhas de comunicação na transição do cuidado clínico**, reduzindo assim a incidência de erros médicos. Trata-se de um estudo de produção tecnológica e pesquisa aplicada, sendo desenvolvido na disciplina de Políticas de Segurança da Informação. 

>🔎 A aplicação soluciona a falta de rastreabilidade e padronização na passagem de plantão, estabelecendo um canal de comunicação auditável para garantir a continuidade da assistência médica.

### 1.1 Requisitos Atendidos
A arquitetura da aplicação foi concebida a partir de um mapeamento minucioso dos fluxos críticos de comunicação hospitalar. O sistema atende aos seguintes requisitos funcionais:   
 - **Padronização de Dados Críticos:** A arquitetura impõe a obrigatoriedade de preenchimento de campos críticos para a transição do cuidado, como o registro de sinais vitais e de medicações pendentes.   
 - **Preservação do Histórico Clínico:** O sistema implementa rotinas de exclusão lógica, o que garante a preservação e a integridade do histórico clínico dos pacientes.   
 - **Gestão de Consentimento:** O software viabiliza um módulo específico focado na gestão de consentimento dos direitos do paciente.   
 - **Escalabilidade Ergonômica:** O projeto deixa como próximos passos a preparação para a expansão da prototipação para interfaces responsivas em dispositivos móveis, visando a adequação da ergonomia do software à rotina dos  - hospitais através de testes de usabilidade com as equipes médicas.

### 1.2 Políticas de Segurança da Informação e Conformidade
Devido à natureza crítica das informações hospitalares, o projeto foi desenvolvido com foco rigoroso em proteção de dados, integrando restrições legais e estruturais:   
 - **Adequação à LGPD:** O sistema foi projetado respeitando as restrições legais impostas pela Lei Geral de Proteção de Dados Pessoais (LGPD) para o tratamento de dados médicos.   
 - **Controle de Acesso (RBAC):** A aplicação protege informações clínicas sensíveis utilizando o controle de acesso baseado em papéis (RBAC), garantindo que apenas profissionais autorizados interajam com determinados dados.   
 - **Rastreabilidade e Logs Inalteráveis:** O sistema assegura total rastreabilidade das informações através de logs inalteráveis. Esses registros de auditoria contêm carimbo de tempo e a autoria das ações, identificando de forma clara o transmissor e o receptor do plantão.   
 - **Integridade dos Dados:** O software é integrado a um banco de dados relacional com restrições de integridade estruturais. Além disso, implementa módulos de entrada com validações lógicas rígidas de conformidade e preenchimento.

---

## Sumário
1. [Descrição](#1-descrição)
2. [Tecnologias Utilizadas](#2-tecnologias-utilizadas)
3. [Segurança e Conformidade](#3-segurança-e-conformidade)
4. [Estrutura do Projeto](#4-estrutura-do-projeto)
5. [Pré-requisitos](#5-pré-requisitos)
6. [Instruções de Instalação e Execução](#6-instruções-de-instalação-e-execução)
7. [Uso Educacional](#7-uso-educacional)
8. [Licença](#8-licença)
9. [Contribuidores](#9-contribuidores)

---

## 2. Tecnologias Utilizadas

O projeto está sendo desenvolvido utilizando o seguinte **stack tecnológico**:

| Categoria | Tecnologias |
|---|---|
| **Linguagens** | JavaScript, PHP e Node.js |
| **Marcação e Estilização** | HTML5 e CSS3 |
| **Bibliotecas/Frameworks** | React (JavaScript), Styled Components |
| **Build Tool** | Vite |
| **Qualidade de Código** | ESLint |
| **Integração** | API REST |
| **Banco de Dados** | MySQL |

---

## 3. Segurança e Conformidade
Com escopo voltado para a área da saúde e à disciplina de **Políticas de Segurança da Informação**, o projeto adota os seguintes princípios:
- **Proteção de Dados:** Diretrizes baseadas na LGPD para o tratamento de informações sensíveis de pacientes.
- **Controle de Acesso:** Implementação de **RBAC (Role-Based Access Control)** para garantir que apenas profissionais autorizados acessem os dados.
- **DevSecOps:** Implementação de Cloudflare, análise estática de código com TruffleHog/Trivy/CodeQL nas GitHub Actions, etc.

---

## 4. Estrutura do projeto
A aplicação está organizada principalemnte entre o cliente e o servidor:

```text
Continuum/
├── Client/
│   ├── src/
│   ├── public/
│   ├── ...
│   └── package.json
│
├── Server/
│   ├── api/
│   └── ...
│
└── README.md
```

---

## 5. Pré-requisitos
Antes de executar o projeto, certifique-se de possuir as seguintes ferramentas instaladas (as versões indicadas correspondem às utilizadas no desenvolvimento):

## Pré-requisitos

| Tecnologia | Versão |
|---|---:|
| **PHP** | v8.2 |
| **Node.js** | v22.20.0 |
| **Vite** | v8.3.4 |
| **MySQL** | v9.0 |
| **Docker Desktop** | v4.95.0 |

---


## 6. Instruções de Instalação e Execução

### 6.1 Clonando o repositório

Abra o seu terminal e execute:
   ```bash
   git clone https://github.com/LucasRSMorais/Projeto_Continuum.git
   ```

### 6.2 Configurando as variáveis de ambiente e segurança

Antes de iniciar a aplicação, é necessário configurar as credenciais do ambiente. 

> **Segurança:** As configurações de conexão com o banco de dados não devem conter senhas ou outras informações sensíveis diretamente no código.

1. Para executar o PHP diretamente no computador, copie `apps/Server/.env.example` para `apps/Server/.env` e informe as credenciais locais do banco.
2. Para Docker Compose, copie `apps/.env.example` para `apps/.env` e defina senhas aleatórias e exclusivas em `DB_ROOT_PASSWORD` e `DB_APP_PASSWORD`. O Compose usa o usuário de aplicação `DB_APP_USER` (não root).
3. Os arquivos `.env` são ignorados pelo Git. Nunca coloque credenciais reais nos arquivos de exemplo, no código-fonte ou em commits. Variáveis de ambiente de contêineres podem ser vistas por usuários com acesso ao Docker; em produção, prefira o gerenciador de segredos da hospedagem.
4. Atualmente, cadastros de profissionais são criados com a conta ativa, sem aprovação manual. A equipe ainda deve validar o registro profissional; até existir um fluxo administrativo de aprovação, considere essa limitação antes de disponibilizar o cadastro publicamente. O login também depende da configuração do serviço de e-mail usado pela verificação em duas etapas.

---

### 6.3 Executando o Servidor e Banco de Dados com Docker (Recomendado)
Para garantir que o projeto seja executado com as mesmas configurações em qualquer máquina, utilizamos o **Docker** para orquestrar o servidor PHP (Backend) e a base de dados MySQL.

#### Pré-requisitos do Docker
- **Docker Desktop:** Versão 4.95.0 ou superior (recomendado).
- **Sistema Operacional Windows:** É necessário ter o Windows Subsystem for Linux (WSL) ativado.

#### Instalação do Docker (Windows)
1. Caso ainda não tenha o WSL ativado, abra o **PowerShell como Administrador** e execute:
  ```bash
   wsl --install
   ```
> **Nota:** Após a instalação do WSL, é necessário reiniciar o computador.

2. Baixe e instale o [Docker Desktop](https://www.docker.com/products/docker-desktop/).
2. Durante a instalação, certifique-se de que a opção de utilizar o **WSL 2** está selecionada.
4. Após a instalação, abra o aplicativo Docker Desktop e aguarde que o Engine inicie (o ícone ficará verde).

#### Comandos de Execução
Todos os comandos do Docker devem ser executados no terminal, dentro da pasta raiz da aplicação onde se encontra o arquivo `docker-compose.yml` (pasta `apps`).

Antes de iniciar, crie `apps/.env` a partir do exemplo e configure as duas senhas. O Compose falha de forma explícita se elas não forem definidas. Não use senhas de exemplo em produção. Para priorizar um banco hospedado também no backend Docker, preencha `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` nesse arquivo; valores não vazios são usados antes do serviço local `db`. Deixe `DB_HOST` vazio para usar o MySQL local do Compose.

Se já existir um volume MySQL, alterar as variáveis do `.env` não troca as senhas gravadas no banco: faça backup e atualize as credenciais no próprio MySQL com uma conta administrativa antes de recriar os serviços. Não use `docker compose down -v`, pois essa opção apaga o volume e os dados.

#### Iniciar o ambiente (Backend e Base de Dados):

Inicia os contêineres em segundo plano. Na primeira execução, o Docker irá baixar as imagens e executar `database/init.sql` para criar e popular o banco `railway`. O backend acessa o banco pela rede interna Docker no host `db`; o cliente MySQL no próprio computador pode usar `localhost:3306`. A porta do MySQL está vinculada somente a `127.0.0.1` e não deve ser publicada em uma interface de rede pública.
```bash
docker compose up -d
```
Após a execução, a API estará disponível em `http://localhost:8000`. O dashboard exige autenticação também no ambiente local; não existe opção de desativar a autenticação no Compose. As portas publicadas são somente locais e podem ser alteradas por `API_PUBLISHED_PORT` e `DB_PUBLISHED_PORT` no `.env`.

#### Parar o ambiente temporariamente:
Para os serviços sem destruir os contêineres ou a rede interna.
```bash
docker compose stop
```

#### Encerrar o ambiente (Desligar):
Para os contêineres e remove a rede interna. Os dados do banco de dados são mantidos em segurança devido à configuração de volumes (`mysql_data`).
```bash
docker compose down
```

--- 

### 6.4 Configuração do Banco de Dados Manualmente (Alternativa)

Caso não deseje utilizar o Docker, é necessário possuir um servidor PHP e um servidor MySQL em funcionamento. 

**Ferramentas recomendadas:**

- XAMPP
- Laragon
- MySQL Server diretamente

Neste cenário, você deverá importar os scripts de criação do banco manualmente no seu SGBD e ajustar as credenciais no arquivo `.env` (ex: alterando `DB_HOST` para `localhost`). Além disso, o servidor PHP precisará ser iniciado conforme a ferramenta escolhida.

Use `apps/Server/.env.example` como modelo e configure `DB_HOST=localhost`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` para esse ambiente. Na hospedagem, configure as mesmas variáveis no gerenciador seguro da plataforma e defina `APP_ENV=production`; `DB_PORT` é opcional e assume `3306`. Em produção, a aplicação exige as credenciais hospedadas e não cai para `localhost` ou para o banco Docker. Configure também `CLIENT_URL` com a origem exata do frontend para permitir CORS com credenciais. O arquivo `apps/Server/.env` não é usado pelo backend no Docker Compose: nele os valores `DB_*` definidos em `apps/.env` têm prioridade sobre o host interno `db`; se não forem preenchidos, o backend usa o banco local do Compose.

---

### 6.5 Executando o Cliente (Frontend)
Com o backend e o banco de dados rodando, você pode iniciar a interface do usuário:

1. Abra um novo terminal e acesse a pasta executável do frontend:
```bash
cd Client
```
2. Instale as dependências (estes comandos instalarão as dependências especificadas no `package.json` e a ferramenta de construção):
```bash
npm install
npm install vite@latest
```
3. Inicie o servidor de desenvolvimento do frontend:
```bash
npm run dev
```
O Vite iniciará o servidor e informará no terminal o endereço local para acessar a aplicação (ex: `http://localhost:5173`).

---

## 7. Uso educacional
Este projeto possui finalidade **estritamente acadêmica e educacional**, desenvolvido no contexto do curso de **Sistemas de Informação da Universidade de Mogi das Cruzes (UMC)**.

---

## 8. Licença
### Apache 2.0

Este projeto está disponibilizado sob a **Apache License 2.0**.

----

## 9. Contribuidores
Projeto atualmente em desenvolvimento no **6º Semestre do curso de Sistemas de Informação**:

- **Lucas Roberto**
- **Leonardo Felix**

---

<div align="center">

**Projeto Continuum — 2026**

*Políticas de Segurança da Informação · Universidade de Mogi das Cruzes (UMC)*

</div>
