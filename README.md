# Sistema de Controle de Estoque - Casa do Norte

## Descrição
Este sistema web foi desenvolvido para gerenciar o estoque de comidas e ingredientes de um restaurante de comidas típicas nordestinas, a "Casa do Norte", conforme o desafio proposto. O sistema permite o cadastro, consulta, edição e exclusão de comidas; registro de entradas e saídas do estoque; controle de estoque mínimo com destaque visual; e autenticação de usuários para acesso restrito.

## Estrutura do Projeto

```

/ (diretório raiz)
├── casanorte.sql         \# Script para criação e povoamento do banco de dados
├── README.md             \# Este arquivo
├── index.php             \# Página de login (ponto de entrada)
├── principal.php         \# Interface principal após login
├── cadastro\_comida.php  \# Gerenciamento de comidas (CRUD)
├── gestao\_estoque.php   \# Gestão de estoque e movimentações
├── autenticacao.php      \# Funções de login, logout e verificação de sessão
├── db.php                \# Configuração da conexão com o banco de dados
├── funcoes.php           \# Funções auxiliares gerais
├── header.php            \# Cabeçalho comum das páginas
├── footer.php            \# Rodapé comum das páginas
├── logout.php            \# Script para encerrar a sessão do usuário
└── style.css             \# Arquivo de estilos CSS

```

## Requisitos para Execução

- Servidor web com suporte a PHP 7 ou superior (Ex: XAMPP, WampServer)
- Banco de dados MySQL ou MariaDB
- Um navegador web moderno

## Configuração

1.  Crie um banco de dados no seu servidor MySQL/MariaDB.
2.  Importe o script `saep_db.sql` para o banco de dados criado.
3.  Ajuste as credenciais de conexão com o banco de dados no arquivo `db.php`.
4.  Coloque a pasta `sistema` no diretório do seu servidor web (Ex: `htdocs` no XAMPP).
5.  Acesse o sistema pelo seu navegador, apontando para o arquivo `index.php`.

## Usuários para Teste

A senha para todos os usuários abaixo é `123`.

| Usuário (login) | Nome Completo |
|-----------------|---------------|
| ig              | IgStation     |
| tio             | Tiozinho      |
| hair            | Long hair     |



