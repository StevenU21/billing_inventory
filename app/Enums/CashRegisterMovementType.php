<?php

namespace App\Enums;

enum CashRegisterMovementType: string
{
    // Movimientos Positivos (Entradas de efectivo)
    case Sale = 'sale';
    case Deposit = 'deposit';
    case AdjustmentIn = 'adjustment_in';
    case ReceivablePayment = 'receivable_payment'; // Cobro de Cuenta por Cobrar

    // Movimientos Negativos (Salidas de efectivo)
    case Refund = 'refund';
    case Withdrawal = 'withdrawal';
    case AdjustmentOut = 'adjustment_out';
    case Purchase = 'purchase'; // Pago de compra al contado

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Venta',
            self::Deposit => 'Depósito',
            self::AdjustmentIn => 'Ajuste de Entrada +',
            self::ReceivablePayment => 'Cobro CxC',
            self::Refund => 'Devolución',
            self::Withdrawal => 'Retiro',
            self::AdjustmentOut => 'Ajuste de Salida -',
            self::Purchase => 'Pago Compra',
        };
    }

    /**
     * Define el comportamiento matemático:
     * 1: Aumenta el balance de caja.
     * -1: Disminuye el balance de caja.
     */
    public function multiplier(): int
    {
        return match ($this) {
            self::Sale,
            self::Deposit,
            self::AdjustmentIn,
            self::ReceivablePayment => 1,

            self::Refund,
            self::Withdrawal,
            self::AdjustmentOut,
            self::Purchase => -1,
        };
    }

    public function isIncome(): bool
    {
        return $this->multiplier() === 1;
    }

    public function isExpense(): bool
    {
        return $this->multiplier() === -1;
    }

    public function color(): string
    {
        return match ($this) {
            self::Sale,
            self::Deposit,
            self::AdjustmentIn,
            self::ReceivablePayment => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',

            self::Refund,
            self::Withdrawal,
            self::AdjustmentOut,
            self::Purchase => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Sale,
            self::Deposit,
            self::AdjustmentIn,
            self::ReceivablePayment => 'green',

            self::Refund,
            self::Withdrawal,
            self::AdjustmentOut,
            self::Purchase => 'red',
        };
    }
}
