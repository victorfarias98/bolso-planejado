<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'financial_account_id',
        'category_id',
        'recurrence_series_id',
        'type',
        'amount',
        'occurred_on',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
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

    public function signedAmount(): string
    {
        $sign = $this->type === TransactionType::Income ? '1' : '-1';

        return bcmul((string) $this->amount, $sign, 2);
    }
}
