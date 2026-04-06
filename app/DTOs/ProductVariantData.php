<?php

namespace App\DTOs;

use Brick\Money\Money;
use Illuminate\Http\UploadedFile;

readonly class ProductVariantData
{
    public function __construct(
        public ?int $id,
        public ?string $sku,
        public ?string $barcode,
        public Money $price,
        public ?Money $creditPrice,
        public string $currency,
        public ?UploadedFile $image,

        public array $attributes = [],
    ) {}

    public static function fromArray(array $data, ?string $defaultCurrency = null): self
    {
        $currency = $data['currency'] ?? $defaultCurrency ?? 'NIO';

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            sku: isset($data['sku']) ? (string) $data['sku'] : null,
            barcode: $data['barcode'] ?? null,
            price: Money::of((string) $data['price'], $currency),
            creditPrice: isset($data['credit_price']) ? Money::of((string) $data['credit_price'], $currency) : null,
            currency: $currency,
            image: $data['image'] ?? null,

            attributes: $data['attributes'] ?? [],
        );
    }
}
