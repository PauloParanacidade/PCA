

### 📄 `deploy-guide.md`

````markdown
# 🚀 Guia de Deploy - Projeto PCA

Este guia descreve os passos para realizar o deploy do sistema **PCA** em ambiente de produção, incluindo o primeiro deploy e atualizações futuras.

---

## 📁 Acesse o diretório do projeto

```bash
cd /var/www/html/PCA
````

---

## 🆕 Se for o **primeiro deploy**:

1. Instale as dependências PHP:

```bash
composer install
```

2. Configure o repositório remoto (caso tenha clonado via HTTPS):

```bash
git remote set-url origin git@github.com:PauloParanacidade/PCA.git
```

3. Configure o `.env`:

```bash
cp .env.example .env
nano .env
```

Preencha com os dados reais de produção, por exemplo:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pca.prcidade.br

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=pca_producao
DB_USERNAME=usuario
DB_PASSWORD=***senha_segura

# Configurar SMTP, S3, Redis etc., se aplicável
```

---

## 🔄 Atualizar o código com o GitHub

Sempre execute:

```bash
git pull origin main
```

Isso irá:

* Baixar a última versão do código hospedado no GitHub.
* Atualizar os arquivos do sistema no servidor.

Se **houve alterações no `composer.json` localmente**, em seguida, rode:

```bash
composer install
```

**⚠️ Nunca execute `composer update` diretamente em produção.**

---

## ⚙️ Torne o script de deploy executável (apenas uma vez)

```bash
chmod +x deploy.sh
```

---

## 🚀 Rode o script de deploy

```bash
./deploy.sh
```

---

## 🔁 Para próximos deploys:

Basta repetir:

```bash
./deploy.sh
```

---

> Mantenha esse guia versionado no repositório para referência rápida da equipe de desenvolvimento ou manutenção.

```
