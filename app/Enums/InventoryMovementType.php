<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    // Movimientos Positivos (Entradas)
    case Purchase = 'purchase';
    case SaleReturn = 'sale_return';
    case SaleCancellation = 'sale_cancellation';
    case AdjustmentIn = 'adjustment_in';

    // Movimientos Negativos (Salidas)
    case Sale = 'sale';
    case PurchaseReturn = 'purchase_return';
    case AdjustmentOut = 'adjustment_out';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Compra',
            self::SaleReturn => 'Devolución de Cliente',
            self::SaleCancellation => 'Anulación de Venta',
            self::AdjustmentIn => 'Ajuste de Entrada +',
            self::Sale => 'Venta',
            self::PurchaseReturn => 'Devolución a Proveedor',
            self::AdjustmentOut => 'Ajuste de Salida -',
        };
    }

    /**
     * Define el comportamiento matemático:
     * 1: Aumenta el stock.
     * -1: Disminuye el stock.
     * Esto elimina la necesidad de if/else en el Servicio.
     */
    public function multiplier(): int
    {
        return match ($this) {
            self::Purchase,
            self::SaleReturn,
            self::SaleCancellation,
            self::AdjustmentIn => 1,

            self::Sale,
            self::PurchaseReturn,
            self::AdjustmentOut => -1,
        };
    }

    /**
     * Get badge color classes for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::Purchase,
            self::SaleReturn,
            self::SaleCancellation,
            self::AdjustmentIn => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',

            self::Sale,
            self::PurchaseReturn,
            self::AdjustmentOut => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        };
    }

    /**
     * Get simple color name for Badge component
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Purchase,
            self::SaleReturn,
            self::SaleCancellation,
            self::AdjustmentIn => 'green',

            self::Sale,
            self::PurchaseReturn,
            self::AdjustmentOut => 'red',
        };
    }
}