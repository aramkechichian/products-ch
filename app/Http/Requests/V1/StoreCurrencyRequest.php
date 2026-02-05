<?php

namespace App\Http\Requests\V1;

class StoreCurrencyRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:currencies,name'],
            'symbol' => ['required', 'string', 'max:10', 'unique:currencies,symbol'],
            'exchange_rate' => ['required', 'numeric', 'min:0', 'max:999999.9999'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The currency name is required.',
            'name.unique' => 'A currency with this name already exists.',
            'symbol.required' => 'The currency symbol is required.',
            'symbol.unique' => 'A currency with this symbol already exists.',
            'exchange_rate.required' => 'The exchange rate is required.',
            'exchange_rate.numeric' => 'The exchange rate must be a number.',
            'exchange_rate.min' => 'The exchange rate must be greater than or equal to 0.',
        ];
    }
}
