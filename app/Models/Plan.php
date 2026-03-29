<?php

namespace App\Models;

use App\Enums\PlanBillingMode;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'billing_mode',
        'price_cents',
        'currency',
        'interval',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'billing_mode' => PlanBillingMode::class,
            'active' => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_feature')
            ->withPivot(['enabled', 'limit_int'])
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
