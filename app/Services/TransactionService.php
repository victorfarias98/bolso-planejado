<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactions,
        private RecurrenceMaterializationService $materialization,
    ) {}

    public function paginate(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->materialization->syncForUser($user, Carbon::today(), Carbon::today()->addYear());
        return $this->transactions->paginateForUser($user->id, $filters, $perPage);
    }

    public function create(User $user, array $data): Transaction
    {
        $data['user_id'] = $user->id;

        return $this->transactions->create($data);
    }

    public function update(User $user, string $id, array $data): Transaction
    {
        $transaction = $this->transactions->findForUserOrFail($user->id, $id);

        return $this->transactions->update($transaction, $data);
    }

    public function delete(User $user, string $id): void
    {
        $transaction = $this->transactions->findForUserOrFail($user->id, $id);
        $this->transactions->delete($transaction);
    }

    public function find(User $user, string $id): Transaction
    {
        return $this->transactions->findForUserOrFail($user->id, $id);
    }
}
