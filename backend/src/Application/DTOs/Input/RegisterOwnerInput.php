<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class RegisterOwnerInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $companyName,
        public readonly string $firstName,
        public readonly string $lastName
    ) {
    }
}

