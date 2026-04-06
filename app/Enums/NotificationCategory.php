<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Inventory = 'inventory';
    case System = 'system';
    case Quotations = 'quotations';

    public function label(): string
    {
        return match ($this) {
            self::Inventory => 'Inventario',
            self::System => 'Sistema',
            self::Quotations => 'Cotizaciones',
        };
    }
}
