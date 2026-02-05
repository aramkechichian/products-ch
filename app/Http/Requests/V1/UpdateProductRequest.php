<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends BaseRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency_id' => ['sometimes', 'required', 'integer', 'exists:currencies,id'],
            'tax_cost' => ['sometimes', 'numeric', 'min:0'],
            'manufacturing_cost' => ['sometimes', 'numeric', 'min:0'],
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
            'name.required' => 'The product name is required.',
            'description.required' => 'The product description is required.',
            'price.required' => 'The product price is required.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be greater than or equal to 0.',
            'currency_id.required' => 'The currency is required.',
            'currency_id.exists' => 'The selected currency does not exist.',
            'tax_cost.numeric' => 'The tax cost must be a number.',
            'tax_cost.min' => 'The tax cost must be greater than or equal to 0.',
            'manufacturing_cost.numeric' => 'The manufacturing cost must be a number.',
            'manufacturing_cost.min' => 'The manufacturing cost must be greater than or equal to 0.',
        ];
    }
}
