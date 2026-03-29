<?php

namespace App\Models;

use App\Enums\InvestmentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'investment_type',
        'current_amount',
        'monthly_contribution',
        'monthly_return_rate',
        'contribution_day',
        'currency',
        'target_amount',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'investment_type' => InvestmentType::class,
            'current_amount' => 'decimal:2',
            'monthly_contribution' => 'decimal:2',
            'monthly_return_rate' => 'decimal:4',
            'target_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
