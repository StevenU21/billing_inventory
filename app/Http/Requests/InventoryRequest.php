<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Inventory::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('inventory'));
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $currencies = implode(',', ProductVariant::SUPPORTED_CURRENCIES);

        $rules = [
            'stock' => ['sometimes', 'numeric', 'min:0', 'max:99999999.9999'],
            'min_stock' => ['required', 'numeric', 'min:0', 'max:99999999.9999'],

            // Movement validation
            'movement_type' => ['nullable', 'string', Rule::enum(\App\Enums\InventoryMovementType::class)],
            'quantity' => ['required_with:movement_type', 'nullable', 'numeric', 'min:0.0001', 'max:99999999.9999'],
            'adjustment_reason' => [
                'nullable',
                Rule::requiredIf(fn() => in_array($this->movement_type, [
                    \App\Enums\InventoryMovementType::AdjustmentIn->value,
                    \App\Enums\InventoryMovementType::AdjustmentOut->value,
                ])),
                Rule::enum(\App\Enums\AdjustmentReason::class),
            ],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->isMethod('post')) {
            $rules['product_variant_id'] = [
                'required',
                'exists:product_variants,id',
                Rule::unique('inventories', 'product_variant_id'),
            ];
            $rules['currency'] = ['required', 'string', "in:$currencies"];
        } else {
            $rules['product_variant_id'] = ['sometimes', 'exists:product_variants,id'];
            $rules['currency'] = ['sometimes', 'string', "in:$currencies"];
        }

        return $rules;
    }
}
