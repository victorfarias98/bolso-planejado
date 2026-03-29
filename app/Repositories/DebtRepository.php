<?php

namespace App\Repositories;

use App\Enums\DebtStatus;
use App\Models\Debt;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Illuminate\Support\Collection;

class DebtRepository implements DebtRepositoryInterface
{
    public function allForUser(string $userId): Collection
    {
        return Debt::query()
            ->where('user_id', $userId)
            ->with(['financialAccount', 'category', 'recurrenceSeries'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    public function findForUserOrFail(string $userId, string $id): Debt
    {
        return Debt::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->with(['financialAccount', 'category', 'recurrenceSeries'])
            ->firstOrFail();
    }

    public function create(array $data): Debt
    {
        return Debt::query()->create($data);
    }

    public function update(Debt $debt, array $data): Debt
    {
        $debt->update($data);

        return $debt->fresh();
    }

    public function delete(Debt $debt): void
    {
        $debt->delete();
    }

    public function sumBalanceForUser(string $userId): string
    {
        $sum = Debt::query()
            ->where('user_id', $userId)
            ->where('status', '!=', DebtStatus::PaidOff)
            ->sum('balance_amount');

        return bcadd((string) $sum, '0', 2);
    }

    public function sumPrincipalForUser(string $userId): string
    {
        $sum = Debt::query()
            ->where('user_id', $userId)
            ->where('status', '!=', DebtStatus::PaidOff)
            ->sum('principal_amount');

        return bcadd((string) $sum, '0', 2);
    }

    public function countForUser(string $userId): int
    {
        return (int) Debt::query()->where('user_id', $userId)->count();
    }
}
