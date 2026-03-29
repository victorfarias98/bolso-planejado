<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\InvestmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'investment_type' => ['required', Rule::enum(InvestmentType::class)],
            'current_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_contribution' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_return_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,4'],
            'contribution_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'currency' => ['nullable', 'string', 'size:3'],
            'target_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
