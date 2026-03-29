<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Plan
 */
class PlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'billing_mode' => $this->billing_mode->value,
            'price_cents' => $this->price_cents,
            'currency' => $this->currency,
            'interval' => $this->interval,
            'active' => $this->active,
        ];
    }
}
