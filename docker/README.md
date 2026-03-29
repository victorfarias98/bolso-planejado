# Docker — Bolso Planejado

Arquivos nesta pasta:

| Arquivo | Função |
|---------|--------|
| `Dockerfile` | Imagem PHP 8.3-FPM + build Vite + Composer (multi-stage) |
| `docker-compose.yml` | MySQL 8, PHP-FPM (`app`) e Nginx |
| `nginx/default.conf` | Raiz em `public/`, FastCGI para `app:9000` |
| `docker-entrypoint.sh` | `APP_KEY` opcional, `migrate --force` com retry |
| `php/opcache.ini` | OpCache |
| `env.example` | Variáveis sugeridas (copie para `.env` na raiz se quiser) |

Na **raiz do repositório** existe `.dockerignore` para builds menores.

## Requisitos

- Docker Engine + Docker Compose v2
- Porta **8080** livre (ou defina `HTTP_PORT`)

## Subir o ambiente

```bash
cd docker
docker compose up -d --build
```

- Site: **http://localhost:8080**
- MySQL na máquina host: **localhost:3307** (usuário/senha padrão no `docker-compose.yml`)

### Primeira vez com bind mount

O `docker-compose` monta o código da raiz do projeto em `/var/www/html`. Se no host **não** existir `vendor/` ou `public/build/`, instale localmente:

```bash
cd ..
composer install
npm ci
npm run build
```

Ou, dentro do container `app`:

```bash
docker compose exec app composer install
docker compose exec app npm ci
docker compose exec app npm run build
```

(Exige Node no container — a imagem final não inclui Node; o mais simples é `composer` + `npm` no host.)

### `APP_KEY`

Se não definir `APP_KEY` no `environment` do serviço `app`, o entrypoint gera uma chave temporária (adequado para testes). Em produção, defina `APP_KEY` fixa no compose ou em `.env` carregado pelo host.

## Parar e remover

```bash
cd docker
docker compose down
```

Volumes nomeados (dados do MySQL) podem ser removidos com `docker compose down -v` (apaga o banco).

## Produção (sem bind mount)

Para usar **só a imagem** (sem montar o código do host), remova as seções `volumes` de `app` e `nginx` no `docker-compose` e publique a imagem em um registry. Nesse caso o `Dockerfile` já copia `vendor` e `public/build` para dentro da imagem.

## Healthcheck do MySQL

O healthcheck usa senha de root padrão `rootsecret`. Se alterar `MYSQL_ROOT_PASSWORD`, ajuste também o comando `healthcheck` do serviço `mysql` para usar a mesma senha.

## Troubleshooting

- **502 Bad Gateway:** verifique se `app` está rodando (`docker compose ps`) e logs `docker compose logs app`.
- **Erro de permissão em `storage/`:** no host, `chmod -R ug+rwX storage bootstrap/cache`.
- **Mix / assets 404:** rode `npm run build` e confira `public/build`.
