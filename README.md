# Continuum/Politicas de Segurança da Informação.

## 1. Descrição
O projeto Continuum consiste no desenvolvimento de uma ferramenta destinada a otimizar o processo de passagem de plantão entre profissionais de saúde.

A iniciativa busca contribuir para a continuidade do cuidado com segurança, padronização e eficiência, permitindo que informações importantes sobre os pacientes sejam transmitidas de forma clara e assertiva entre as equipes de plantão.

O software está sendo desenvolvido para apresentação no SUMMIT UMC 2026, como parte das atividades da disciplina de Políticas de Segurança da Informação, e futuramente como projeto a ser apresentado à banca do PFC.

## Sumário

1. [Descrição](#descrição)
2. [Tecnologias utilizadas](#tecnologias)
3. [Pré-requisitos](#pré-requisitos)
4. [Instruções de Instalação e Excecução](#instalação)
5. [Configuração do Banco de Dados](#banco-de-dados)
6. [Estrutura do projeto](#estrutura)
7. [Licença](#licença)
8. [Uso](#uso)
9. [Contribuidores](#contribuidores)

## 2. Tecnologias utilizadas
O projeto está sendo desenvolvido utilizando as seguintes tecnologias:

    Linguagem de programação: JavaScript, PHP e Node.Js
    Linguagem de marcação e estilização: HTML5 e CSS3
    Bibliotecas: React (JavaScript), Styled Components (React)
    Build tool: Vite
    Linter: ESlint
    Integração: API REST
    Banco de dados: MySQL

## 3. Pré-requisitos
Antes de executar o projeto, certifique-se de possuir as seguintes ferramentas instaladas:

## Pré-requisitos

- HTML v5
- PHP v8.5
- Node.js v22.20.0
- npm v11.1.0
- Vite v8.2.2
- MySQL v9
  
  Observação: As versões apresentadas correspondem às utilizadas durante o desenvolvimento do projeto.

## 4. Instruções de Instalação e Excecução

### 4.1 Etapas 

1. Clone o repositório:
   ```bash
   git clone https://github.com/LucasRSMorais/Projeto_Continuum.git
   ```
2. Acesse a pasta do cliente
   Abra o terminal e utilize o comando abaixo para acessar à pasta executavél:
   ```bash
   cd Client
   ```
4. Instale as dependências
   Ainda na pasta client, execute os seguintes comandos:
   ```bash
   npm install
   ```
   ```bash
   npm install vite@latest
   ```
   Estes comandos instalarão as dependências especificadas no package.json e a ferramenta de construção que transformará o código-fonte em um software pronto para uso.

### 4.2 Execução
   Para executar projeto, abra terminal e execute:
   ```bash
   npm run dev
   ```
   O Vite iniciará o servidor de desenvolvimento e informará no terminal o endereço utilizado para acessar a aplicação.

## 5. Configuração do banco de dados
Para executar a aplicação corretamente, é necessário possuir um servidor MySQL em funcionamento.

O MySQL pode ser executado por meio de ferramentas como:
  - XAMPP
  - Laragon
  - MySQL Server diretamente

Após iniciar o servidor MySQL, é necessário configurar o banco de dados utilizado pela aplicação de acordo com as configurações presentes no projeto

    Importante: As configurações de conexão com o banco de dados não devem conter senhas ou outras informações sensíveis diretamente no código ou no repositório público. As presentes no momento são apenas para fins de testes.

## 6. Estrutura do projeto
A aplicação está organizada principalemnte entre o cliente e o servidor:

```text
Continuum/
├── Client/
│   ├── src/
│   ├── public/
│   └── package.json
│
├── Server/
│   ├── api/
│   └── ...
│
└── README.md

## 7. Licença

### Apache 2.0

Este projeto está disponibilizado sob a Apache License 2.0.

## 8. Uso educacional

O projeto possui finalidade acadêmica e educacional, sendo desenvolvido no contexto do cusro de Sistemas de Informação.

## 9. Contribuidores

Projeto atualmente em desenvolvimento por Lucas Roberto e Leonardo Felix, no 6º Semestre do curso Sistemas de Informação.
