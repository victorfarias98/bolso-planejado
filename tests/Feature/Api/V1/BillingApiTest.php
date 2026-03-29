<?php

use App\Models\Plan;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('lista de planos pagos é pública', function (): void {
    $this->getJson('/api/v1/billing/plans')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

test('checkout simulado exige autenticação', function (): void {
    $this->postJson('/api/v1/billing/checkout', [
        'plan_slug' => 'premium-monthly',
    ])->assertUnauthorized();
});

test('checkout simulado ativa assinatura premium mensal', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/billing/checkout', [
        'plan_slug' => 'premium-monthly',
    ])
        ->assertCreated()
        ->assertJsonPath('plan_slug', 'premium-monthly');

    $plan = Plan::query()->where('slug', 'premium-monthly')->firstOrFail();

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => $user->id,
        'plan_id' => $plan->id,
    ]);

    $this->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.billing.plan.slug', 'premium-monthly');
});

test('usuário free não acessa recomendações na api', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/recommendations')
        ->assertForbidden()
        ->assertJsonPath('code', 'feature_locked');
});
