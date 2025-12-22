<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class SubscribeToPlanInput
{
    private function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly string $type,
        public readonly int $startDate,
        public readonly ?int $endDate = null,
        public readonly float $price = 0.0,
    ) {}

    public static function create(
        string $userId,
        string $parkingId,
        string $type,
        int $startDate,
        ?int $endDate = null,
        float $price = 0.0,
    ): self {
        return new self(
            userId: $userId,
            parkingId: $parkingId,
            type: $type,
            startDate: $startDate,
            endDate: $endDate,
            price: $price,
        );
    }
}
