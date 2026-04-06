<?php

namespace App\DTOs;

use Deifhelt\ActivityPresenter\Data\ActivityPresentationDTO;

final class AuditIndexRowData
{
    public function __construct(
        public readonly int $id,
        public readonly string $userName,
        public readonly string $event,
        public readonly string $subjectType,
        public readonly string $subjectName,
        public readonly string $date,
        public readonly int $count,
        public readonly ?string $showUrl,
        public readonly ?string $exportUrl = null,
    ) {
    }
}
