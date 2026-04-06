<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use Carbon\CarbonImmutable;

readonly class QuotationData
{
    /**
     * @param Collection<int, QuotationItemData> $items
     */
    public function __construct(
        public int $clientId,
        public int $userId,
        public string $currency,
        public ?CarbonImmutable $validUntil,
        public Collection $items,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $items = collect($validatedData['items'])->map(function ($item) {
            return QuotationItemData::fromArray($item);
        });

        return new self(
            clientId: (int) $validatedData['client_id'],
            userId: (int) $validatedData['user_id'],
            currency: $validatedData['currency'] ?? 'NIO',
            validUntil: null, // Always null from request, let Service calculate it
            items: $items,
        );
    }
}
