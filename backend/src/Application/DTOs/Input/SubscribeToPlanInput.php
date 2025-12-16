<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class SubscribeToPlanInput
{
    private function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly string $type,
        public readonly float $price,
    ) {}

    public static function create(
        string $userId,
        string $parkingId,
        string $type,
        float $price,
    ): self {
        return new self(
            userId: $userId,
            parkingId: $parkingId,
            type: $type,
            price: $price,
        );
    }
}
