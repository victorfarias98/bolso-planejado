<?php

namespace App\Services;

use App\Models\FinancialAccount;
use App\Models\User;
use App\Repositories\Contracts\FinancialAccountRepositoryInterface;

class FinancialAccountService
{
    public function __construct(
        private FinancialAccountRepositoryInterface $accounts,
    ) {}

    public function listForUser(User $user): \Illuminate\Support\Collection
    {
        return $this->accounts->allForUser($user->id);
    }

    public function create(User $user, array $data): FinancialAccount
    {
        $data['user_id'] = $user->id;
        $data['initial_balance'] = $data['initial_balance'] ?? '0';
        $data['currency'] = $data['currency'] ?? 'BRL';

        return $this->accounts->create($data);
    }

    public function update(User $user, string $id, array $data): FinancialAccount
    {
        $account = $this->accounts->findForUserOrFail($user->id, $id);

        return $this->accounts->update($account, $data);
    }

    public function delete(User $user, string $id): void
    {
        $account = $this->accounts->findForUserOrFail($user->id, $id);
        $this->accounts->delete($account);
    }

    public function find(User $user, string $id): FinancialAccount
    {
        return $this->accounts->findForUserOrFail($user->id, $id);
    }
}
