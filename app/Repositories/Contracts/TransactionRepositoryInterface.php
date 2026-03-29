<?php

namespace App\Repositories\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function paginateForUser(string $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findForUserOrFail(string $userId, string $id): Transaction;

    public function create(array $data): Transaction;

    public function update(Transaction $transaction, array $data): Transaction;

    public function delete(Transaction $transaction): void;

    /**
     * Transações (completas ou agendadas) no intervalo [from, to] para as contas informadas.
     *
     * @param  list<string>  $accountIds
     */
    public function forProjectionWindow(string $userId, array $accountIds, string $from, string $to): Collection;

    /**
     * Soma algebrica (entradas − saídas) de transações **concluídas** com occurred_on estritamente anterior a $beforeDate (Y-m-d).
     */
    /**
     * Soma lançamentos (concluídos e agendados) antes da data informada — base do saldo de abertura na projeção.
     */
    public function sumSignedCompletedBefore(string $userId, string $accountId, string $beforeDate): string;
}
