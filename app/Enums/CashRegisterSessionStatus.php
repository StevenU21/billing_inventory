<?php

namespace App\Enums;

enum CashRegisterSessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::Closed => 'Cerrada',
            self::Suspended => 'Suspendida',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'gray',
            self::Suspended => 'warning',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Open => 'green',
            self::Closed => 'gray',
            self::Suspended => 'yellow',
        };
    }
}
