<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'category_id'           => ['required', 'exists:categories,id'],
            'name'                  => ['required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'base_price'            => ['required', 'numeric', 'min:0'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.sku'        => ['required', 'string', 'distinct', 'unique:product_variants,sku'],
            'variants.*.price'      => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.attributes' => ['required', 'array'],
        ];
    }
}
