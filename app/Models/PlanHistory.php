<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'category_id',
        'cut_amount',
        'extra_investment',
        'debt_share_pct',
        'simulated_debt_payment',
        'simulated_investment_total',
        'simulated_debt_payoff_months',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'cut_amount' => 'decimal:2',
            'extra_investment' => 'decimal:2',
            'debt_share_pct' => 'decimal:2',
            'simulated_debt_payment' => 'decimal:2',
            'simulated_investment_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
