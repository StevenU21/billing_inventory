<?php

namespace App\Http\Requests;

use App\Models\CashRegisterSession;
use Illuminate\Foundation\Http\FormRequest;

class OpenCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('open', CashRegisterSession::class);
    }

    public function rules(): array
    {
        return [
            'opening_balance' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'in:NIO,USD'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'opening_balance.required' => 'El saldo inicial es obligatorio.',
            'opening_balance.numeric' => 'El saldo inicial debe ser un número.',
            'opening_balance.min' => 'El saldo inicial no puede ser negativo.',
            'opening_balance.max' => 'El saldo inicial excede el máximo permitido.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $existingSession = \App\Models\CashRegisterSession::where('user_id', $this->user()->id)
                ->where('status', \App\Enums\CashRegisterSessionStatus::Open)
                ->exists();

            if ($existingSession) {
                $validator->errors()->add(
                    'session',
                    'Ya tienes una sesión de caja abierta. Debes cerrar la sesión actual antes de abrir una nueva.'
                );
            }
        });
    }
}
