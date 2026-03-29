<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Investment
 */
class InvestmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'investment_type' => $this->investment_type->value,
            'current_amount' => $this->current_amount,
            'monthly_contribution' => $this->monthly_contribution,
            'monthly_return_rate' => $this->monthly_return_rate,
            'contribution_day' => $this->contribution_day,
            'currency' => $this->currency,
            'target_amount' => $this->target_amount,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            // Primeiro mês do mesmo modelo de juros compostos da projeção 12m: (saldo + aporte) × taxa
            'estimated_monthly_yield' => $this->estimatedMonthlyCompoundYield(),
            'projected_12m' => $this->projected12mValue(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function estimatedMonthlyCompoundYield(): string
    {
        $base = bcadd(
            (string) $this->current_amount,
            (string) ($this->monthly_contribution ?? '0'),
            2
        );
        $rate = bcdiv((string) ($this->monthly_return_rate ?? '0'), '100', 8);

        return bcmul($base, $rate, 2);
    }

    private function projected12mValue(): string
    {
        $value = (float) $this->current_amount;
        $contrib = (float) ($this->monthly_contribution ?? 0);
        $rate = ((float) ($this->monthly_return_rate ?? 0)) / 100;

        for ($i = 0; $i < 12; $i++) {
            $value += $contrib;
            $value *= (1 + $rate);
        }

        return number_format($value, 2, '.', '');
    }
}
