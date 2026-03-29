<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\InvestmentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:160'],
            'investment_type' => ['sometimes', Rule::enum(InvestmentType::class)],
            'current_amount' => ['sometimes', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_contribution' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'monthly_return_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'decimal:0,4'],
            'contribution_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'target_amount' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
