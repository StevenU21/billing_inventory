<?php

namespace App\DTOs;

final class AuditShowActivityData
{
    /**
     * @param array<int, AuditShowChangeRowData> $changes
     */
    public function __construct(
        public readonly int $id,
        public readonly string $event,
        public readonly string $userName,
        public readonly string $date,
        public readonly array $changes,
    ) {
    }
}
