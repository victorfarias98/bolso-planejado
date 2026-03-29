<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonthlyReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'include_details' => ['sometimes', 'boolean'],
            'details_limit' => ['sometimes', 'integer', 'min:50', 'max:1000'],
        ]);

        $month = $validated['month'] ?? Carbon::today()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $userId = $request->user()->id;
        $from = $start->toDateString();
        $to = $end->toDateString();

        $incomeType = TransactionType::Income->value;
        $expenseType = TransactionType::Expense->value;
        $includeDetails = (bool) ($validated['include_details'] ?? true);
        $detailsLimit = (int) ($validated['details_limit'] ?? 300);

        $totals = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_on', [$from, $to])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as income_sum, COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as expense_sum',
                [$incomeType, $expenseType]
            )
            ->first();

        $incomeTotal = (float) ($totals->income_sum ?? 0);
        $expenseTotal = (float) ($totals->expense_sum ?? 0);
        $balance = $incomeTotal - $expenseTotal;

        $byCategory = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_on', [$from, $to])
            ->where('type', $expenseType)
            ->leftJoin('categories', 'categories.id', '=', 'transactions.category_id')
            ->selectRaw('MAX(COALESCE(categories.name, ?)) as name, SUM(transactions.amount) as total', ['Sem categoria'])
            ->groupBy('transactions.category_id')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'total' => (float) $row->total,
            ]);

        $baseTransactionsQuery = Transaction::query()
            ->where('user_id', $userId)
            ->whereBetween('occurred_on', [$from, $to]);

        $transactionsCount = (int) (clone $baseTransactionsQuery)->count();
        $isTruncated = false;

        if ($includeDetails) {
            $transactions = $baseTransactionsQuery
                ->select([
                    'id',
                    'financial_account_id',
                    'category_id',
                    'type',
                    'amount',
                    'occurred_on',
                    'status',
                    'description',
                ])
                ->with([
                    'financialAccount:id,name',
                    'category:id,name',
                ])
                ->orderBy('occurred_on')
                ->orderBy('created_at')
                ->limit($detailsLimit)
                ->get();
            $isTruncated = $transactionsCount > $detailsLimit;
        } else {
            $transactions = collect();
        }

        $pdf = Pdf::loadView('reports.monthly', [
            'user' => $request->user(),
            'monthLabel' => $start->locale('pt_BR')->translatedFormat('F \\d\\e Y'),
            'transactions' => $transactions,
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'balance' => $balance,
            'byCategory' => $byCategory,
            'includeDetails' => $includeDetails,
            'transactionsCount' => $transactionsCount,
            'detailsLimit' => $detailsLimit,
            'isTruncated' => $isTruncated,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('relatorio-mensal-%s.pdf', $month);

        return $pdf->download($filename);
    }
}
