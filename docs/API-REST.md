# API REST — Bolso Planejado (v1)

Base URL local: `http://localhost:8000` (ou a URL do `php artisan serve`). Todas as rotas abaixo usam o prefixo **`/api/v1`**.

- **Formato:** JSON (`Content-Type: application/json`, `Accept: application/json`).
- **IDs:** UUID (string) em recursos de domínio.
- **Autenticação:** [Laravel Sanctum](https://laravel.com/docs/sanctum) — token pessoal no header `Authorization: Bearer {token}`.

Documentação de produto / MVP: [MVP-PREVISIBILIDADE.md](./MVP-PREVISIBILIDADE.md).  
Panorama do que o backend já expõe: [BACKEND-STATUS.md](./BACKEND-STATUS.md).

---

## Frontend (SPA)

Interface **Vue 3** + **Vue Router** + **Pinia**, compilada com **Vite** e **Tailwind CSS v4**.

- **Entrada:** qualquer rota web (`/`, `/login`, `/contas`, …) renderiza [resources/views/app.blade.php](../resources/views/app.blade.php) e monta o app em `#app`.
- **API:** cliente Axios em `resources/js/api/http.js` com `baseURL: '/api/v1'` e token em `localStorage` (`divida_zero_token`).
- **Telas:** login, cadastro, início (resumo), previsão de caixa, contas, transações, recorrências.
- **Edição na listagem:** contas (linha vira formulário) e transações (painel expansível abaixo da linha com `PUT /transactions/{id}`).
- **UX:** toasts (Pinia), transições de página (`page`), gráfico de linha (Chart.js) na previsão e no dashboard.
- **Build:** `npm run build` → `public/build/`; desenvolvimento: `npm run dev` + `php artisan serve` (ou Laragon).

---

## Rotas públicas

| Método | Caminho | Descrição |
|--------|---------|-----------|
| GET | `/categories` | Lista categorias (seed) — **não exige token** |
| POST | `/register` | Cadastro: `name`, `email`, `password`, `password_confirmation` |
| POST | `/login` | `email`, `password` — retorna `user` + `token` |

---

## Rotas autenticadas (`auth:sanctum`)

| Método | Caminho | Descrição |
|--------|---------|-----------|
| POST | `/logout` | Revoga o token atual |
| GET | `/me` | Usuário logado (`data`: `id`, `name`, `email`) |
| GET | `/projection` | Previsão de saldo — query: `horizon_days` (1–365, padrão 30), `financial_account_id` (opcional) |
| REST | `/financial-accounts` | Contas / carteiras |
| REST | `/transactions` | Lançamentos — query em `index`: `financial_account_id`, `status`, `from`, `to`, `per_page` |
| REST | `/recurrence-series` | Recorrências mensais |

Convenções REST do `apiResource`: `GET/POST` coleção, `GET/PUT|PATCH/DELETE` item `{id}`.

### Enums

- **type** (transação / recorrência): `income` | `expense`
- **status** (transação): `completed` | `scheduled`

---

## Exemplos (PowerShell)

Substitua `BASE` e `TOKEN` conforme o ambiente.

### Registrar e guardar token

```powershell
$BASE = "http://127.0.0.1:8000/api/v1"
$r = Invoke-RestMethod -Uri "$BASE/register" -Method Post -Body (@{
  name = "Maria"
  email = "maria@example.com"
  password = "senha-segura-123"
  password_confirmation = "senha-segura-123"
} | ConvertTo-Json) -ContentType "application/json"
$TOKEN = $r.token
```

### Categorias (sem token)

```powershell
Invoke-RestMethod -Uri "$BASE/categories" -Headers @{ Accept = "application/json" }
```

### Criar conta e lançamento

```powershell
$h = @{ Authorization = "Bearer $TOKEN"; Accept = "application/json" }
$acc = Invoke-RestMethod -Uri "$BASE/financial-accounts" -Method Post -Headers $h -Body (@{
  name = "Conta principal"
  initial_balance = "1200.00"
} | ConvertTo-Json) -ContentType "application/json"
$accId = $acc.data.id

Invoke-RestMethod -Uri "$BASE/transactions" -Method Post -Headers $h -Body (@{
  financial_account_id = $accId
  type = "expense"
  amount = "150.00"
  occurred_on = "2026-04-05"
  status = "scheduled"
  description = "Conta de luz"
} | ConvertTo-Json) -ContentType "application/json"
```

### Projeção (30 dias, uma conta)

```powershell
Invoke-RestMethod -Uri "$BASE/projection?horizon_days=30&financial_account_id=$accId" -Headers $h
```

---

## Testes automatizados

```bash
php artisan test tests/Feature/Api
```

Arquivo: `tests/Feature/Api/V1/ApiSmokeTest.php`.
