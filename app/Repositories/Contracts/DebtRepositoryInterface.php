<?php

namespace App\Repositories\Contracts;

use App\Models\Debt;
use Illuminate\Support\Collection;

interface DebtRepositoryInterface
{
    public function allForUser(string $userId): Collection;

    public function findForUserOrFail(string $userId, string $id): Debt;

    public function create(array $data): Debt;

    public function update(Debt $debt, array $data): Debt;

    public function delete(Debt $debt): void;

    public function sumBalanceForUser(string $userId): string;

    public function sumPrincipalForUser(string $userId): string;

    public function countForUser(string $userId): int;
}
