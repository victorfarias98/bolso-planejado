<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecurrenceSeries extends Model
{
    use HasUuids;

    protected $table = 'recurrence_series';

    protected $fillable = [
        'user_id',
        'financial_account_id',
        'category_id',
        'type',
        'amount',
        'day_of_month',
        'start_on',
        'end_on',
        'max_occurrences',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'start_on' => 'date',
            'end_on' => 'date',
            'is_active' => 'boolean',
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

    public function debtAgreement(): HasOne
    {
        return $this->hasOne(Debt::class, 'recurrence_series_id');
    }

    public function signedAmount(): string
    {
        $sign = $this->type === TransactionType::Income ? '1' : '-1';

        return bcmul((string) $this->amount, $sign, 2);
    }
}
