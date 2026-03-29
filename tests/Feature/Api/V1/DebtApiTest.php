<?php

use App\Models\FinancialAccount;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use Laravel\Sanctum\Sanctum;

test('dívidas retornam lista e totais', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/debts')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => [
                'totals' => ['principal_total', 'balance_total', 'count'],
            ],
        ])
        ->assertJsonPath('meta.totals.count', 0);
});

test('criar dívida com acordo e recorrência opcional', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);

    $account = FinancialAccount::query()->create([
        'user_id' => $user->id,
        'name' => 'Conta teste',
        'initial_balance' => '1000.00',
        'currency' => 'BRL',
    ]);

    $firstDue = now()->addMonthNoOverflow()->day(15)->toDateString();

    $response = $this->postJson('/api/v1/debts', [
        'title' => 'Cartão acordo',
        'creditor' => 'Banco X',
        'debt_type' => 'card',
        'principal_amount' => '5000.00',
        'sync_recurrence' => true,
        'financial_account_id' => $account->id,
        'agreement_formalized_on' => now()->toDateString(),
        'agreement_first_due_date' => $firstDue,
        'agreement_installment_count' => 12,
        'agreement_installment_amount' => '200.00',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Cartão acordo')
        ->assertJsonPath('data.has_recurrence', true)
        ->assertJsonPath('data.balance_amount', '2400.00');

    expect($response->json('data.recurrence_series_id'))->not->toBeNull();

    $this->getJson('/api/v1/recurrence-series')
        ->assertOk();

    $list = $this->getJson('/api/v1/debts')->assertOk();
    expect($list->json('meta.totals.count'))->toBe(1);
});

test('atualizar parcelas recalcula balance_amount quando acordo não foi cumprido', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $account = FinancialAccount::query()->create([
        'user_id' => $user->id,
        'name' => 'Conta teste',
        'initial_balance' => '1000.00',
        'currency' => 'BRL',
    ]);

    $firstDue = now()->addMonthNoOverflow()->day(15)->toDateString();

    $created = $this->postJson('/api/v1/debts', [
        'title' => 'Dívida renegociada',
        'debt_type' => 'loan',
        'principal_amount' => '1000.00',
        'sync_recurrence' => false,
        'financial_account_id' => $account->id,
        'agreement_formalized_on' => now()->toDateString(),
        'agreement_first_due_date' => $firstDue,
        'agreement_installment_count' => 10,
        'agreement_installment_amount' => '10.00',
    ]);

    $id = $created->json('data.id');

    $this->patchJson("/api/v1/debts/{$id}", [
        'agreement_installment_count' => 4,
        'agreement_installment_amount' => '25.00',
    ])->assertOk()
        ->assertJsonPath('data.balance_amount', '100.00');
});

test('acordo com data de finalização menor ou igual hoje é considerado finalizado', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $created = $this->postJson('/api/v1/debts', [
        'title' => 'Acordo encerrado',
        'debt_type' => 'other',
        'principal_amount' => '900.00',
        'agreement_formalized_on' => now()->subMonths(2)->toDateString(),
        'agreement_end_on' => now()->toDateString(),
        'agreement_installment_count' => 6,
        'agreement_installment_amount' => '150.00',
    ])->assertCreated();

    $id = $created->json('data.id');

    $this->getJson("/api/v1/debts/{$id}")
        ->assertOk()
        ->assertJsonPath('data.agreement_is_finalized', true)
        ->assertJsonPath('data.agreement_fulfilled', true);
});

test('excluir dívida remove recorrência vinculada', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);

    $account = FinancialAccount::query()->create([
        'user_id' => $user->id,
        'name' => 'Conta teste',
        'initial_balance' => '1000.00',
        'currency' => 'BRL',
    ]);

    $firstDue = now()->addMonthNoOverflow()->day(10)->toDateString();

    $created = $this->postJson('/api/v1/debts', [
        'title' => 'Dívida com série',
        'debt_type' => 'loan',
        'principal_amount' => '1000.00',
        'sync_recurrence' => true,
        'financial_account_id' => $account->id,
        'agreement_first_due_date' => $firstDue,
        'agreement_installment_count' => 6,
        'agreement_installment_amount' => '50.00',
    ]);

    $id = $created->json('data.id');
    $recId = $created->json('data.recurrence_series_id');

    $this->deleteJson("/api/v1/debts/{$id}")->assertNoContent();

    $this->getJson("/api/v1/recurrence-series/{$recId}")->assertNotFound();
});
