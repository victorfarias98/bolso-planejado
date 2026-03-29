<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
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
                'required',
                'uuid',
                Rule::exists('financial_accounts', 'id')->where('user_id', $this->user()->id),
            ],
            'category_id' => [
                'nullable',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'occurred_on' => ['required', 'date'],
            'status' => ['required', Rule::enum(TransactionStatus::class)],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
