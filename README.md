## Bolso Planejado

Organização financeira pessoal com ênfase em previsibilidade de caixa (saldo dia a dia), registro de transações, contas/carteiras, categorias, dívidas e rotinas leves que ajudam a tomar decisões com clareza.

### Visão geral
- **Frontend**: Vue 3 (SPA) + Vue Router + Pinia, build com Vite e Tailwind CSS v4.
- **Backend**: Laravel 13, API REST em `/api/v1` com autenticação via Laravel Sanctum.
- **Landing**: página pública em Blade servida em `/`.
- **Diferencial**: projeção de saldo por dia e por conta, com “marco de risco” quando o saldo projetado fica negativo.

### Stack técnica
- PHP 8.3+, Laravel 13
- Node 20+/22+ para build (Vite)
- Banco: SQLite (dev) ou MySQL/PostgreSQL (prod)
- Docker (opcional) com Nginx + PHP-FPM + MySQL

### Estrutura (alto nível)
- `app/`, `routes/`, `database/` — Laravel (API, serviços e migrations)
- `resources/js/` — SPA Vue (layouts, views, stores)
- `resources/views/` — Blade (`landing.blade.php`, `app.blade.php`)
- `docker/` — Dockerfile, docker-compose e Nginx
- `docs/` — guias de deploy e documentação de produto/API

## Como rodar em desenvolvimento

### Opção A) Windows + Laragon (ou PHP embutido) — recomendado
1) Instale dependências PHP e JS:
```bash
composer install
npm ci
npm run dev
```
2) Copie `.env.example` para `.env` e gere a chave:
```bash
php artisan key:generate
```
3) Banco de desenvolvimento (padrão SQLite):
- No `.env`, deixe `DB_CONNECTION=sqlite` e crie o arquivo:
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```
4) Rode as migrações (e seeders se desejar):
```bash
php artisan migrate
# opcional (ex.: categorias iniciais / usuários demo, conforme seeders do projeto)
php artisan db:seed
```
5) Sirva a aplicação:
- Se usar Laragon: aponte o DocumentRoot para `public/` (ou simplesmente adicionar o projeto e acessar o domínio local configurado).
- PHP embutido (apenas para dev):
```bash
php artisan serve
```
6) Acesse:
- SPA: `http://localhost:8000/app` (ou o host/porta do Laragon)
- Landing: `http://localhost:8000/`

### Opção B) Docker Compose
Pré-requisitos: Docker Engine + Docker Compose v2.
1) Na pasta `docker/`:
```bash
docker compose up -d --build
```
2) Endereços:
- Site: `http://localhost:8080`
- MySQL no host: `localhost:3307` (credenciais padrão no compose)
3) Observações:
- O compose monta o código do host. Se não houver `vendor/` nem `public/build/`, rode localmente:
```bash
composer install
npm ci
npm run build
```
- Containers são nomeados como `bolso-planejado-app`, `bolso-planejado-mysql` e `bolso-planejado-nginx`.
Mais detalhes: `docker/README.md`.

## Variáveis de ambiente
Exemplo mínimo para desenvolvimento:
```env
APP_NAME="Bolso Planejado"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Dev com SQLite
DB_CONNECTION=sqlite
# (quando usar MySQL)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=bolso_planejado
# DB_USERNAME=bolso_planejado
# DB_PASSWORD=secret

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```
Importante em produção:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` com HTTPS.
- Definir `APP_KEY` (use `php artisan key:generate`).
- Ajustar `SESSION_DRIVER`/`CACHE_STORE` conforme o ambiente.

## Comandos úteis
```bash
# Backend
php artisan migrate
php artisan db:seed
php artisan tinker
php artisan test

# Frontend
npm run dev
npm run build
```

## API e Frontend
- Documentação da API: `docs/API-REST.md`
- Status do backend: `docs/BACKEND-STATUS.md`
- SPA: montada por `resources/views/app.blade.php` e inicializada em `resources/js/`

## Deploy
- Hospedagem compartilhada (FTP): `docs/DEPLOY-HOSPEDAGEM-COMPARTILHADA-FTP.md`
- Cloud com tier gratuito: `docs/DEPLOY-CLOUD-GRATUITA.md`
- Produção via Docker: ver `docker/README.md` e `docker/Dockerfile`

## Documentação de produto
- MVP focado em previsibilidade: `docs/MVP-PREVISIBILIDADE.md`
- Visão de produto e módulos: `docs/PRODUTO-DIVIDA-ZERO.md` (documento histórico com o escopo; referências ao nome antigo já estão sendo atualizadas gradualmente)

## Seeders e conta demo
- Consulte `docs/DEMO.md` para fluxo/credenciais de demonstração (e-mails de domínio local podem estar como `@dividazero.local` por compatibilidade — se quiser, atualizamos para o novo slug).
- Seeders principais: veja `database/seeders/`.

## Convenções e qualidade
- PSR padrão (Laravel), formatação do front guiada por Tailwind e lint de projeto.
- Evite comentários triviais; prefira nomes claros e funções curtas.

## Contribuição
- Issues e PRs são bem-vindos.
- Abra PRs pequenos, com descrição do problema, solução proposta e passos de teste.

## Licença
Este projeto segue a licença MIT (ver `LICENSE`, se presente).
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
