<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;

class SearchProductRequest extends BaseRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'currency_symbol' => ['sometimes', 'string', 'exists:currencies,symbol'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'min_tax_cost' => ['sometimes', 'numeric', 'min:0'],
            'max_tax_cost' => ['sometimes', 'numeric', 'min:0', 'gte:min_tax_cost'],
            'min_manufacturing_cost' => ['sometimes', 'numeric', 'min:0'],
            'max_manufacturing_cost' => ['sometimes', 'numeric', 'min:0', 'gte:min_manufacturing_cost'],
            'sort_by' => ['sometimes', 'string', Rule::in(['name', 'price', 'tax_cost', 'manufacturing_cost', 'created_at', 'updated_at'])],
            'sort_order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
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
            'currency_symbol.exists' => 'The selected currency symbol does not exist.',
            'min_price.numeric' => 'The minimum price must be a number.',
            'min_price.min' => 'The minimum price must be greater than or equal to 0.',
            'max_price.numeric' => 'The maximum price must be a number.',
            'max_price.min' => 'The maximum price must be greater than or equal to 0.',
            'max_price.gte' => 'The maximum price must be greater than or equal to the minimum price.',
            'min_tax_cost.numeric' => 'The minimum tax cost must be a number.',
            'min_tax_cost.min' => 'The minimum tax cost must be greater than or equal to 0.',
            'max_tax_cost.numeric' => 'The maximum tax cost must be a number.',
            'max_tax_cost.min' => 'The maximum tax cost must be greater than or equal to 0.',
            'max_tax_cost.gte' => 'The maximum tax cost must be greater than or equal to the minimum tax cost.',
            'min_manufacturing_cost.numeric' => 'The minimum manufacturing cost must be a number.',
            'min_manufacturing_cost.min' => 'The minimum manufacturing cost must be greater than or equal to 0.',
            'max_manufacturing_cost.numeric' => 'The maximum manufacturing cost must be a number.',
            'max_manufacturing_cost.min' => 'The maximum manufacturing cost must be greater than or equal to 0.',
            'max_manufacturing_cost.gte' => 'The maximum manufacturing cost must be greater than or equal to the minimum manufacturing cost.',
            'sort_by.in' => 'The sort_by field must be one of: name, price, tax_cost, manufacturing_cost, created_at, updated_at.',
            'sort_order.in' => 'The sort_order field must be either asc or desc.',
            'per_page.min' => 'The per_page must be at least 1.',
            'per_page.max' => 'The per_page may not be greater than 100.',
            'page.min' => 'The page must be at least 1.',
        ];
    }
}
