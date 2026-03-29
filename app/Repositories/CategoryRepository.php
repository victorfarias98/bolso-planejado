<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function allOrdered(User $user): Collection
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    public function create(User $user, array $data): Category
    {
        $name = trim($data['name']);
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'categoria';
        }

        return Category::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $this->ensureUniqueSlug($user->id, $baseSlug),
        ]);
    }

    private function ensureUniqueSlug(string $userId, string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Category::query()->where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
