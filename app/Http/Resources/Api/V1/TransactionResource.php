<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Transaction
 */
class TransactionResource extends JsonResource
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
            'recurrence_series_id' => $this->recurrence_series_id,
            'type' => $this->type->value,
            'amount' => $this->amount,
            'occurred_on' => $this->occurred_on->toDateString(),
            'status' => $this->status->value,
            'description' => $this->description,
            'financial_account' => new FinancialAccountResource($this->whenLoaded('financialAccount')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
