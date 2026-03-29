<?php

namespace Database\Seeders;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\FinancialAccount;
use App\Models\Plan;
use App\Models\RecurrenceSeries;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use App\Services\EntitlementService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Usuário de demonstração + contas, lançamentos e recorrências para explorar o app.
     *
     * Credenciais (ver também docs/DEMO.md):
     * - E-mail: demo@dividazero.local
     * - Senha:  demo1234
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'demo@dividazero.local'],
            [
                'name' => 'Marina (demonstração)',
                'password' => Hash::make('demo1234'),
            ]
        );

        app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);

        $monthly = Plan::query()->where('slug', 'premium-monthly')->first();
        if ($monthly !== null) {
            Subscription::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'external_id' => 'demo-seed-sub',
                ],
                [
                    'plan_id' => $monthly->id,
                    'status' => SubscriptionStatus::Active,
                    'current_period_start' => now()->subMonth(),
                    'current_period_end' => now()->addMonth(),
                    'gateway' => 'fake',
                ]
            );
            app(EntitlementService::class)->syncUserPlanSnapshot($user);
        }

        $cat = static fn (string $slug) => Category::query()
            ->where('user_id', $user->id)
            ->where('slug', $slug)
            ->firstOrFail();

        // Remove dados demo anteriores do mesmo usuário (re-seed idempotente)
        RecurrenceSeries::query()->where('user_id', $user->id)->delete();
        Transaction::query()->where('user_id', $user->id)->delete();
        FinancialAccount::query()->where('user_id', $user->id)->delete();

        $nubank = FinancialAccount::query()->create([
            'user_id' => $user->id,
            'name' => 'Nubank (conta corrente)',
            'initial_balance' => '1850.00',
            'currency' => 'BRL',
        ]);

        $dinheiro = FinancialAccount::query()->create([
            'user_id' => $user->id,
            'name' => 'Dinheiro (carteira)',
            'initial_balance' => '120.00',
            'currency' => 'BRL',
        ]);

        $today = Carbon::today();

        $tx = [
            // Passado — já realizados (montam o saldo até “hoje”)
            [$nubank->id, 'alimentacao', TransactionType::Expense, '287.45', $today->copy()->subDays(18), TransactionStatus::Completed, 'Mercado + feira'],
            [$nubank->id, 'transporte', TransactionType::Expense, '45.00', $today->copy()->subDays(11), TransactionStatus::Completed, 'Uber / ônibus'],
            [$nubank->id, 'rendimentos', TransactionType::Income, '350.00', $today->copy()->subDays(6), TransactionStatus::Completed, 'Freela (PIX)'],
            [$nubank->id, 'alimentacao', TransactionType::Expense, '62.90', $today->copy()->subDays(4), TransactionStatus::Completed, 'Padaria'],
            [$dinheiro->id, 'lazer', TransactionType::Expense, '40.00', $today->copy()->subDays(2), TransactionStatus::Completed, 'Cinema'],
            // Futuro — agendados (aparecem na projeção)
            [$nubank->id, 'moradia', TransactionType::Expense, '220.00', $today->copy()->addDays(4), TransactionStatus::Scheduled, 'Conta de luz (vencimento)'],
            [$nubank->id, 'dividas-parcelas', TransactionType::Expense, '189.90', $today->copy()->addDays(9), TransactionStatus::Scheduled, 'Parcela cartão (acordo)'],
            [$nubank->id, 'moradia', TransactionType::Expense, '95.00', $today->copy()->addDays(16), TransactionStatus::Scheduled, 'Condomínio / água'],
            [$nubank->id, 'alimentacao', TransactionType::Expense, '400.00', $today->copy()->addDays(22), TransactionStatus::Scheduled, 'Compra mensal (estimativa)'],
        ];

        foreach ($tx as $row) {
            [$accountId, $slug, $type, $amount, $date, $status, $desc] = $row;
            Transaction::query()->create([
                'user_id' => $user->id,
                'financial_account_id' => $accountId,
                'category_id' => $cat($slug)->id,
                'type' => $type,
                'amount' => $amount,
                'occurred_on' => $date->toDateString(),
                'status' => $status,
                'description' => $desc,
            ]);
        }

        // Recorrências mensais (entram na projeção)
        RecurrenceSeries::query()->create([
            'user_id' => $user->id,
            'financial_account_id' => $nubank->id,
            'category_id' => $cat('rendimentos')->id,
            'type' => TransactionType::Income,
            'amount' => '4200.00',
            'day_of_month' => 5,
            'start_on' => $today->copy()->startOfMonth()->toDateString(),
            'end_on' => null,
            'max_occurrences' => null,
            'is_active' => true,
            'description' => 'Salário (CLT)',
        ]);

        RecurrenceSeries::query()->create([
            'user_id' => $user->id,
            'financial_account_id' => $nubank->id,
            'category_id' => $cat('moradia')->id,
            'type' => TransactionType::Expense,
            'amount' => '980.00',
            'day_of_month' => 12,
            'start_on' => $today->copy()->startOfMonth()->toDateString(),
            'end_on' => null,
            'max_occurrences' => null,
            'is_active' => true,
            'description' => 'Aluguel',
        ]);

        RecurrenceSeries::query()->create([
            'user_id' => $user->id,
            'financial_account_id' => $nubank->id,
            'category_id' => $cat('assinaturas')->id,
            'type' => TransactionType::Expense,
            'amount' => '49.90',
            'day_of_month' => 8,
            'start_on' => $today->copy()->startOfMonth()->toDateString(),
            'end_on' => null,
            'max_occurrences' => null,
            'is_active' => true,
            'description' => 'Internet + streaming',
        ]);
    }
}
