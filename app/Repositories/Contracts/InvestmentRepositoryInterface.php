<?php

namespace App\Repositories\Contracts;

use App\Models\Investment;
use Illuminate\Support\Collection;

interface InvestmentRepositoryInterface
{
    public function allForUser(string $userId): Collection;

    public function activeForUser(string $userId): Collection;

    public function findForUserOrFail(string $userId, string $id): Investment;

    public function create(array $data): Investment;

    public function update(Investment $investment, array $data): Investment;

    public function delete(Investment $investment): void;

    public function sumCurrentForUser(string $userId): string;
}
