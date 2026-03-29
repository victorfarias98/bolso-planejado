<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('recomendações gerais retornam estrutura esperada', function (): void {
    $user = User::factory()->create();
    grantPremiumMonthly($user);
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'period' => ['from', 'to'],
                'summary' => ['income', 'expense', 'surplus', 'debt_balance_active', 'invested_total'],
                'monthly_comparison' => [
                    'current' => ['income', 'expense', 'surplus'],
                    'previous' => ['income', 'expense', 'surplus'],
                    'categories_delta',
                ],
                'category_recommendations',
                'category_goals_progress',
                'general_recommendations',
                'investment_recommendation' => ['suggested_monthly_investment', 'message'],
                'weekly_plan',
            ],
        ]);
});
