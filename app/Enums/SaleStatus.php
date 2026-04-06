<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
            self::Refunded => 'Reembolsada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Refunded => 'warning',
        };
    }
}
