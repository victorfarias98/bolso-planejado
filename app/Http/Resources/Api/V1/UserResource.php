<?php

namespace App\Http\Resources\Api\V1;

use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entitlements = app(EntitlementService::class);
        $plan = $entitlements->effectivePlan($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'billing' => [
                'plan' => [
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'billing_mode' => $plan->billing_mode->value,
                ],
                'entitlements' => $entitlements->entitlements($this->resource),
                'premium_expires_at' => $this->premium_expires_at?->toIso8601String(),
            ],
        ];
    }
}
