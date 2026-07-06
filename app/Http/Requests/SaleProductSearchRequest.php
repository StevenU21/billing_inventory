<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleProductSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'page' => ['nullable', 'integer', 'min:1'],
            'filter' => ['nullable', 'array'],
            'filter.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'filter.brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        ];
    }
}
