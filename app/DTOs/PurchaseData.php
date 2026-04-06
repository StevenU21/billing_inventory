<?php

namespace App\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

readonly class PurchaseData
{
    /**
     * @param Collection<int, PurchaseItemData> $items
     */
    public function __construct(
        public readonly string $reference,
        public readonly CarbonImmutable $purchaseDate,
        public readonly string $currency,
        public readonly int $supplierId,
        public readonly int $paymentMethodId,
        public readonly Collection $items,
        public readonly ?bool $isCredit,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return self::fromArray($validatedData);
    }

    public static function fromArray(array $validatedData): self
    {
        $currency = $validatedData['currency'] ?? 'NIO';

        $items = collect($validatedData['details'])->map(function ($item) {
            return PurchaseItemData::fromArray($item);
        });

        $purchaseDate = isset($validatedData['purchase_date'])
            ? CarbonImmutable::parse($validatedData['purchase_date'])
            : CarbonImmutable::now();

        return new self(
            reference: (string) $validatedData['reference'],
            purchaseDate: $purchaseDate,
            currency: $currency,
            supplierId: (int) $validatedData['supplier_id'],
            paymentMethodId: (int) $validatedData['payment_method_id'],
            items: $items,
            isCredit: isset($validatedData['is_credit']) ? (bool) $validatedData['is_credit'] : null,
        );
    }
}
