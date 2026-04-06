<?php

namespace App\DTOs;

readonly class EntityData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $identityCard,
        public readonly int $municipalityId,
        public readonly string $phone,
        public readonly bool $isClient,
        public readonly bool $isSupplier,
        public readonly bool $isActive,
        public readonly ?string $ruc = null,
        public readonly ?string $email = null,
        public readonly ?string $address = null,
        public readonly ?string $description = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            firstName: $validatedData['first_name'],
            lastName: $validatedData['last_name'],
            identityCard: $validatedData['identity_card'],
            municipalityId: (int) $validatedData['municipality_id'],
            phone: $validatedData['phone'],
            isClient: (bool) $validatedData['is_client'],
            isSupplier: (bool) $validatedData['is_supplier'],
            isActive: (bool) $validatedData['is_active'],
            ruc: $validatedData['ruc'] ?? null,
            email: $validatedData['email'] ?? null,
            address: $validatedData['address'] ?? null,
            description: $validatedData['description'] ?? null,
        );
    }
}
