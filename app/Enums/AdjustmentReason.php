<?php

namespace App\Enums;

enum AdjustmentReason: string
{
    case Correction = 'correction';
    case PhysicalCount = 'physical_count';
    case Damage = 'damage';
    case Theft = 'theft';
    case Expiration = 'expiration';
    case Production = 'production';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::Correction => 'Corrección',
            self::PhysicalCount => 'Conteo Físico',
            self::Damage => 'Daño',
            self::Theft => 'Robo',
            self::Expiration => 'Vencimiento',
            self::Production => 'Producción',
            self::Return => 'Devolución',
        };
    }
}
