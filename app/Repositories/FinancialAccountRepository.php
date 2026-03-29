<?php

namespace App\Repositories;

use App\Models\FinancialAccount;
use App\Repositories\Contracts\FinancialAccountRepositoryInterface;
use Illuminate\Support\Collection;

class FinancialAccountRepository implements FinancialAccountRepositoryInterface
{
    public function allForUser(string $userId): Collection
    {
        return FinancialAccount::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    public function findForUserOrFail(string $userId, string $id): FinancialAccount
    {
        return FinancialAccount::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data): FinancialAccount
    {
        return FinancialAccount::query()->create($data);
    }

    public function update(FinancialAccount $account, array $data): FinancialAccount
    {
        $account->update($data);

        return $account->fresh();
    }

    public function delete(FinancialAccount $account): void
    {
        $account->delete();
    }
}
