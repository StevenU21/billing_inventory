<?php

namespace App\Http\Requests;

use App\DTOs\InventoryMovementData;
use App\Enums\AdjustmentReason;
use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', InventoryMovement::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('inventory_movement'));
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

        return [
            'inventory_id' => ['required', 'exists:inventories,id'],
            'type' => ['required', Rule::enum(InventoryMovementType::class)],

            'adjustment_reason' => [
                Rule::requiredIf(fn() => in_array($this->input('type'), [
                    InventoryMovementType::AdjustmentIn->value,
                    InventoryMovementType::AdjustmentOut->value
                ])),
                Rule::enum(AdjustmentReason::class),
                'nullable',
            ],

            'quantity' => ['required', 'numeric', 'min:0.0001', 'max:99999999.9999'],

            'unit_price' => [
                Rule::requiredIf(fn() => in_array($this->input('type'), [
                    InventoryMovementType::Purchase->value,
                    InventoryMovementType::AdjustmentIn->value
                ])),
                'nullable',
                'numeric',
                'min:0'
            ],

            'currency' => ['required', 'string', "in:$currencies"],

            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255']
        ];
    }

    public function toDto(): InventoryMovementData
    {
        return InventoryMovementData::fromRequest($this->validated());
    }
}