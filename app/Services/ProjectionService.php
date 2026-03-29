<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\FinancialAccount;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\FinancialAccountRepositoryInterface;
use App\Repositories\Contracts\InvestmentRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProjectionService
{
    /** Limite de dias na projeção (evita abuso; ~55 anos). Intervalo from/to é respeitado até este teto. */
    private const MAX_PROJECTION_DAYS = 20000;

    public function __construct(
        private FinancialAccountRepositoryInterface $accounts,
        private TransactionRepositoryInterface $transactions,
        private InvestmentRepositoryInterface $investments,
        private RecurrenceMaterializationService $materialization,
    ) {}

    /**
     * @return array{
     *   horizon_days: int,
     *   projection_start: string,
     *   projection_end: string,
     *   account_ids: list<string>,
     *   days: list<array<string, mixed>>,
     *   summary: array<string, mixed>
     * }
     */
    public function build(
        User $user,
        ?string $accountId,
        int $horizonDays,
        ?string $fromDate = null,
        ?string $toDate = null
    ): array
    {
        $horizonDays = max(1, min($horizonDays, self::MAX_PROJECTION_DAYS));
        $start = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::today()->startOfDay();
        $end = $toDate ? Carbon::parse($toDate)->endOfDay() : $start->copy()->addDays($horizonDays - 1)->endOfDay();

        if ($end->lt($start)) {
            // Mantém o comportamento previsível: se usuário errar intervalo, projeta a partir do start.
            $end = $start->copy()->addDays($horizonDays - 1)->endOfDay();
        }

        // Segurança: limita tamanho máximo para não travar a projeção.
        $maxDays = self::MAX_PROJECTION_DAYS;
        $actualDays = $start->copy()->startOfDay()->diffInDays($end->copy()->endOfDay()) + 1;
        if ($actualDays > $maxDays) {
            $end = $start->copy()->addDays($maxDays - 1)->endOfDay();
        }

        $rangeStartStr = $start->toDateString();
        $rangeEndStr = $end->copy()->startOfDay()->toDateString();
        // Dias do intervalo real (from/to), não o parâmetro horizon_days da requisição.
        $projectionDayCount = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $projectionDayCount = max(1, min($projectionDayCount, $maxDays));

        $this->materialization->syncForUser($user, $start, $end);

        $accountList = $accountId
            ? collect([$this->accounts->findForUserOrFail($user->id, $accountId)])
            : $this->accounts->allForUser($user->id);

        if ($accountList->isEmpty()) {
            return [
                'horizon_days' => $projectionDayCount,
                'projection_start' => $rangeStartStr,
                'projection_end' => $rangeEndStr,
                'account_ids' => [],
                'days' => [],
                'summary' => [
                    'opening_balance_consolidated' => null,
                    'minimum_balance' => null,
                    'minimum_balance_date' => null,
                    'first_negative_date' => null,
                    'projected_investments_end' => '0.00',
                    'projected_net_worth_end' => '0.00',
                    'income_total' => '0.00',
                    'expense_total' => '0.00',
                    'net_cash_flow_total' => '0.00',
                    'investment_contributions_cash_total' => '0.00',
                    'investment_contributions_simulated_total' => '0.00',
                    'investment_estimated_yield_period' => '0.00',
                    'net_economic_including_investments' => '0.00',
                    'disclaimer' => 'Cadastre ao menos uma conta para ver a projeção.',
                ],
            ];
        }

        $accountIds = $accountList->pluck('id')->all();
        $windowTx = $this->transactions->forProjectionWindow(
            $user->id,
            $accountIds,
            $start->toDateString(),
            $end->toDateString()
        );

        $txByAccountDate = $this->indexTransactionsByAccountAndDate($windowTx);

        $activeInvestments = $this->investments->activeForUser($user->id);
        $invMonths = $this->investmentMonthsFromProjectionDays($projectionDayCount);

        $days = [];
        $minBalance = null;
        $minDate = null;
        $firstNegativeDate = null;
        $incomeTotal = '0.00';
        $expenseTotal = '0.00';
        $investmentContributionCashTotal = '0.00';
        $openingBalancePeriodStart = null;

        foreach ($this->eachDate($start, $end) as $date) {
            $dateStr = $date->toDateString();
            $movements = [];
            $endByAccount = [];
            $dayOpeningConsolidated = '0';

            foreach ($accountList as $account) {
                /** @var FinancialAccount $account */
                $aid = $account->id;

                if ($dateStr === $start->toDateString()) {
                    // Saldo após tudo que já foi concluído até o fim do dia anterior à projeção.
                    $carry = bcadd((string) $account->initial_balance, $this->transactions->sumSignedCompletedBefore(
                        $user->id,
                        $aid,
                        $start->copy()->toDateString()
                    ), 2);
                } else {
                    $prev = end($days);
                    $carry = $prev['end_balances_by_account'][$aid] ?? '0';
                }

                $dayOpeningConsolidated = bcadd($dayOpeningConsolidated, $carry, 2);

                $dayTx = $this->transactionsForDayFromIndex($txByAccountDate, $aid, $dateStr);
                $sorted = $dayTx->sort(function ($a, $b): int {
                    $order = fn ($x) => ($x['sort'] ?? 2) === 'income' ? 0 : 1;

                    return $order($a) <=> $order($b);
                })->values();

                foreach ($sorted as $m) {
                    $carry = bcadd($carry, $m['signed_amount'], 2);
                    $movements[] = $m;
                }

                $endByAccount[$aid] = $carry;
            }

            $consolidated = '0';
            foreach ($endByAccount as $bal) {
                $consolidated = bcadd($consolidated, $bal, 2);
            }

            $investmentMoves = $this->investmentContributionMovementsForDay($activeInvestments, $date);
            foreach ($investmentMoves as $m) {
                $movements[] = $m;
                if (($m['source'] ?? '') === 'investment_transfer_out') {
                    $signed = (string) ($m['signed_amount'] ?? '0');
                    $abs = bcsub('0', $signed, 2);
                    $investmentContributionCashTotal = bcadd($investmentContributionCashTotal, $abs, 2);
                }
                if (($m['affects_cash'] ?? true) === true) {
                    $consolidated = bcadd($consolidated, $m['signed_amount'], 2);
                }
            }

            usort($movements, function (array $a, array $b): int {
                $rank = fn (array $item): int => ($item['sort'] ?? 'expense') === 'income' ? 0 : 1;
                return $rank($a) <=> $rank($b);
            });

            // Totais do período (entradas/saídas) apenas para movimentos que afetam caixa.
            foreach ($movements as $m) {
                if (($m['affects_cash'] ?? true) === false) continue;
                $signed = (string) ($m['signed_amount'] ?? '0');
                if (bccomp($signed, '0', 2) >= 0) {
                    $incomeTotal = bcadd($incomeTotal, $signed, 2);
                } else {
                    $abs = bcsub('0', $signed, 2);
                    $expenseTotal = bcadd($expenseTotal, $abs, 2);
                }
            }

            if ($minBalance === null || bccomp($consolidated, $minBalance, 2) < 0) {
                $minBalance = $consolidated;
                $minDate = $dateStr;
            }

            if (bccomp($consolidated, '0', 2) < 0 && $firstNegativeDate === null) {
                $firstNegativeDate = $dateStr;
            }

            $days[] = [
                'date' => $dateStr,
                'end_balance_consolidated' => $consolidated,
                'end_balances_by_account' => $endByAccount,
                'movements' => $movements,
            ];
        }

        $projectedInvestmentEnd = $this->projectInvestmentsForHorizon($user, $invMonths);
        $investmentSimulatedContributionsTotal = $this->investmentSimulatedContributionsTotal($user, $invMonths);
        $investmentInitialTotal = $this->investmentInitialTotal($user);
        $investmentEstimatedYieldPeriod = bcsub(
            $projectedInvestmentEnd,
            bcadd($investmentInitialTotal, $investmentSimulatedContributionsTotal, 2),
            2
        );
        if (bccomp($investmentEstimatedYieldPeriod, '0', 2) < 0) {
            $investmentEstimatedYieldPeriod = '0.00';
        }

        $lastCashEnd = $days ? ($days[count($days) - 1]['end_balance_consolidated'] ?? '0') : '0';
        $projectedNetWorthEnd = bcadd((string) $lastCashEnd, $projectedInvestmentEnd, 2);
        $netCashFlow = bcsub($incomeTotal, $expenseTotal, 2);
        $netEconomicIncludingInvestments = bcadd($netCashFlow, $investmentEstimatedYieldPeriod, 2);

        return [
            'horizon_days' => $projectionDayCount,
            'projection_start' => $rangeStartStr,
            'projection_end' => $rangeEndStr,
            'account_ids' => $accountIds,
            'days' => $days,
            'summary' => [
                'opening_balance_consolidated' => $openingBalancePeriodStart,
                'current_balance' => $days[0]['end_balance_consolidated'] ?? null,
                'current_balance_date' => $days[0]['date'] ?? $start->toDateString(),
                'minimum_balance' => $minBalance,
                'minimum_balance_date' => $minDate,
                'first_negative_date' => $firstNegativeDate,
                'projected_investments_end' => $projectedInvestmentEnd,
                'projected_net_worth_end' => $projectedNetWorthEnd,
                'income_total' => $incomeTotal,
                'expense_total' => $expenseTotal,
                'net_cash_flow_total' => $netCashFlow,
                'investment_contributions_cash_total' => $investmentContributionCashTotal,
                'investment_contributions_simulated_total' => $investmentSimulatedContributionsTotal,
                'investment_estimated_yield_period' => $investmentEstimatedYieldPeriod,
                'net_economic_including_investments' => $netEconomicIncludingInvestments,
                'disclaimer' => 'Projeção baseada nos lançamentos cadastrados; não substitui extrato bancário.',
            ],
        ];
    }

    /**
     * @return iterable<CarbonInterface>
     */
    private function eachDate(Carbon $start, Carbon $end): iterable
    {
        $d = $start->copy();
        while ($d->lte($end)) {
            yield $d->copy();
            $d->addDay();
        }
    }

    /**
     * Índice conta + data → transações do intervalo (evita filtrar a janela inteira por dia).
     *
     * @param  Collection<int, Transaction>  $windowTx
     * @return array<string, array<string, array<int, Transaction>>>
     */
    private function indexTransactionsByAccountAndDate(Collection $windowTx): array
    {
        $index = [];
        foreach ($windowTx as $t) {
            $aid = $t->financial_account_id;
            $ds = $t->occurred_on->toDateString();
            $index[$aid][$ds][] = $t;
        }

        return $index;
    }

    /**
     * @param  array<string, array<string, array<int, Transaction>>>  $index
     * @return Collection<int, array<string, mixed>>
     */
    private function transactionsForDayFromIndex(array $index, string $accountId, string $dateStr): Collection
    {
        $list = $index[$accountId][$dateStr] ?? [];

        return collect($list)->map(fn (Transaction $t) => [
            'source' => 'transaction',
            'id' => $t->id,
            'financial_account_id' => $t->financial_account_id,
            'description' => $t->description ?? $t->type->value,
            'type' => $t->type->value,
            'status' => $t->status->value,
            'signed_amount' => $t->signedAmount(),
            'sort' => $t->type === TransactionType::Income ? 'income' : 'expense',
        ])->values();
    }

    private function investmentMonthsFromProjectionDays(int $projectionDays): int
    {
        $projectionDays = max(1, min($projectionDays, self::MAX_PROJECTION_DAYS));
        $avgDaysPerMonth = 365.0 / 12.0;

        return max(1, (int) round($projectionDays / $avgDaysPerMonth));
    }

    /**
     * Soma dos saldos iniciais das caixinhas ativas (base para rendimento no período).
     */
    private function investmentInitialTotal(User $user): string
    {
        $sum = '0.00';
        foreach ($this->investments->activeForUser($user->id) as $inv) {
            $sum = bcadd($sum, (string) $inv->current_amount, 2);
        }

        return $sum;
    }

    /**
     * Aportes totais no modelo mensal (meses × aporte) — alinhado à simulação de juros compostos.
     */
    private function investmentSimulatedContributionsTotal(User $user, int $months): string
    {
        $sum = '0.00';
        $monthsStr = (string) $months;
        foreach ($this->investments->activeForUser($user->id) as $inv) {
            $sum = bcadd($sum, bcmul((string) $inv->monthly_contribution, $monthsStr, 2), 2);
        }

        return $sum;
    }

    /**
     * Valor projetado das caixinhas no fim do período (mesmo modelo da tela de investimentos: aporte + juros compostos).
     *
     * @param  int  $months  Número de meses equivalente ao intervalo da projeção (from/to).
     */
    private function projectInvestmentsForHorizon(User $user, int $months): string
    {
        $active = $this->investments->activeForUser($user->id);
        if ($active->isEmpty()) {
            return '0.00';
        }

        $months = max(1, $months);

        $result = 0.0;
        foreach ($active as $inv) {
            /** @var Investment $inv */
            $value = (float) $inv->current_amount;
            $rate = ((float) $inv->monthly_return_rate) / 100;
            $contribution = (float) $inv->monthly_contribution;
            for ($i = 0; $i < $months; $i++) {
                $value += $contribution;
                $value *= (1 + $rate);
            }
            $result += $value;
        }

        return number_format($result, 2, '.', '');
    }

    /**
     * @param  Collection<int, Investment>  $activeInvestments
     * @return Collection<int, array<string, mixed>>
     */
    private function investmentContributionMovementsForDay(Collection $activeInvestments, CarbonInterface $date): Collection
    {
        $day = (int) $date->format('j');

        return $activeInvestments
            ->filter(fn (Investment $inv) => $day === max(1, min((int) $inv->contribution_day, (int) $date->daysInMonth)))
            ->flatMap(function (Investment $inv): array {
                $amount = number_format((float) $inv->monthly_contribution, 2, '.', '');
                if ((float) $amount <= 0) {
                    return [];
                }

                return [
                    [
                        'source' => 'investment_transfer_out',
                        'id' => $inv->id,
                        'financial_account_id' => null,
                        'description' => 'Aporte investimento: '.$inv->title,
                        'type' => TransactionType::Expense->value,
                        'status' => TransactionStatus::Scheduled->value,
                        'signed_amount' => '-'.$amount,
                        'sort' => 'expense',
                        'affects_cash' => true,
                    ],
                    [
                        'source' => 'investment_transfer_in',
                        'id' => $inv->id,
                        'financial_account_id' => null,
                        'description' => 'Entrada na caixinha: '.$inv->title,
                        'type' => TransactionType::Income->value,
                        'status' => TransactionStatus::Scheduled->value,
                        'signed_amount' => $amount,
                        'sort' => 'income',
                        'affects_cash' => false,
                    ],
                ];
            })
            ->values();
    }
}
