# Backend — o que já está pronto (Bolso Planejado)

Documento de referência rápida do que está **implementado e em uso** no repositório. Detalhes de contratos HTTP em [API-REST.md](./API-REST.md).

---

## Stack

- **Laravel 13**, PHP 8.3+
- **SQLite** (desenvolvimento; configurável via `.env`)
- **Laravel Sanctum** — tokens pessoais (`Bearer`) para a API
- **UUID** como chave primária nos modelos de domínio (e `users`)

---

## Autenticação

| Item | Status |
|------|--------|
| Registro (`POST /api/v1/register`) | Pronto |
| Login (`POST /api/v1/login`) | Pronto |
| Logout (`POST /api/v1/logout`) | Pronto |
| Perfil (`GET /api/v1/me`) | Pronto |
| Resposta `user` em login/registro | Objeto plano (`id`, `name`, `email`) via `UserResource::resolve()` |

---

## Domínio financeiro (API v1)

| Recurso | Rotas REST | UUID | Observação |
|---------|------------|------|------------|
| **Categorias** | `GET /categories` | Sim | **Público** (sem token); populadas pelo `CategorySeeder` |
| **Contas** | `financial-accounts` | Sim | `name`, `initial_balance`, `currency` (padrão BRL) |
| **Transações** | `transactions` | Sim | `income` / `expense`; `completed` / `scheduled`; filtros em `index` |
| **Recorrências** | `recurrence-series` | Sim | Mensal por `day_of_month`; `is_active` |
| **Projeção** | `GET /projection` | — | `horizon_days`, `financial_account_id` opcional; motor em `ProjectionService` |

---

## Arquitetura de código

| Camada | Local |
|--------|--------|
| **Controllers** | `App\Http\Controllers\Api\V1\*` |
| **Form requests** | `App\Http\Requests\Api\V1\*` |
| **Resources (JSON)** | `App\Http\Resources\Api\V1\*` |
| **Services** | `App\Services\*` (inclui `ProjectionService`) |
| **Repositories** | `App\Repositories\*` + `Contracts\` |
| **Models** | `App\Models\*` com `HasUuids` onde aplicável |
| **Enums** | `TransactionType`, `TransactionStatus` |

Bindings repository → interface em `AppServiceProvider`.

---

## Banco de dados (migrations)

- `users` (UUID)
- `sessions` (`user_id` UUID FK)
- `personal_access_tokens` (`uuidMorphs` para usuário UUID)
- `categories`, `financial_accounts`, `transactions`, `recurrence_series`

---

## Testes

- `tests/Feature/Api/V1/ApiSmokeTest.php` — fluxos principais da API  
- Comando: `php artisan test`

---

## O que ainda **não** está no backend (ideias futuras)

- Open Finance / importação de extrato
- Módulo completo de **dívidas** e **acordos** (hoje: categorias + lançamentos cobrem o MVP)
- What-if, buffer de segurança, e-mail/push para marco de risco
- Políticas Laravel (`Policy`) por recurso — hoje a autorização é por `user_id` nas queries / validação `exists` com escopo

---

## Frontend

SPA **Vue 3** + **Vue Router** + **Pinia** servida pela mesma origem Laravel; consome esta API. Ver secção em [API-REST.md](./API-REST.md#frontend-spa).
