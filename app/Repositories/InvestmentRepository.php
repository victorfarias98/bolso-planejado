<?php

namespace App\Repositories;

use App\Models\Investment;
use App\Repositories\Contracts\InvestmentRepositoryInterface;
use Illuminate\Support\Collection;

class InvestmentRepository implements InvestmentRepositoryInterface
{
    public function allForUser(string $userId): Collection
    {
        return Investment::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function activeForUser(string $userId): Collection
    {
        return Investment::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUserOrFail(string $userId, string $id): Investment
    {
        return Investment::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data): Investment
    {
        return Investment::query()->create($data);
    }

    public function update(Investment $investment, array $data): Investment
    {
        $investment->update($data);

        return $investment->fresh();
    }

    public function delete(Investment $investment): void
    {
        $investment->delete();
    }

    public function sumCurrentForUser(string $userId): string
    {
        return (string) Investment::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(current_amount), 0) as total')
            ->value('total');
    }
}
