<?php

namespace App\Rules;

use App\Models\CashRegisterSession;
use Brick\Money\Money;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NotesRequiredWhenCashDoesNotMatch implements ValidationRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    public function __construct(
        private readonly ?CashRegisterSession $session,
        private readonly mixed $actualClosingBalance,
        private readonly ?string $currency
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->session instanceof CashRegisterSession) {
            return;
        }

        if (! is_numeric($this->actualClosingBalance)) {
            return;
        }

        $currency = $this->currency ?? $this->session->currency ?? 'NIO';
        $actualMoney = Money::of((string) $this->actualClosingBalance, $currency);

        if ($actualMoney->isEqualTo($this->session->expected_closing_balance)) {
            return;
        }

        $notes = trim((string) $value);

        if ($notes === '') {
            $fail('Debe indicar un motivo o detalle cuando el cierre no cuadra.');
        }
    }
}
