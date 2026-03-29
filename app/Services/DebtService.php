<?php

namespace App\Services;

use App\Enums\DebtStatus;
use App\Enums\TransactionType;
use App\Models\Debt;
use App\Models\User;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\RecurrenceSeriesRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DebtService
{
    public function __construct(
        private DebtRepositoryInterface $debts,
        private RecurrenceSeriesRepositoryInterface $recurrence,
    ) {}

    public function listForUser(User $user): \Illuminate\Support\Collection
    {
        return $this->debts->allForUser($user->id);
    }

    public function find(User $user, string $id): Debt
    {
        return $this->debts->findForUserOrFail($user->id, $id);
    }

    public function create(User $user, array $data): Debt
    {
        return DB::transaction(function () use ($user, $data) {
            $sync = (bool) ($data['sync_recurrence'] ?? false);
            unset($data['sync_recurrence']);

            $data['user_id'] = $user->id;
            $data['status'] = $data['status'] ?? DebtStatus::Open->value;
            $data['currency'] = $data['currency'] ?? 'BRL';
            $data['agreement_formalized_on'] = $data['agreement_formalized_on'] ?? ($data['agreement_date'] ?? null);
            $agreementIsFinalized = $this->agreementIsFinalizedByDate($data['agreement_end_on'] ?? null);
            $data['agreement_fulfilled'] = $agreementIsFinalized;

            if (! isset($data['balance_amount'])) {
                $data['balance_amount'] = $data['principal_amount'];
            }

            if (! empty($data['agreement_first_due_date'])) {
                $d = Carbon::parse($data['agreement_first_due_date']);
                $data['agreement_day_of_month'] = min((int) $d->format('j'), $d->daysInMonth);
            }

            // Quando existir acordo em andamento (renegociação) e as parcelas estiverem definidas,
            // o valor da dívida deve refletir a somatória das parcelas para dar mais previsibilidade.
            if (
                $agreementIsFinalized === false
                && ! empty($data['agreement_installment_count'])
                && $data['agreement_installment_amount'] !== null
                && $data['agreement_installment_amount'] !== ''
            ) {
                $data['balance_amount'] = bcmul(
                    (string) $data['agreement_installment_amount'],
                    (string) $data['agreement_installment_count'],
                    2
                );
            }

            $recurrenceId = null;
            if ($sync) {
                $recurrenceId = $this->createRecurrenceForAgreement($user, $data)->id;
            }

            $data['recurrence_series_id'] = $recurrenceId;

            return $this->debts->create($data);
        });
    }

    public function update(User $user, string $id, array $data): Debt
    {
        unset($data['sync_recurrence']);

        $debt = $this->debts->findForUserOrFail($user->id, $id);
        if (! array_key_exists('agreement_formalized_on', $data) && array_key_exists('agreement_date', $data)) {
            $data['agreement_formalized_on'] = $data['agreement_date'];
        }

        if (! empty($data['agreement_first_due_date'])) {
            $d = Carbon::parse($data['agreement_first_due_date']);
            $data['agreement_day_of_month'] = min((int) $d->format('j'), $d->daysInMonth);
        } elseif (array_key_exists('agreement_first_due_date', $data) && $data['agreement_first_due_date'] === null) {
            $data['agreement_day_of_month'] = null;
        }

        $agreementEndOn = array_key_exists('agreement_end_on', $data)
            ? $data['agreement_end_on']
            : ($debt->agreement_end_on?->toDateString());
        $agreementIsFinalized = $this->agreementIsFinalizedByDate($agreementEndOn);
        $data['agreement_fulfilled'] = $agreementIsFinalized;

        $agreementInstallmentCount = array_key_exists('agreement_installment_count', $data)
            ? $data['agreement_installment_count']
            : $debt->agreement_installment_count;

        $agreementInstallmentAmount = array_key_exists('agreement_installment_amount', $data)
            ? $data['agreement_installment_amount']
            : $debt->agreement_installment_amount;

        if (
            $agreementIsFinalized === false
            && $agreementInstallmentCount !== null
            && $agreementInstallmentCount !== ''
            && $agreementInstallmentAmount !== null
            && $agreementInstallmentAmount !== ''
        ) {
            $data['balance_amount'] = bcmul(
                (string) $agreementInstallmentAmount,
                (string) $agreementInstallmentCount,
                2
            );
        }

        return $this->debts->update($debt, $data);
    }

    private function agreementIsFinalizedByDate(mixed $agreementEndOn): bool
    {
        if (! $agreementEndOn) {
            return false;
        }

        return Carbon::parse((string) $agreementEndOn)->toDateString() <= Carbon::today()->toDateString();
    }

    public function delete(User $user, string $id): void
    {
        DB::transaction(function () use ($user, $id): void {
            $debt = $this->debts->findForUserOrFail($user->id, $id);

            if ($debt->recurrence_series_id) {
                $series = $this->recurrence->findForUserOrFail($user->id, $debt->recurrence_series_id);
                $this->recurrence->delete($series);
            }

            $this->debts->delete($debt);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createRecurrenceForAgreement(User $user, array $data): \App\Models\RecurrenceSeries
    {
        $title = $data['title'] ?? 'Dívida';

        $categoryId = $data['category_id'] ?? \App\Models\Category::query()
            ->where('user_id', $user->id)
            ->where('slug', 'dividas-parcelas')
            ->value('id');

        return $this->recurrence->create([
            'user_id' => $user->id,
            'financial_account_id' => $data['financial_account_id'],
            'category_id' => $categoryId,
            'type' => TransactionType::Expense,
            'amount' => $data['agreement_installment_amount'],
            'day_of_month' => (int) $data['agreement_day_of_month'],
            'start_on' => Carbon::parse($data['agreement_first_due_date'])->toDateString(),
            'max_occurrences' => (int) $data['agreement_installment_count'],
            'description' => $title.' — parcela do acordo',
            'is_active' => true,
        ]);
    }

    /**
     * @return array{principal_total: string, balance_total: string, count: int}
     */
    public function totalsForUser(User $user): array
    {
        return [
            'principal_total' => $this->debts->sumPrincipalForUser($user->id),
            'balance_total' => $this->debts->sumBalanceForUser($user->id),
            'count' => $this->debts->countForUser($user->id),
        ];
    }
}
