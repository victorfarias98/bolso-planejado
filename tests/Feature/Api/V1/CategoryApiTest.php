<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('lista categorias exige autenticação', function (): void {
    $this->getJson('/api/v1/categories')->assertUnauthorized();
});

test('cria categoria autenticado', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/categories', [
        'name' => 'Minha categoria',
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Minha categoria')
        ->assertJsonPath('data.slug', 'minha-categoria');

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => 'Minha categoria',
        'slug' => 'minha-categoria',
    ]);
});

test('slug duplicado recebe sufixo numérico', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/categories', ['name' => 'Teste Slug'])->assertCreated();
    $this->postJson('/api/v1/categories', ['name' => 'Teste Slug'])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'teste-slug-2');
});

test('não cria categoria sem autenticação', function (): void {
    $this->postJson('/api/v1/categories', ['name' => 'X'])
        ->assertUnauthorized();
});

test('valida nome obrigatório', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/categories', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('slug igual é permitido para outro usuário', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    Sanctum::actingAs($userA);
    $this->postJson('/api/v1/categories', ['name' => 'Mesmo nome'])->assertCreated();
    Sanctum::actingAs($userB);
    $this->postJson('/api/v1/categories', ['name' => 'Mesmo nome'])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'mesmo-nome');
});
