<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Debt
 */
class DebtResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $agreementIsFinalized = false;
        if ($this->agreement_end_on) {
            $agreementIsFinalized = $this->agreement_end_on->toDateString() <= Carbon::today()->toDateString();
        }

        $scheduledTotal = null;
        if ($this->agreement_installment_count !== null && $this->agreement_installment_amount !== null) {
            $scheduledTotal = bcmul(
                (string) $this->agreement_installment_amount,
                (string) $this->agreement_installment_count,
                2
            );
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'creditor' => $this->creditor,
            'debt_type' => $this->debt_type->value,
            'principal_amount' => $this->principal_amount,
            'balance_amount' => $this->balance_amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'financial_account_id' => $this->financial_account_id,
            'category_id' => $this->category_id,
            'agreement_date' => $this->agreement_date?->toDateString(),
            'agreement_formalized_on' => $this->agreement_formalized_on?->toDateString(),
            'agreement_end_on' => $this->agreement_end_on?->toDateString(),
            'agreement_fulfilled' => $agreementIsFinalized,
            'agreement_is_finalized' => $agreementIsFinalized,
            'agreement_day_of_month' => $this->agreement_day_of_month,
            'agreement_first_due_date' => $this->agreement_first_due_date?->toDateString(),
            'agreement_installment_count' => $this->agreement_installment_count,
            'agreement_installment_amount' => $this->agreement_installment_amount,
            'agreement_down_payment' => $this->agreement_down_payment,
            'agreement_notes' => $this->agreement_notes,
            'agreement_scheduled_total' => $scheduledTotal,
            'recurrence_series_id' => $this->recurrence_series_id,
            'has_recurrence' => $this->recurrence_series_id !== null,
            'financial_account' => new FinancialAccountResource($this->whenLoaded('financialAccount')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'recurrence_series' => $this->when(
                $this->relationLoaded('recurrenceSeries') && $this->recurrenceSeries,
                fn () => new RecurrenceSeriesResource($this->recurrenceSeries)
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
