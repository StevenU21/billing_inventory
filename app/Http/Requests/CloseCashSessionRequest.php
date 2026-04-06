<?php

namespace App\Http\Requests;

use App\Rules\NotesRequiredWhenCashDoesNotMatch;
use Illuminate\Foundation\Http\FormRequest;

class CloseCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('session');

        return $session && $this->user()->can('close', $session);
    }

    public function rules(): array
    {
        return [
            'actual_closing_balance' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'currency' => ['nullable', 'string', 'in:NIO,USD'],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
                new NotesRequiredWhenCashDoesNotMatch(
                    $this->route('session'),
                    $this->input('actual_closing_balance'),
                    $this->input('currency')
                ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'actual_closing_balance.required' => 'Debe ingresar el saldo real de cierre.',
            'actual_closing_balance.numeric' => 'El saldo de cierre debe ser un número.',
            'actual_closing_balance.min' => 'El saldo de cierre no puede ser negativo.',
        ];
    }
}
