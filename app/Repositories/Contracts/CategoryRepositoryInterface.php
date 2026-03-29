<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function allOrdered(User $user): Collection;

    /**
     * @param  array{name: string}  $data
     */
    public function create(User $user, array $data): Category;
}
