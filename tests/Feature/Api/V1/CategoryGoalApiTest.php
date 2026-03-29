<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('crud de metas por categoria', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $category = Category::query()->create([
        'user_id' => $user->id,
        'slug' => 'teste-goal',
        'name' => 'Teste Goal',
    ]);

    $created = $this->postJson('/api/v1/category-goals', [
        'category_id' => $category->id,
        'monthly_limit' => '500.00',
    ])->assertCreated()
        ->assertJsonPath('data.monthly_limit', '500.00');

    $id = $created->json('data.id');

    $this->patchJson("/api/v1/category-goals/{$id}", [
        'monthly_limit' => '450.00',
    ])->assertOk()
        ->assertJsonPath('data.monthly_limit', '450.00');

    $this->getJson('/api/v1/category-goals')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->deleteJson("/api/v1/category-goals/{$id}")
        ->assertNoContent();
});
