<?php

namespace App\Repositories\Contracts;

use App\Models\FinancialAccount;
use Illuminate\Support\Collection;

interface FinancialAccountRepositoryInterface
{
    public function allForUser(string $userId): Collection;

    public function findForUserOrFail(string $userId, string $id): FinancialAccount;

    public function create(array $data): FinancialAccount;

    public function update(FinancialAccount $account, array $data): FinancialAccount;

    public function delete(FinancialAccount $account): void;
}
