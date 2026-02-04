<?php

namespace App\Http\Requests\V1;

use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends BaseRequest
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
        $currencyId = $this->route('currency');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('currencies', 'name')->ignore($currencyId)],
            'symbol' => ['sometimes', 'required', 'string', 'max:10', Rule::unique('currencies', 'symbol')->ignore($currencyId)],
            'exchange_rate' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.9999'],
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
            'name.required' => 'El nombre de la moneda es obligatorio.',
            'name.unique' => 'Ya existe una moneda con este nombre.',
            'symbol.required' => 'El símbolo de la moneda es obligatorio.',
            'symbol.unique' => 'Ya existe una moneda con este símbolo.',
            'exchange_rate.required' => 'La tasa de cambio es obligatoria.',
            'exchange_rate.numeric' => 'La tasa de cambio debe ser un número.',
            'exchange_rate.min' => 'La tasa de cambio debe ser mayor o igual a 0.',
        ];
    }
}
