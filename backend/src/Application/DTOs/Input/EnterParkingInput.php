<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class EnterParkingInput
{
    private function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly ?string $reservationId = null,
        public readonly ?string $subscriptionId = null,
    ) {}

    public static function create(
        string $userId,
        string $parkingId,
        ?string $reservationId = null,
        ?string $subscriptionId = null,
    ): self {
        return new self(
            userId: $userId,
            parkingId: $parkingId,
            reservationId: $reservationId,
            subscriptionId: $subscriptionId,
        );
    }
}
