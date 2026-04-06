<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly class CompanyData
{
    public function __construct(
        public readonly string $name,
        public readonly string $address,
        public readonly string $email,
        public readonly ?string $ruc = null,
        public readonly ?string $description = null,
        public readonly ?string $phone = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            name: $validatedData['name'],
            address: $validatedData['address'],
            email: $validatedData['email'],
            ruc: $validatedData['ruc'] ?? null,
            description: $validatedData['description'] ?? null,
            phone: $validatedData['phone'] ?? null,
        );
    }
}
