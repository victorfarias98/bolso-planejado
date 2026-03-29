# Deploy em cloud com tier gratuito (ou quase)

Este projeto é **Laravel 13** + **Vue 3** + **Vite**. O backend expõe API em `/api` e o frontend SPA em `/app`; a landing é Blade em `/`.

> **Importante:** tiers “grátis” mudam com frequência e costumam ter **limites de CPU, sono do serviço, ou crédito trial**. Sempre confira o site do provedor antes de publicar em produção.

## Pré-requisitos do projeto

- **PHP 8.3+**
- **Composer**
- **Node.js** (para build do Vite em deploy)
- **Extensões PHP** usuais do Laravel (openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath, etc.)

Banco de dados:

- Desenvolvimento padrão no `.env.example` usa **SQLite** (`DB_CONNECTION=sqlite`).
- Em cloud, **SQLite em disco efêmero** (contêiner que reinicia) **não é confiável** para dados persistentes. Para produção, use **PostgreSQL ou MySQL** oferecidos pelo provedor (muitos têm plano gratuito limitado).

## Checklist antes do primeiro deploy

1. Copiar `.env.example` para `.env` no ambiente remoto.
2. `APP_ENV=production`, `APP_DEBUG=false`.
3. `APP_URL` com **HTTPS** e domínio final (ex.: `https://seu-app.onrender.com`).
4. `php artisan key:generate` (ou definir `APP_KEY` gerada localmente).
5. Banco: criar database e usuário; preencher `DB_*` no `.env`.
6. Rodar migrações: `php artisan migrate --force`.
7. Build de front: `npm ci && npm run build` (gera `public/build`).
8. Otimizar Laravel: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (quando estiver estável).

## Opções de provedores (referência)

| Provedor | Observação típica |
|----------|---------------------|
| **Render** | Freemium; serviços web “dormem” após inatividade no plano gratuito; bom para demo. |
| **Railway** | Crédito/trial; verificar limites atuais. |
| **Fly.io** | Free allowance; costuma exigir Dockerfile ou config mais manual. |
| **Oracle Cloud “Always Free”** | VM gratuita, mas setup mais trabalhoso (servidor próprio). |

Abaixo um fluxo **genérico** que se aplica à maioria (build + variáveis + migrate).

---

## Exemplo de fluxo: Render (ilustrativo)

> Os nomes exatos de menus mudam; use como roteiro.

### 1. Repositório

- Envie o código para **GitHub/GitLab/Bitbucket** (repositório privado ou público).

### 2. Novo Web Service

- Tipo: **Web Service** conectado ao repositório.
- **Runtime:** escolha **PHP** ou **Docker** (se você adicionar um `Dockerfile` ao projeto).
- **Root directory:** raiz do repositório (onde está o `composer.json`).

### 3. Comandos de build (exemplo)

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Se o provedor não tiver Node no mesmo build, faça o build do front **na sua máquina** ou em CI e commite apenas `public/build` (não é o ideal, mas funciona em cenários limitados).

### 4. Comando de start (exemplo)

Depende da imagem:

- Servidor PHP embutido (apenas para testes):

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

- Em produção real, o ideal é **Nginx/Apache** apontando `DocumentRoot` para a pasta `public/` (muitos painéis de PaaS fazem isso com buildpack).

### 5. Variáveis de ambiente

No painel do provedor, configure pelo menos:

- `APP_KEY` (base64)
- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SESSION_DRIVER` (ex.: `database` após migrar)
- `QUEUE_CONNECTION` (se usar filas; em free tier muitas vezes `sync` é mais simples)

### 6. Migrações

Execute no deploy (hook “post-deploy” ou job one-off):

```bash
php artisan migrate --force
```

### 7. Sanctum / SPA

O frontend chama a API (axios). Garanta:

- Mesmo domínio ou CORS configurado se API e front estiverem em origens diferentes.
- Cookies CSRF: em produção com subdomínios diferentes, pode ser necessário ajustar `SESSION_DOMAIN` e Sanctum — teste login após o deploy.

---

## SQLite só para teste rápido

Se insistir em SQLite em um PaaS:

- Confirme se o disco **persiste** entre reinícios.
- Caso contrário, use PostgreSQL/MySQL do provedor.

Comando típico (após garantir arquivo gravável):

```bash
touch database/database.sqlite
php artisan migrate --force
```

---

## Domínio e HTTPS

- Configure DNS (CNAME/A) conforme o provedor.
- Ative certificado TLS no painel (quase sempre automático).

---

## O que monitorar após o deploy

- Logs de aplicação (`storage/logs`).
- Erro 500: permissões em `storage/` e `bootstrap/cache/`.
- Assets 404: rodar `npm run build` e conferir `public/build/manifest.json`.
- API 419: sessão/CSRF (SPA + Sanctum).

---

## Limitações comuns do “free tier”

- Serviço pode **hibernar** → primeira requisição lenta.
- Sem SLA; não use para dados críticos sem backup.
- Banco gratuito com limite de conexões/tamanho.

Para produção séria, avalie plano pago no mesmo provedor ou VPS.
