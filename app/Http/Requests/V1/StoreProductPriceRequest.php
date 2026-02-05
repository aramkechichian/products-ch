<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;

class StoreProductPriceRequest extends BaseRequest
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
        $product = $this->route('product');
        $baseCurrencyId = $product ? $product->currency_id : null;
        
        $rules = [
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],
        ];
        
        // Evitar que se cree un precio en la misma moneda base del producto
        if ($baseCurrencyId) {
            $rules['currency_id'][] = Rule::notIn([$baseCurrencyId]);
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'currency_id.required' => 'The currency ID is required.',
            'currency_id.integer' => 'The currency ID must be an integer.',
            'currency_id.exists' => 'The selected currency does not exist.',
            'currency_id.not_in' => 'Cannot create a price in the same currency as the product base currency.',
        ];
    }
}
