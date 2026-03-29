<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\RecurrenceSeries
 */
class RecurrenceSeriesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'financial_account_id' => $this->financial_account_id,
            'category_id' => $this->category_id,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'day_of_month' => $this->day_of_month,
            'start_on' => $this->start_on->toDateString(),
            'end_on' => $this->end_on?->toDateString(),
            'max_occurrences' => $this->max_occurrences,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'has_linked_debt' => $this->whenLoaded('debtAgreement', fn () => $this->debtAgreement !== null, false),
            'linked_debt_title' => $this->whenLoaded('debtAgreement', fn () => $this->debtAgreement?->title),
            'financial_account' => new FinancialAccountResource($this->whenLoaded('financialAccount')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
