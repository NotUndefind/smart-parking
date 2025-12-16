<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class AuthenticateUserInput
{
    private function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function create(string $email, string $password): self
    {
        return new self(email: $email, password: $password);
    }
}
