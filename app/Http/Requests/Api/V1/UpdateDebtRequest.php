<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'creditor' => ['nullable', 'string', 'max:200'],
            'debt_type' => ['sometimes', Rule::enum(DebtType::class)],
            'principal_amount' => ['sometimes', 'numeric', 'min:0.01', 'decimal:0,2'],
            'balance_amount' => ['sometimes', 'numeric', 'min:0', 'decimal:0,2'],
            'currency' => ['nullable', 'string', 'size:3'],
            'status' => ['sometimes', Rule::enum(DebtStatus::class)],
            'financial_account_id' => [
                'nullable',
                'uuid',
                Rule::exists('financial_accounts', 'id')->where('user_id', $this->user()->id),
            ],
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'agreement_date' => ['nullable', 'date'],
            'agreement_formalized_on' => ['nullable', 'date'],
            'agreement_end_on' => ['nullable', 'date', 'after_or_equal:agreement_formalized_on'],
            'agreement_fulfilled' => ['sometimes', 'boolean'],
            'agreement_first_due_date' => ['nullable', 'date'],
            'agreement_installment_count' => ['nullable', 'integer', 'min:1', 'max:600'],
            'agreement_installment_amount' => ['nullable', 'numeric', 'min:0.01', 'decimal:0,2'],
            'agreement_down_payment' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'agreement_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
