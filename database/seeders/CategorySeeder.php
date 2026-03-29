<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CategoryDefaultsService;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CategoryDefaultsService::class);

        foreach (User::query()->cursor() as $user) {
            $service->ensureDefaultsForUser($user);
        }
    }
}
