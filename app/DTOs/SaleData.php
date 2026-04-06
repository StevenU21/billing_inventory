<?php

namespace App\DTOs;

use Illuminate\Support\Collection;
use Carbon\CarbonImmutable;

readonly class SaleData
{
    public function __construct(
        public readonly ?int $userId,
        public readonly int $clientId,
        public readonly bool $isCredit,
        public readonly string $currency,
        public readonly CarbonImmutable $saleDate,
        public readonly ?int $paymentMethodId,
        public Collection $items,
        public ?int $quotationId = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        $currency = $validatedData['currency'] ?? 'NIO';

        $items = collect($validatedData['items'])->map(function ($item) {
            return SaleItemData::fromArray($item);
        });

        $saleDate = isset($validatedData['sale_date'])
            ? CarbonImmutable::parse($validatedData['sale_date'])
            : CarbonImmutable::now();

        return new self(
            userId: (int) $validatedData['user_id'],
            clientId: (int) $validatedData['client_id'],
            isCredit: (bool) ($validatedData['is_credit'] ?? false),
            currency: $currency,
            saleDate: $saleDate,
            paymentMethodId: $validatedData['payment_method_id'] ?? null,
            items: $items,
            quotationId: $validatedData['quotation_id'] ?? null,
        );
    }
}
