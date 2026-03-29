<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PlanHistory
 */
class PlanHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'cut_amount' => $this->cut_amount,
            'extra_investment' => $this->extra_investment,
            'debt_share_pct' => $this->debt_share_pct,
            'simulated_debt_payment' => $this->simulated_debt_payment,
            'simulated_investment_total' => $this->simulated_investment_total,
            'simulated_debt_payoff_months' => $this->simulated_debt_payoff_months,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
