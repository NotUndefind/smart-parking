<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class OwnerOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $companyName,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $fullName,
        public readonly string $createdAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'company_name' => $this->companyName,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->fullName,
            'created_at' => $this->createdAt,
        ];
    }
}

