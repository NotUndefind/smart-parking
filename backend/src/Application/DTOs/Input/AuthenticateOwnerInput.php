<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class AuthenticateOwnerInput
{
    private function __construct(
        public readonly string $email,
        public readonly string $passwordhash,
    ) {}

    public static function create(string $email, string $passwordhash): self
    {
        return new self(email: $email, passwordhash: $passwordhash);
    }
}
