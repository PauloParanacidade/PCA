# Sistema Base de Autenticação com LDAP

Este projeto é um sistema base desenvolvido para o **Paranacidade**, com foco em autenticação via **LDAP** e gerenciamento de usuários. Ele serve como ponto de partida para futuros sistemas da organização que exigem controle de acesso e integração com o diretório corporativo.

O sistema foi desenvolvido com **Laravel 12**, utilizando o **Breeze** como starter kit de autenticação, e conta com integração nativa ao LDAP para autenticação de usuários.

### Tecnologias Utilizadas
- Laravel 12
- Breeze (Tailwind + Blade)
- LDAP (via [LdapRecord-Laravel](https://ldaprecord.com/docs/laravel/))
- PostgreSQL (ou outro banco de dados compatível com Laravel)
- Node.js (para gerenciamento de dependências frontend)
- Vite (para build e desenvolvimento frontend)
- Tailwind CSS (para estilização)

### Requisitos do Sistema
- PHP 8.2 ou superior
- Composer
- Node.js 18 ou superior
- PostgreSQL 12 ou superior
- Servidor LDAP configurado
- Extensões PHP necessárias:
  - PDO
  - OpenSSL
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON

### Configuração Inicial

Após clonar o projeto, siga os passos abaixo para configurar o ambiente:

```bash
# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# CRIAÇÃO DAS TABELAS NO BANCO DE DADOS
# Executar as migrations para criação das tabelas
php artisan migrate

# Popular a tabela de cargos/perfis
php artisan db:seed --class=RoleSeeder

# Compilar assets
npm run build
```

### Configuração do Ambiente

1. Configure o arquivo `.env` com as seguintes informações:
   - Dados de conexão com o banco de dados
   - Configurações do LDAP
   - Configurações de e-mail
   - Outras configurações específicas do ambiente

2. Certifique-se de que o diretório `storage` e seus subdiretórios têm permissões de escrita:
   ```bash
   chmod -R 775 storage
   ```

### Desenvolvimento

Para iniciar o ambiente de desenvolvimento:

```bash
# Iniciar servidor de desenvolvimento
php artisan serve

# Em outro terminal, iniciar o Vite
npm run dev
```

### Testes

O projeto utiliza PHPUnit para testes. Para executar os testes:

```bash
php artisan test
```
### Licença

Este projeto é proprietário e pertence ao Paranacidade. Todos os direitos reservados.