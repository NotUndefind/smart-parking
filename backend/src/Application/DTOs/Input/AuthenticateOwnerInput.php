<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class AuthenticateOwnerInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}

