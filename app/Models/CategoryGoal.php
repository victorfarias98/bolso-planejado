<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryGoal extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'category_id',
        'monthly_limit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_limit' => 'decimal:2',
            'is_active' => 'boolean',
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
