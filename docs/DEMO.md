# Conta de demonstração

Após `php artisan migrate --seed` ou `php artisan db:seed`, existe um usuário pronto para testar o fluxo (previsão, contas, transações, recorrências).

## Credenciais

| Campo | Valor |
|--------|--------|
| **E-mail** | `demo@dividazero.local` |
| **Senha** | `demo1234` |

## O que vem no seed

- **2 contas:** “Nubank (conta corrente)” com saldo inicial R$ 1.850,00 e “Dinheiro (carteira)” com R$ 120,00.
- **Lançamentos passados** (realizados): mercado, transporte, freela, etc.
- **Lançamentos futuros** (agendados): luz, parcela de dívida, condomínio, compra mensal estimada.
- **3 recorrências mensais:** salário (dia 5), aluguel (dia 12), internet/streaming (dia 8).

O seeder é **reexecutável**: ao rodar de novo, remove lançamentos/recorrências/contas desse e-mail e recria (mantém o mesmo usuário).

## Login inicial (antes do demo)

O projeto **não** cria mais o usuário `test@example.com`. Tudo passa pela conta demo acima ou por cadastro próprio (`/cadastro`).
