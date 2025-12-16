<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class CreateReservationInput
{
    private function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly int $startTime,
        public readonly int $endTime,
    ) {}

    public static function create(
        string $userId,
        string $parkingId,
        int $startTime,
        int $endTime,
    ): self {
        return new self(
            userId: $userId,
            parkingId: $parkingId,
            startTime: $startTime,
            endTime: $endTime,
        );
    }
}
