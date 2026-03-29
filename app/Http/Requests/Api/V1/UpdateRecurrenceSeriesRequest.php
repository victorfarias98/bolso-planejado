<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecurrenceSeriesRequest extends FormRequest
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
            'day_of_month' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'start_on' => ['sometimes', 'date'],
            'end_on' => ['nullable', 'date'],
            'max_occurrences' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
