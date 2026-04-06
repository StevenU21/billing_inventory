<?php

namespace App\Http\Requests;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Quotation;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->isMethod('post') ? $this->user()->can('create', Quotation::class) : false;
    }

    public function rules(): array
    {
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        return [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('entities', 'id')->where(function ($query) {
                    return $query->where('is_client', true);
                }),
            ],
            'currency' => ['nullable', 'string', "in:$currencies"],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999.9999', new \App\Rules\FractionalQuantity],

            'items.*.discount' => ['nullable', 'boolean'],
            'items.*.discount_percentage' => [
                'nullable',
                'required_if:items.*.discount,true',
                'numeric',
                'min:0',
                'max:100'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'El cliente es obligatorio.',
            'items.required' => 'Debe agregar al menos un producto a la cotización.',
        ];
    }
}
