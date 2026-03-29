<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\FinancialAccount;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use Laravel\Sanctum\Sanctum;

test('categorias exigem autenticação', function (): void {
    $this->getJson('/api/v1/categories')->assertUnauthorized();
});

test('categorias listadas para o usuário autenticado', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(8, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'slug', 'name'],
            ],
        ]);
});

test('registro retorna usuário e token bearer', function (): void {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Novo Usuário',
        'email' => 'novo@example.com',
        'password' => 'senha-segura-123',
        'password_confirmation' => 'senha-segura-123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'novo@example.com')
        ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token', 'token_type']);

    expect($response->json('user.id'))->toBeString()
        ->and(strlen($response->json('user.id')))->toBe(36);
});

test('login com credenciais válidas retorna token', function (): void {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonStructure(['token']);
});

test('me exige autenticação', function (): void {
    $this->getJson('/api/v1/me')->assertUnauthorized();
});

test('me retorna usuário autenticado via sanctum', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

test('crud de conta financeira com uuid', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $store = $this->postJson('/api/v1/financial-accounts', [
        'name' => 'Conta corrente',
        'initial_balance' => '1500.50',
    ]);

    $store->assertCreated();
    $id = $store->json('data.id');
    expect($id)->toBeString()->and(strlen($id))->toBe(36);

    $this->getJson("/api/v1/financial-accounts/{$id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Conta corrente');

    $this->putJson("/api/v1/financial-accounts/{$id}", [
        'name' => 'Conta atualizada',
    ])->assertOk()->assertJsonPath('data.name', 'Conta atualizada');

    $this->deleteJson("/api/v1/financial-accounts/{$id}")->assertNoContent();
});

test('transação e projeção retornam estrutura esperada', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);

    $account = FinancialAccount::query()->create([
        'user_id' => $user->id,
        'name' => 'Principal',
        'initial_balance' => '1000.00',
        'currency' => 'BRL',
    ]);

    $categoryId = \App\Models\Category::query()
        ->where('user_id', $user->id)
        ->where('slug', 'alimentacao')
        ->value('id');

    $tx = $this->postJson('/api/v1/transactions', [
        'financial_account_id' => $account->id,
        'category_id' => $categoryId,
        'type' => TransactionType::Expense->value,
        'amount' => '200.00',
        'occurred_on' => now()->addDays(5)->toDateString(),
        'status' => TransactionStatus::Scheduled->value,
        'description' => 'Conta futura',
    ]);

    $tx->assertCreated();

    $proj = $this->getJson('/api/v1/projection?horizon_days=10&financial_account_id='.$account->id);
    $proj->assertOk()
        ->assertJsonStructure([
            'data' => [
                'horizon_days',
                'projection_start',
                'projection_end',
                'account_ids',
                'days',
                'summary' => [
                    'opening_balance_consolidated',
                    'minimum_balance',
                    'minimum_balance_date',
                    'first_negative_date',
                    'investment_contributions_cash_total',
                    'investment_contributions_simulated_total',
                    'investment_estimated_yield_period',
                    'net_economic_including_investments',
                    'disclaimer',
                ],
            ],
        ]);

    expect($proj->json('data.days'))->toBeArray()->not->toBeEmpty();
});
