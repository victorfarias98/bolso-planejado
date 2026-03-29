<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('investimentos retornam lista, totais e análise', function (): void {
    $user = User::factory()->create();
    grantPremiumMonthly($user);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/investments')
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => [
                'totals' => ['current_total', 'monthly_contribution_total', 'monthly_yield_estimate_total', 'active_count'],
                'analysis' => [
                    'month_income',
                    'month_expense',
                    'month_surplus',
                    'debt_balance_active',
                    'recommended_debt_payment',
                    'recommended_investment_contribution',
                    'recommended_debt_payment_pct',
                    'recommended_investment_pct',
                    'estimated_debt_payoff_months',
                    'scenario_12m' => ['conservative', 'base', 'optimistic'],
                    'recommendations',
                ],
            ],
        ]);
});

test('cria, atualiza e remove investimento', function (): void {
    $user = User::factory()->create();
    grantPremiumMonthly($user);
    Sanctum::actingAs($user);

    $created = $this->postJson('/api/v1/investments', [
        'title' => 'Caixinha emergência',
        'investment_type' => 'pocket',
        'current_amount' => '1500.00',
        'monthly_contribution' => '200.00',
        'monthly_return_rate' => '0.80',
    ])->assertCreated()
        ->assertJsonPath('data.title', 'Caixinha emergência');

    $id = $created->json('data.id');

    $this->patchJson("/api/v1/investments/{$id}", [
        'monthly_contribution' => '300.00',
    ])->assertOk()
        ->assertJsonPath('data.monthly_contribution', '300.00');

    $this->deleteJson("/api/v1/investments/{$id}")
        ->assertNoContent();
});
