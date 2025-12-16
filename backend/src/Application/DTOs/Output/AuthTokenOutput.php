<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class AuthTokenOutput
{
    public function __construct(
        public readonly string $token,
        public readonly string $type,
        public readonly int $expiresIn,
        public readonly UserOutput $user
    ) {
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'type' => $this->type,
            'expires_in' => $this->expiresIn,
            'user' => $this->user->toArray(),
        ];
    }
}

