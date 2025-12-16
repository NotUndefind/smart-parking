<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class RegisterUserInput
{
    private function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}

    public static function create(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            email: $email,
            password: $password,
            firstName: $firstName,
            lastName: $lastName,
        );
    }
}
