<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialAccountRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'initial_balance' => ['sometimes', 'numeric', 'decimal:0,2'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}
