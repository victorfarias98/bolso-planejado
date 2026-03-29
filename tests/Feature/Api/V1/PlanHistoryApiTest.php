<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('cria e lista histórico de plano aplicado', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/plan-histories', [
        'category_id' => null,
        'cut_amount' => '100.00',
        'extra_investment' => '50.00',
        'debt_share_pct' => '70.00',
        'simulated_debt_payment' => '350.00',
        'simulated_investment_total' => '200.00',
        'simulated_debt_payoff_months' => 12,
        'notes' => 'Plano aplicado pelo simulador',
    ])->assertCreated();

    $this->getJson('/api/v1/plan-histories')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
