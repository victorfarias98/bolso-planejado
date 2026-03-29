<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Usuário administrador do painel Filament (/admin).
     * Altere a senha em produção.
     */
    public function run(): void
    {
        $free = Plan::query()->where('slug', 'free')->first();

        User::query()->updateOrCreate(
            ['email' => 'admin@dividazero.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin1234'),
                'is_admin' => true,
                'plan_id' => $free?->id,
            ]
        );
    }
}
