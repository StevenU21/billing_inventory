<?php

namespace App\Enums;

enum AccountPayableStatus: string
{
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::PartiallyPaid => 'Parcialmente Pagado',
            self::Paid => 'Pagado',
            self::Voided => 'Anulado',
        };
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'red',
            self::PartiallyPaid => 'yellow',
            self::Paid => 'green',
            self::Voided => 'gray',
        };
    }
}
