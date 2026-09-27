<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'category_id'           => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name'                  => ['sometimes', 'required', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'base_price'            => ['sometimes', 'required', 'numeric', 'min:0'],
            'variants'              => ['sometimes', 'array', 'min:1'],
            'variants.*.id'         => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.sku'        => ['required_with:variants', 'string', 'distinct'],
            'variants.*.price'      => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.attributes' => ['required_with:variants', 'array'],

            //ចំណាំ: យើងប្រើ sometimes ដើម្បីឱ្យ Admin អាចកែប្រែតែមួយ field ក៏បាន (ឧ. កែតែ base_price ឬ name) ដោយមិនបាច់ផ្ញើមកទាំងអស់។
        ];
    }
}
