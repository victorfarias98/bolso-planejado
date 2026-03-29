# Deploy em hospedagem compartilhada (FTP)

Este guia descreve como publicar o **Bolso Planejado** em um plano **compartilhado** típico (cPanel ou similar), enviando arquivos por **FTP/SFTP**. Ajuste nomes de pastas conforme sua hospedagem.

## O que você precisa na hospedagem

- **PHP 8.3** ou superior (o `composer.json` exige `^8.3`).
- Extensões PHP comuns do Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`.
- **Composer** *no servidor* **ou** você gera a pasta `vendor` no seu PC e envia pronta (segunda opção é mais comum em FTP puro).
- **Node.js** *no servidor* **ou** você roda `npm run build` no PC e envia a pasta `public/build`.
- Banco: **MySQL/MariaDB** recomendado (painel costuma criar usuário e banco).
- Acesso **FTP ou SFTP** (SFTP é preferível).

> **Evite** expor `.env`, `storage/logs` e pastas privadas com permissão de leitura pública. O document root do site deve apontar só para `public/`.

---

## Visão da estrutura no servidor

Opção recomendada (similar ao Laravel Forge / “above webroot”):

```
/home/seuusuario/
  bolso-planejado/          ← projeto completo (fora do public_html)
    app/
    bootstrap/
    config/
    database/
    public/             ← APENAS esta pasta vira o site
    resources/
    routes/
    storage/
    vendor/
    .env
  public_html/          ← symlink ou cópia do conteúdo de public/
```

Muitas hospedagens só permitem `public_html` como raiz do site. Nesse caso:

1. Envie o projeto **inteiro** para uma pasta **fora** de `public_html` (ex.: `~/bolso-planejado`).
2. Mova **só o conteúdo** de `public/` para `public_html`, **ajustando** `index.php` para apontar para os caminhos corretos do Laravel (veja seção abaixo).

### Ajuste do `index.php` se a aplicação ficar fora de `public_html`

Se o Laravel ficar em `~/bolso-planejado` e o site em `~/public_html`, edite `public_html/index.php` para os `require` apontarem para:

```php
require __DIR__.'/../bolso-planejado/vendor/autoload.php';
$app = require_once __DIR__.'/../bolso-planejado/bootstrap/app.php';
```

(Confirme o número de `../` conforme a pasta real.)

Alternativa: manter apenas `public/` como raiz e subir o restante uma pasta acima — o importante é **não** deixar `storage/` e `.env` acessíveis pela URL.

---

## Passo a passo sugerido

### 1. Preparar no seu computador (antes do FTP)

Na cópia do projeto:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:clear
```

Isso gera:

- `vendor/` completo
- `public/build/` com assets do Vite

> Se **não** puder rodar `composer` no servidor, o `vendor/` precisa ir no pacote FTP (pode ser grande).

### 2. Criar `.env` de produção

- Copie `.env.example` para `.env`.
- Defina:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seudominio.com.br

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```

- Gere a chave:

```bash
php artisan key:generate
```

Copie o valor de `APP_KEY` para o `.env` do servidor.

**Session e cache:** em muitas hospedagens, `file` para sessão funciona bem. Se usar `SESSION_DRIVER=database` ou `CACHE_STORE=database`, rode as migrações.

### 3. Enviar arquivos por FTP/SFTP

Envie **tudo** que o Laravel precisa, **exceto** o que pode ignorar:

Sugestão de **não enviar** (opcional, reduz tamanho):

- `node_modules/`
- `tests/`
- `.git/` (se não for usar Git no servidor)

**Deve existir no servidor:**

- `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`, `public/` (com `public/build`), `.env`, `artisan`, `composer.json` (útil para futuras atualizações).

### 4. Permissões

No servidor (SSH ou gerenciador de arquivos, se permitir):

- `storage/` e `bootstrap/cache/` graváveis pelo usuário do PHP (ex.: `chmod -R 775`, dono `usuario:webserver` — varia por host).

### 5. Banco de dados

No painel (cPanel): crie banco + usuário e associe permissões **ALL** ao banco.

Depois, **se tiver SSH**:

```bash
cd ~/bolso-planejado
php artisan migrate --force
```

Sem SSH: alguns painéis têm “Terminal” ou agendamento de cron; como último recurso, rode migrações localmente apontando para o MySQL remoto (liberando seu IP no firewall da hospedagem), ou peça suporte para executar `php artisan migrate --force`.

### 6. Document root

O domínio deve apontar **apenas** para a pasta `public` do Laravel (ou para `public_html` já ajustada).

### 7. Testar

- Abra `https://seudominio.com.br` → landing.
- Abra `https://seudominio.com.br/app` → SPA; teste login e uma chamada API.

Erros comuns:

- **500:** permissões em `storage/` / `bootstrap/cache/`.
- **Página branca / mix não encontrado:** faltou `public/build` ou `npm run build`.
- **419 no login:** `APP_URL` errado, HTTP/HTTPS misturado, ou cookie de sessão; limpe cache de config no servidor: `php artisan config:clear` (via SSH).

---

## `.htaccess` (Apache)

O Laravel já traz `public/.htaccess`. Garanta que o Apache tenha **mod_rewrite** ativo e que `AllowOverride` permita `.htaccess` na pasta pública.

---

## Atualizações futuras via FTP

1. Local: `composer install --no-dev`, `npm run build`.
2. Envie arquivos alterados + `vendor` se dependências mudarem + `public/build`.
3. No servidor (SSH): `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.

Sem SSH, limpe caches apagando arquivos em `bootstrap/cache/*.php` **com cuidado** ou use um script permitido pelo host.

---

## Segurança

- Nunca commite `.env` real.
- Use **HTTPS**.
- Mantenha PHP e dependências atualizados quando possível.

---

## Resumo rápido

| Etapa | Ação |
|--------|------|
| Build | `composer install --no-dev` + `npm run build` |
| Env | `.env` produção + `APP_KEY` |
| Upload | Projeto completo + `vendor` + `public/build` |
| Web root | Só `public/` |
| Permissões | `storage/`, `bootstrap/cache/` |
| DB | Criar MySQL + `migrate --force` |
