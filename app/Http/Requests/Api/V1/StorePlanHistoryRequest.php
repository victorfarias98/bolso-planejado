<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'cut_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'extra_investment' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'debt_share_pct' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
            'simulated_debt_payment' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'simulated_investment_total' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'simulated_debt_payoff_months' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:200'],
        ];
    }
}
