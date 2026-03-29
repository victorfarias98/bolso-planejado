<?php

use App\Models\Category;
use App\Models\User;
use App\Services\CategoryDefaultsService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (User::query()->cursor() as $user) {
            if (Category::query()->where('user_id', $user->id)->doesntExist()) {
                app(CategoryDefaultsService::class)->ensureDefaultsForUser($user);
            }
        }
    }
};
