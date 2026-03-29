<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'financial_account_id' => [
                'sometimes',
                'uuid',
                Rule::exists('financial_accounts', 'id')->where('user_id', $this->user()->id),
            ],
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'type' => ['sometimes', Rule::enum(TransactionType::class)],
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'decimal:0,2'],
            'occurred_on' => ['sometimes', 'date'],
            'status' => ['sometimes', Rule::enum(TransactionStatus::class)],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
