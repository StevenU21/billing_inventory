<?php

namespace App\DTOs;

readonly class ProfileData
{
    public function __construct(
        public readonly ?string $phone = null,
        public readonly ?string $identityCard = null,
        public readonly ?string $gender = null,
        public readonly ?string $address = null,
        public readonly ?string $avatar = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            phone: $validatedData['phone'] ?? null,
            identityCard: $validatedData['identity_card'] ?? null,
            gender: $validatedData['gender'] ?? null,
            address: $validatedData['address'] ?? null,
            avatar: $validatedData['avatar'] ?? null,
        );
    }
}
