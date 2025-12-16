<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class RegisterOwnerInput
{
    private function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $companyName,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}

    public static function create(
        string $email,
        string $password,
        string $companyName,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            email: $email,
            password: $password,
            companyName: $companyName,
            firstName: $firstName,
            lastName: $lastName,
        );
    }
}
