<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = "draft";
    case Available = 'available';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Available => 'Disponible',
            self::Archived => 'Archivado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Available => 'green',
            self::Archived => 'red',
        };
    }
}
