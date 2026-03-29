# Billing e planos

## Visão geral

- **Planos** (`free`, `premium-monthly`, `premium-lifetime`) e **matriz de recursos** (`features` + `plan_feature`) definem o que cada plano pode fazer (recomendações, investimentos, PDF, limite de contas, etc.).
- **Entitlements** são calculados em [`App\Services\EntitlementService`](../app/Services/EntitlementService.php) a partir de:
  1. Assinatura ativa (`subscriptions`);
  2. Compra válida (`purchases`, `expires_at` nulo ou futuro);
  3. Snapshot em `users.plan_id` (plano gratuito padrão).
- **API**: `GET /api/v1/me` inclui `billing.plan`, `billing.entitlements` e `billing.premium_expires_at`.
- **Checkout simulado**: com `BILLING_DRIVER=fake`, `POST /api/v1/billing/checkout` com `{ "plan_slug": "premium-monthly" }` grava assinatura ou compra conforme o modo do plano, sem HTTP externo.

## Variáveis de ambiente

| Variável | Descrição |
|----------|-----------|
| `BILLING_DRIVER` | `fake` (padrão) ou futuro identificador do gateway real. |
| `BILLING_WEBHOOK_SECRET` | Segredo para validar webhooks quando um provedor for configurado. |

## Painel administrativo (Filament)

- URL: `/admin` (sessão web; não usa token Sanctum da API).
- Acesso: usuários com `is_admin = true` ([`User::canAccessPanel`](../app/Models/User.php)).
- Seed de exemplo: [`AdminUserSeeder`](../database/seeders/AdminUserSeeder.php) — e-mail `admin@dividazero.local`, senha `admin1234` (altere em produção).
- **Idioma:** o locale é definido como `pt_BR` ao servir o Filament ([`AppServiceProvider`](../app/Providers/AppServiceProvider.php)), usando as traduções oficiais do pacote.
- **Performance:** o painel usa modo SPA (navegação sem recarregar a página inteira), busca global desativada, carregamento adiado das tabelas (`deferLoading`), eager loading de relações nas listagens e paginação padrão de 25 itens. Em produção, execute após deploy: `php artisan filament:cache-components`.

Recursos: Usuários, Planos, Features, Assinaturas, Compras.

## Integrar um gateway real

1. Implementar [`App\Contracts\PaymentGateway`](../app/Contracts/PaymentGateway.php) (criar checkout, processar webhook).
2. Registrar o binding em [`AppServiceProvider`](../app/Providers/AppServiceProvider.php) conforme `config('billing.driver')`.
3. Implementar `POST /api/v1/billing/webhook` ou rota dedicada do provedor, validando assinatura com `BILLING_WEBHOOK_SECRET`.
4. Manter `EntitlementService::syncUserPlanSnapshot()` após eventos de pagamento para alinhar `users.plan_id` e `premium_expires_at`.

## Frontend

- Rota `/app/assinatura`: lista planos (`GET /api/v1/billing/plans`) e checkout simulado.
- Rotas com `meta.requiresFeature` no Vue redirecionam para assinatura quando o recurso está bloqueado no plano gratuito.
