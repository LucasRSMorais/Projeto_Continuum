# Continuum/Politicas de Segurança da Informação

## Descrição
A projeto Continuum consiste no desenvolvimento de uma ferramenta para otimizar o processo de passagem de plantão entre profissionais de saúde. A iniciativa busca garantir a continuidade do cuidado com segurança, padronização e eficiência, assegurando que informações críticas sobre os pacientes sejam transmitidas de forma clara e assertiva entree as equipes de plantão.

Este software tem como finalidade ser apresentado ao SUMMIT UMC 2026, à disciplina de Politicas de Segurança da Informação e futuramente à banca do PFC.

## Está sendo desenvolvido com as seguintes ferramentas:
    Linguagem de programação: PHP, JavaScript
    Linguagem de marcação e estilização: HTML e CSS
    FrameWorks: React (JavaScript)
      Liner: ESlint
    API: REST
    Banco de dados: MySQL

## Instrução de instalação

### Pré-requisitos // Versão utilizada
    - HTML 5
    - PHP 8.5
    - JavaScript
    - Xampp Control Panel v3.3.0
    - Vite v8.2.1
    - Node.Js v22.20.0
    - nmp 11.1.0
    - MySQL

### Etapas

'''bash
  npm install
'''

## Instrução de uso
1- Abrir XAMPP, ativar APACHE e MySql

### Visualizar telas em desenvolvimento
Para abrir as rotas privadas sem autenticar durante o desenvolvimento, crie
`apps/Client/.env.local` com:

```env
VITE_DEV_AUTH_BYPASS=true
```

Reinicie o servidor Vite após alterar essa variável. O bypass só é aceito quando
o Vite está em modo de desenvolvimento (`npm run dev`) e não tem efeito no build
de produção. Sem essa variável, as rotas privadas continuam exigindo autenticação.

## Licença
### Apache 2.0
### Uso educacional

## Contribuidores
Projeto atualmente em desenvolvimento por Lucas Roberto e Leonardo Felix, no 6º Semestre do curso Sistemas de Informação.
