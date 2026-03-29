<?php

namespace App\Models;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'financial_account_id',
        'category_id',
        'title',
        'creditor',
        'debt_type',
        'principal_amount',
        'balance_amount',
        'currency',
        'status',
        'agreement_date',
        'agreement_formalized_on',
        'agreement_end_on',
        'agreement_fulfilled',
        'agreement_day_of_month',
        'agreement_first_due_date',
        'agreement_installment_count',
        'agreement_installment_amount',
        'agreement_down_payment',
        'agreement_notes',
        'recurrence_series_id',
    ];

    protected function casts(): array
    {
        return [
            'debt_type' => DebtType::class,
            'status' => DebtStatus::class,
            'principal_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'agreement_date' => 'date',
            'agreement_formalized_on' => 'date',
            'agreement_end_on' => 'date',
            'agreement_fulfilled' => 'boolean',
            'agreement_first_due_date' => 'date',
            'agreement_installment_amount' => 'decimal:2',
            'agreement_down_payment' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financialAccount(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function recurrenceSeries(): BelongsTo
    {
        return $this->belongsTo(RecurrenceSeries::class);
    }
}
