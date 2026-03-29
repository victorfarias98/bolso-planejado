<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryDefaultsService
{
    /**
     * @var list<array{slug: string, name: string}>
     */
    private const DEFAULTS = [
        ['slug' => 'moradia', 'name' => 'Moradia'],
        ['slug' => 'alimentacao', 'name' => 'Alimentação'],
        ['slug' => 'transporte', 'name' => 'Transporte'],
        ['slug' => 'dividas-parcelas', 'name' => 'Dívidas / parcelas'],
        ['slug' => 'rendimentos', 'name' => 'Rendimentos'],
        ['slug' => 'assinaturas', 'name' => 'Assinaturas'],
        ['slug' => 'lazer', 'name' => 'Lazer'],
        ['slug' => 'outros', 'name' => 'Outros'],
    ];

    public function ensureDefaultsForUser(User $user): void
    {
        foreach (self::DEFAULTS as $row) {
            Category::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'slug' => $row['slug'],
                ],
                [
                    'name' => $row['name'],
                ]
            );
        }
    }
}
