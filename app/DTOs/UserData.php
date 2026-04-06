<?php

namespace App\DTOs;

readonly class UserData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly bool $isActive,
        public readonly string $role,
        public readonly ?string $password = null,
    ) {
    }

    public static function fromRequest(array $validatedData): self
    {
        return new self(
            firstName: $validatedData['first_name'],
            lastName: $validatedData['last_name'],
            email: $validatedData['email'],
            isActive: (bool) ($validatedData['is_active'] ?? true),
            role: $validatedData['role'],
            password: $validatedData['password'] ?? null,
        );
    }
}
