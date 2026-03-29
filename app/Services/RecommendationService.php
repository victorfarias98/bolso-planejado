<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\CategoryGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\Contracts\InvestmentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        private DebtRepositoryInterface $debts,
        private InvestmentRepositoryInterface $investments,
    ) {}

    public function build(User $user, ?string $from = null, ?string $to = null): array
    {
        $rangeStart = $from ? Carbon::parse($from) : Carbon::today()->startOfMonth();
        $rangeEnd = $to ? Carbon::parse($to) : Carbon::today()->endOfMonth();
        if ($rangeEnd->lt($rangeStart)) {
            $rangeEnd = $rangeStart->copy();
        }
        $start = $rangeStart->toDateString();
        $end = $rangeEnd->toDateString();

        $periodDays = max(1, min(365, $rangeStart->diffInDays($rangeEnd) + 1));
        $prevEndDate = $rangeStart->copy()->subDay();
        $prevStartDate = $prevEndDate->copy()->subDays($periodDays - 1);
        $prevStart = $prevStartDate->toDateString();
        $prevEnd = $prevEndDate->toDateString();

        $currentTotals = $this->totalsByType($user->id, $start, $end);
        $previousTotals = $this->totalsByType($user->id, $prevStart, $prevEnd);

        $income = $currentTotals['income'];
        $expense = $currentTotals['expense'];
        $surplus = $income - $expense;

        $byCategory = $this->expenseByCategory($user->id, $start, $end);
        $byCategoryPrev = $this->expenseByCategory($user->id, $prevStart, $prevEnd)->keyBy('category');
        $top3 = $byCategory->take(3)->values()->all();
        $totalExpense = max($expense, 0.01);

        $categoryTips = array_map(function (array $row) use ($totalExpense): array {
            $pct = ($row['amount'] / $totalExpense) * 100;
            $cut10 = $row['amount'] * 0.10;
            $cut20 = $row['amount'] * 0.20;

            return [
                'category_id' => $row['category_id'],
                'category' => $row['category'],
                'amount' => number_format($row['amount'], 2, '.', ''),
                'expense_share_pct' => number_format($pct, 2, '.', ''),
                'suggested_cut_10_pct' => number_format($cut10, 2, '.', ''),
                'suggested_cut_20_pct' => number_format($cut20, 2, '.', ''),
                'message' => sprintf(
                    'Reavalie %s. Cortar 10%% libera R$ %s/mês; cortar 20%% libera R$ %s/mês.',
                    mb_strtolower($row['category']),
                    number_format($cut10, 2, ',', '.'),
                    number_format($cut20, 2, ',', '.')
                ),
            ];
        }, $top3);

        $debtBalance = (float) $this->debts->sumBalanceForUser($user->id);
        $invested = (float) $this->investments->sumCurrentForUser($user->id);
        $investSuggestion = $this->investmentSuggestion($surplus, $debtBalance);

        return [
            'period' => ['from' => $start, 'to' => $end],
            'summary' => [
                'income' => number_format($income, 2, '.', ''),
                'expense' => number_format($expense, 2, '.', ''),
                'surplus' => number_format($surplus, 2, '.', ''),
                'debt_balance_active' => number_format($debtBalance, 2, '.', ''),
                'invested_total' => number_format($invested, 2, '.', ''),
            ],
            'monthly_comparison' => [
                'current' => [
                    'income' => number_format($income, 2, '.', ''),
                    'expense' => number_format($expense, 2, '.', ''),
                    'surplus' => number_format($surplus, 2, '.', ''),
                ],
                'previous' => [
                    'income' => number_format($previousTotals['income'], 2, '.', ''),
                    'expense' => number_format($previousTotals['expense'], 2, '.', ''),
                    'surplus' => number_format(
                        $previousTotals['income'] - $previousTotals['expense'],
                        2,
                        '.',
                        ''
                    ),
                ],
                'categories_delta' => $this->categoriesDelta($top3, $byCategoryPrev),
            ],
            'category_recommendations' => $categoryTips,
            'category_goals_progress' => $this->categoryGoalsProgress($user->id, $start, $end),
            'general_recommendations' => $this->generalRecommendations($surplus, $debtBalance, $categoryTips),
            'investment_recommendation' => $investSuggestion,
            'weekly_plan' => $this->weeklyPlan($surplus, $debtBalance, $categoryTips),
        ];
    }

    private function sumByType(string $userId, TransactionType $type, string $from, string $to): float
    {
        return (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::Completed)
            ->where('type', $type)
            ->whereBetween('occurred_on', [$from, $to])
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');
    }

    /**
     * @return array{income: float, expense: float}
     */
    private function totalsByType(string $userId, string $from, string $to): array
    {
        $income = TransactionType::Income->value;
        $expense = TransactionType::Expense->value;

        $row = Transaction::query()
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::Completed)
            ->whereBetween('occurred_on', [$from, $to])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as income_total, COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as expense_total',
                [$income, $expense]
            )
            ->first();

        return [
            'income' => (float) ($row->income_total ?? 0),
            'expense' => (float) ($row->expense_total ?? 0),
        ];
    }

    /**
     * @return Collection<int, array{category_id: ?string, category: string, amount: float}>
     */
    private function expenseByCategory(string $userId, string $from, string $to): Collection
    {
        $rows = Transaction::query()
            ->leftJoin('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.status', TransactionStatus::Completed)
            ->where('transactions.type', TransactionType::Expense)
            ->whereBetween('transactions.occurred_on', [$from, $to])
            ->selectRaw("transactions.category_id as category_id, COALESCE(categories.name, 'Sem categoria') as category_name, COALESCE(SUM(transactions.amount), 0) as total")
            ->groupBy('transactions.category_id', 'category_name')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn ($r) => [
            'category_id' => $r->category_id ? (string) $r->category_id : null,
            'category' => (string) $r->category_name,
            'amount' => (float) $r->total,
        ]);
    }

    private function investmentSuggestion(float $surplus, float $debtBalance): array
    {
        if ($surplus <= 0) {
            return [
                'suggested_monthly_investment' => '0.00',
                'message' => 'Sem sobra no mês: priorize reduzir gastos variáveis e estabilizar o caixa antes de aumentar aporte.',
            ];
        }

        $ratio = $debtBalance > 0 ? 0.25 : 0.50;
        $value = $surplus * $ratio;

        return [
            'suggested_monthly_investment' => number_format($value, 2, '.', ''),
            'message' => $debtBalance > 0
                ? 'Com dívida ativa, mantenha aporte menor (aprox. 25% da sobra) e acelere amortização.'
                : 'Sem dívida ativa, você pode direcionar cerca de 50% da sobra para investimentos.',
        ];
    }

    private function generalRecommendations(float $surplus, float $debtBalance, array $categoryTips): array
    {
        $items = [];
        if ($debtBalance > 0) {
            $items[] = 'Priorize despesas essenciais e use parte fixa da sobra para amortizar dívidas mais caras primeiro.';
        }
        if ($surplus < 0) {
            $items[] = 'Seu mês está deficitário: congele gastos não essenciais por 30 dias e reavalie assinaturas e lazer.';
        }
        if (!empty($categoryTips)) {
            $items[] = 'Comece pela maior categoria de gasto para gerar impacto rápido no caixa.';
        }
        if (empty($items)) {
            $items[] = 'Fluxo saudável no mês atual. Mantenha disciplina e revise metas de curto prazo quinzenalmente.';
        }

        return $items;
    }

    private function categoryGoalsProgress(string $userId, string $from, string $to): array
    {
        $goals = CategoryGoal::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->with('category')
            ->get();

        if ($goals->isEmpty()) {
            return [];
        }

        $spentByCategory = Transaction::query()
            ->where('user_id', $userId)
            ->where('status', TransactionStatus::Completed)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_on', [$from, $to])
            ->selectRaw('category_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        return $goals->map(function (CategoryGoal $goal) use ($spentByCategory): array {
            $spent = (float) ($spentByCategory[$goal->category_id]->total ?? 0);
            $limit = (float) $goal->monthly_limit;
            $pct = $limit > 0 ? ($spent / $limit) * 100 : 0;

            return [
                'goal_id' => $goal->id,
                'category_id' => $goal->category_id,
                'category_name' => $goal->category?->name ?? 'Categoria',
                'monthly_limit' => number_format($limit, 2, '.', ''),
                'current_spent' => number_format($spent, 2, '.', ''),
                'progress_pct' => number_format($pct, 2, '.', ''),
                'status' => $spent <= $limit ? 'within_limit' : 'over_limit',
            ];
        })->values()->all();
    }

    private function categoriesDelta(array $topCurrent, Collection $previousByCategory): array
    {
        return array_map(function (array $row) use ($previousByCategory): array {
            $prev = (float) ($previousByCategory[$row['category']]['amount'] ?? 0);
            $delta = $row['amount'] - $prev;
            return [
                'category' => $row['category'],
                'current_amount' => number_format($row['amount'], 2, '.', ''),
                'previous_amount' => number_format($prev, 2, '.', ''),
                'delta_amount' => number_format($delta, 2, '.', ''),
                'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat'),
            ];
        }, $topCurrent);
    }

    private function weeklyPlan(float $surplus, float $debtBalance, array $categoryTips): array
    {
        $focusCategory = $categoryTips[0]['category'] ?? null;

        $tasks = [
            ['day' => 'Segunda', 'title' => 'Revisar gastos da semana anterior', 'detail' => 'Classifique despesas e corrija categorias pendentes.'],
            ['day' => 'Terça', 'title' => 'Negociação / revisão de dívidas', 'detail' => $debtBalance > 0
                ? 'Priorize dívida com maior custo e tente reduzir parcela.'
                : 'Sem dívida ativa: revisar metas para antecipar objetivos.'],
            ['day' => 'Quarta', 'title' => 'Corte direcionado', 'detail' => $focusCategory
                ? "Reavalie gastos de {$focusCategory} e aplique um corte imediato de 10%."
                : 'Reavalie um gasto variável para liberar caixa.'],
            ['day' => 'Quinta', 'title' => 'Ajustar recorrências e assinaturas', 'detail' => 'Pause o que não usa e remarca vencimentos críticos.'],
            ['day' => 'Sexta', 'title' => 'Aporte e fechamento parcial', 'detail' => $surplus > 0
                ? 'Com sobra positiva, faça o aporte sugerido e registre no investimento.'
                : 'Sem sobra no mês, registre plano de contenção para próxima semana.'],
        ];

        return $tasks;
    }
}
