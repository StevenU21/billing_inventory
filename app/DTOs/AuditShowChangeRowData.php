<?php

namespace App\DTOs;

final class AuditShowChangeRowData
{
    public function __construct(
        public readonly string $label,
        public readonly string $old,
        public readonly string $new,
    ) {
    }
}
