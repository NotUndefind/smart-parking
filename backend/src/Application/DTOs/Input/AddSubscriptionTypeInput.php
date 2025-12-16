<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class AddSubscriptionTypeInput
{
    private function __construct(
        public readonly string $parkingId,
        public readonly string $type,
        public readonly float $price,
        public readonly int $durationDays,
    ) {}

    public static function create(
        string $parkingId,
        string $type,
        float $price,
        int $durationDays,
    ): self {
        return new self(
            parkingId: $parkingId,
            type: $type,
            price: $price,
            durationDays: $durationDays,
        );
    }
}
