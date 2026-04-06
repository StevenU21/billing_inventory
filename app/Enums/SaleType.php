<?php

namespace App\Enums;

enum SaleType: string
{
    case Cash = 'cash';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Contado',
            self::Credit => 'Crédito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cash => 'green',
            self::Credit => 'gray',  // Neutral para crédito
        };
    }

    public static function fromBoolean(bool $isCredit): self
    {
        return $isCredit ? self::Credit : self::Cash;
    }
}
