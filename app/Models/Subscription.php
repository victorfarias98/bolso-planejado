<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'gateway',
        'external_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActiveForDate($query, \Carbon\Carbon $date)
    {
        return $query
            ->where('status', SubscriptionStatus::Active)
            ->where(function ($q) use ($date): void {
                $q->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>=', $date);
            });
    }
}
