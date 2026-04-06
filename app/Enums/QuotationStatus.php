<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Accepted => 'Aceptada',
            self::Rejected => 'Rechazada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Accepted => 'green',
            self::Rejected => 'red',
        };
    }
}
