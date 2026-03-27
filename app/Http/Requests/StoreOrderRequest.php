<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64'],
            'qty' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU є обов\'язковим полем.',
            'sku.string' => 'SKU має бути рядком.',
            'sku.max' => 'SKU не може бути довшим за 64 символи.',
            'qty.required' => 'Кількість є обов\'язковим полем.',
            'qty.integer' => 'Кількість має бути цілим числом.',
            'qty.min' => 'Кількість має бути не менше 1.',
            'qty.max' => 'Кількість не може перевищувати 10 000.',
        ];
    }
}
