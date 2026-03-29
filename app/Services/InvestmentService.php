<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\InvestmentRepositoryInterface;
use Carbon\Carbon;

class InvestmentService
{
    public function __construct(
        private InvestmentRepositoryInterface $investments,
        private DebtRepositoryInterface $debts,
    ) {}

    public function listForUser(User $user): \Illuminate\Support\Collection
    {
        return $this->investments->allForUser($user->id);
    }

    public function find(User $user, string $id): \App\Models\Investment
    {
        return $this->investments->findForUserOrFail($user->id, $id);
    }

    public function create(User $user, array $data): \App\Models\Investment
    {
        $data['user_id'] = $user->id;
        $data['currency'] = $data['currency'] ?? 'BRL';
        $data['monthly_contribution'] = $data['monthly_contribution'] ?? '0';
        $data['monthly_return_rate'] = $data['monthly_return_rate'] ?? '0';
        $data['contribution_day'] = $data['contribution_day'] ?? 5;
        $data['is_active'] = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return $this->investments->create($data);
    }

    public function update(User $user, string $id, array $data): \App\Models\Investment
    {
        $model = $this->investments->findForUserOrFail($user->id, $id);

        return $this->investments->update($model, $data);
    }

    public function delete(User $user, string $id): void
    {
        $model = $this->investments->findForUserOrFail($user->id, $id);
        $this->investments->delete($model);
    }

    public function metaForUser(User $user): array
    {
        $list = $this->investments->allForUser($user->id);
        $active = $list->where('is_active', true);
        $currentTotal = $active->sum(fn ($i) => (float) $i->current_amount);
        $monthlyContributionTotal = $active->sum(fn ($i) => (float) $i->monthly_contribution);
        $monthlyYieldTotal = $active->sum(function ($i): float {
            $base = (float) $i->current_amount + (float) ($i->monthly_contribution ?? 0);

            return $base * (((float) $i->monthly_return_rate) / 100);
        });
        $averageMonthlyRatePct = $this->weightedAverageMonthlyReturnRatePercent($active);
        $debtBalance = (float) $this->debts->sumBalanceForUser($user->id);

        return [
            'totals' => [
                'current_total' => number_format($currentTotal, 2, '.', ''),
                'monthly_contribution_total' => number_format($monthlyContributionTotal, 2, '.', ''),
                'monthly_yield_estimate_total' => number_format($monthlyYieldTotal, 2, '.', ''),
                'active_count' => $active->count(),
            ],
            'analysis' => $this->buildAnalysis(
                $user,
                $debtBalance,
                $monthlyContributionTotal,
                $monthlyYieldTotal,
                $averageMonthlyRatePct,
                $currentTotal
            ),
        ];
    }

    /**
     * Taxa mensal média ponderada por (saldo + aporte), para cenários 12m coerentes com juros compostos.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Investment>  $active
     */
    private function weightedAverageMonthlyReturnRatePercent(\Illuminate\Support\Collection $active): float
    {
        $numerator = 0.0;
        $denominator = 0.0;
        foreach ($active as $i) {
            $weight = (float) $i->current_amount + (float) ($i->monthly_contribution ?? 0);
            if ($weight <= 0) {
                continue;
            }
            $numerator += $weight * (float) $i->monthly_return_rate;
            $denominator += $weight;
        }

        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    private function buildAnalysis(
        User $user,
        float $debtBalance,
        float $monthlyContributionTotal,
        float $monthlyYieldTotal,
        float $averageMonthlyRatePct,
        float $currentTotal
    ): array
    {
        $start = Carbon::today()->startOfMonth()->toDateString();
        $end = Carbon::today()->endOfMonth()->toDateString();

        $income = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('status', TransactionStatus::Completed)
            ->where('type', TransactionType::Income)
            ->whereBetween('occurred_on', [$start, $end])
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $expense = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('status', TransactionStatus::Completed)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_on', [$start, $end])
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $surplus = $income - $expense;
        $recommendations = [];
        $split = $this->recommendedSplit($debtBalance, $surplus);
        $payoffMonths = $this->estimateDebtPayoffMonths($debtBalance, $split['debt_payment']);
        $scenarios = $this->buildScenarioProjection(
            $currentTotal,
            $split['invest_contribution'],
            $monthlyContributionTotal,
            $averageMonthlyRatePct
        );

        if ($debtBalance > 0 && $surplus <= 0) {
            $recommendations[] = 'Com dívida ativa e sem sobra no mês, priorize estabilizar caixa e renegociar antes de aumentar aportes.';
        }
        if ($debtBalance > 0 && $surplus > 0) {
            $recommendations[] = 'Com sobra mensal e dívida ativa, destine parte para acelerar quitação e mantenha aporte mínimo nas caixinhas.';
        }
        if ($monthlyContributionTotal > $surplus && $surplus > 0) {
            $recommendations[] = 'Seus aportes mensais estão acima da sobra real do mês. Ajuste para não pressionar o caixa.';
        }
        if ($monthlyYieldTotal < ($monthlyContributionTotal * 0.15) && $monthlyContributionTotal > 0) {
            $recommendations[] = 'Rentabilidade mensal estimada ainda baixa frente ao aporte. Reveja alocação das caixinhas para objetivos por prazo.';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'Estratégia equilibrada no momento. Mantenha consistência de aportes e revisões mensais.';
        }

        return [
            'month_income' => number_format($income, 2, '.', ''),
            'month_expense' => number_format($expense, 2, '.', ''),
            'month_surplus' => number_format($surplus, 2, '.', ''),
            'debt_balance_active' => number_format($debtBalance, 2, '.', ''),
            'recommended_debt_payment' => number_format($split['debt_payment'], 2, '.', ''),
            'recommended_investment_contribution' => number_format($split['invest_contribution'], 2, '.', ''),
            'recommended_debt_payment_pct' => number_format($split['debt_pct'], 2, '.', ''),
            'recommended_investment_pct' => number_format($split['invest_pct'], 2, '.', ''),
            'estimated_debt_payoff_months' => $payoffMonths,
            'scenario_12m' => $scenarios,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * @return array{debt_payment: float, invest_contribution: float, debt_pct: float, invest_pct: float}
     */
    private function recommendedSplit(float $debtBalance, float $surplus): array
    {
        if ($surplus <= 0) {
            return [
                'debt_payment' => 0.0,
                'invest_contribution' => 0.0,
                'debt_pct' => 0.0,
                'invest_pct' => 0.0,
            ];
        }

        if ($debtBalance <= 0) {
            return [
                'debt_payment' => 0.0,
                'invest_contribution' => $surplus,
                'debt_pct' => 0.0,
                'invest_pct' => 100.0,
            ];
        }

        $debtPct = $debtBalance > 5000 ? 75.0 : 60.0;
        $investPct = 100.0 - $debtPct;

        return [
            'debt_payment' => ($surplus * $debtPct) / 100,
            'invest_contribution' => ($surplus * $investPct) / 100,
            'debt_pct' => $debtPct,
            'invest_pct' => $investPct,
        ];
    }

    private function estimateDebtPayoffMonths(float $debtBalance, float $monthlyDebtPayment): ?int
    {
        if ($debtBalance <= 0) {
            return 0;
        }
        if ($monthlyDebtPayment <= 0) {
            return null;
        }

        return (int) ceil($debtBalance / $monthlyDebtPayment);
    }

    /**
     * @return array{conservative: string, base: string, optimistic: string}
     */
    private function buildScenarioProjection(
        float $currentTotal,
        float $recommendedInvestContribution,
        float $currentMonthlyContribution,
        float $averageMonthlyReturnRatePercent
    ): array {
        $baseContribution = max($recommendedInvestContribution, $currentMonthlyContribution);
        $baseRate = max($averageMonthlyReturnRatePercent, 0);

        $conservative = $this->futureValue12m($currentTotal, $baseContribution, $baseRate * 0.7);
        $base = $this->futureValue12m($currentTotal, $baseContribution, $baseRate);
        $optimistic = $this->futureValue12m($currentTotal, $baseContribution, $baseRate * 1.3);

        return [
            'conservative' => number_format($conservative, 2, '.', ''),
            'base' => number_format($base, 2, '.', ''),
            'optimistic' => number_format($optimistic, 2, '.', ''),
        ];
    }

    private function futureValue12m(float $current, float $monthlyContribution, float $ratePct): float
    {
        $rate = $ratePct / 100;
        $value = $current;
        for ($i = 0; $i < 12; $i++) {
            $value += $monthlyContribution;
            $value *= (1 + $rate);
        }

        return $value;
    }
}
