<?php

namespace App\Repositories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginateForUser(string $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->where('transactions.user_id', $userId)
            ->with(['financialAccount', 'category']);

        if (! empty($filters['financial_account_id'])) {
            $query->where('transactions.financial_account_id', $filters['financial_account_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('transactions.status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('transactions.occurred_on', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('transactions.occurred_on', '<=', $filters['to']);
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'desc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $sortKey = (string) ($filters['sort'] ?? 'created_at');
        $allowed = [
            'occurred_on',
            'created_at',
            'amount',
            'type',
            'status',
            'description',
            'account_name',
        ];
        if (! in_array($sortKey, $allowed, true)) {
            $sortKey = 'created_at';
        }

        if ($sortKey === 'account_name') {
            $query->join('financial_accounts', 'financial_accounts.id', '=', 'transactions.financial_account_id')
                ->where('financial_accounts.user_id', $userId)
                ->select('transactions.*')
                ->orderBy('financial_accounts.name', $direction);
        } else {
            $query->orderBy('transactions.'.$sortKey, $direction);
        }

        $query->orderBy('transactions.id', $direction);

        return $query->paginate($perPage);
    }

    public function findForUserOrFail(string $userId, string $id): Transaction
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function create(array $data): Transaction
    {
        return Transaction::query()->create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->fresh();
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    public function forProjectionWindow(string $userId, array $accountIds, string $from, string $to): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->whereIn('financial_account_id', $accountIds)
            ->whereBetween('occurred_on', [$from, $to])
            ->whereIn('status', [
                TransactionStatus::Completed->value,
                TransactionStatus::Scheduled->value,
            ])
            ->orderBy('occurred_on')
            ->get();
    }

    /**
     * Soma valores assinados de lançamentos com data estritamente anterior a {@see $beforeDate}.
     * Inclui "scheduled" para que recorrências materializadas entrem no saldo de abertura da projeção.
     *
     * Agregação no banco (evita carregar todo o histórico na memória).
     */
    public function sumSignedCompletedBefore(string $userId, string $accountId, string $beforeDate): string
    {
        $income = TransactionType::Income->value;

        $raw = Transaction::query()
            ->where('user_id', $userId)
            ->where('financial_account_id', $accountId)
            ->whereIn('status', [
                TransactionStatus::Completed->value,
                TransactionStatus::Scheduled->value,
            ])
            ->whereDate('occurred_on', '<', $beforeDate)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE -amount END), 0) as signed_sum',
                [$income]
            )
            ->value('signed_sum');

        return bcadd('0', (string) $raw, 2);
    }
}
