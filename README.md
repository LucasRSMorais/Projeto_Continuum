# Continuum/Politicas de Segurança da Informação

## Descrição
O projeto Continuum consiste no desenvolvimento de uma ferramenta destinada a otimizar o processo de passagem de plantão entre profissionais de saúde.

A iniciativa busca contribuir para a continuidade do cuidado com segurança, padronização e eficiência, permitindo que informações importantes sobre os pacientes sejam transmitidas de forma clara e assertiva entre as equipes de plantão.

O software está sendo desenvolvido para apresentação no SUMMIT UMC 2026, como parte das atividades da disciplina de Políticas de Segurança da Informação, e futuramente como projeto a ser apresentado à banca do PFC.

## Tecnologias utilizadas
O projeto está sendo desenvolvido utilizando as seguintes tecnologias:

    Linguagem de programação: JavaScript, PHP e Node.Js
    Linguagem de marcação e estilização: HTML5 e CSS3
    Bibliotecas: React (JavaScript), Styled Components (React)
    Build tool: Vite
    Linter: ESlint
    API: REST
    Banco de dados: MySQL e MongoDB

## Requisitos

### Pré-requisitos // Versão utilizada
Antes de executar o projeto, certifique-se de possuir as seguintes ferramentas instaladas:

    - HTML 5
    - PHP 8.5
    - Node.Js v22.20.0
    - npm v11.1.0
    - Vite v8.2.2
    - MySQL

  Observação: As versões apresentadas correspondem às utilizadas durante o desenvolvimento do projeto.

## Instalação
Após realizar o download de uma Release ou clonar o repositório, siga os passos abaixo.

  1. Acessar a pasta do cliente
    Abra o terminal dentro da pasta do projeto e acesse a pasta Client:
    
    cd Client

  2. Instalar as dependências
    Execute:
    
    npm install
    npm install vite@latest

    Estes comandos instalarão as dependências especificadas no package.json e a ferramenta de construção que transformará o código-fonte em um software pronto para uso.
  
  3. Executar o projeto
    Após a instalação das dependências, execute:
    
    npm run dev

    O Vite iniciará o servidor de desenvolvimento e informará no terminal o endereço utilizado para acessar a aplicação.


## Configuração do banco de dados
Para executar a aplicação corretamente, é necessário possuir um servidor MySQL em funcionamento.

O MySQL pode ser executado por meio de ferramentas como:
  - XAMPP
  - Laragon
  - MySQL Server diretamente

Após iniciar o servidor MySQL, é necessário configurar o banco de dados utilizado pela aplicação de acordo com as configurações presentes no projeto

    Importante: As configurações de conexão com o banco de dados não devem conter senhas ou outras informações sensíveis diretamente no código ou no repositório público. As presentes no momento são apenas para fins de testes.

## Estrutura do projeto

A aplicação está organizada principalemnte entre o cliente e o servidor:

  Continuum/
      - Client/
          - src/
          - public/
          - package.json
          - ...
      - Server/
          - api/
          - ...
      - README.md

A estrutura acima deve ser ajustada caso a organização real do projeto seja diferente.

## Licença

### Apache 2.0

Este projeto está disponibilizado sob a Apache License 2.0.

### Uso educacional

O projeto possui finalidade acadêmica e educacional, sendo desenvolvido no contexto do cusro de Sistemas de Informação.

## Contribuidores

Projeto atualmente em desenvolvimento por Lucas Roberto e Leonardo Felix, no 6º Semestre do curso Sistemas de Informação.
