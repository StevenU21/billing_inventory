<?php

namespace App\Http\Requests;

use App\Enums\CashRegisterMovementType;
use App\Models\CashRegisterSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        if (! $session) {
            return false;
        }

        // Check specific permission based on movement type
        $type = $this->input('type');

        if (in_array($type, ['deposit', 'adjustment_in'])) {
            return $this->user()->can('deposit', CashRegisterSession::class);
        }

        if (in_array($type, ['withdrawal', 'adjustment_out'])) {
            return $this->user()->can('withdraw', CashRegisterSession::class);
        }

        // For other movements, check general record permission
        return $this->user()->can('recordMovement', $session);
    }

    public function rules(): array
    {
        // Solo permitir tipos de movimientos manuales
        $manualTypes = [
            CashRegisterMovementType::Deposit->value,
            CashRegisterMovementType::Withdrawal->value,
            CashRegisterMovementType::AdjustmentIn->value,
            CashRegisterMovementType::AdjustmentOut->value,
        ];

        return [
            'type' => ['required', 'string', Rule::in($manualTypes)],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'in:NIO,USD'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'reference_type' => ['nullable', 'string', 'max:255'],
            'reference_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Debe especificar el tipo de movimiento.',
            'type.in' => 'El tipo de movimiento no es válido.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.gt' => 'El monto debe ser mayor a cero.',
            'amount.max' => 'El monto excede el máximo permitido.',
        ];
    }
}
