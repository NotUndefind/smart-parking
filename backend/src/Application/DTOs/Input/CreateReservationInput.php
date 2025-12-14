<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class CreateReservationInput
{
    public function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly int $startTime,
        public readonly int $endTime
    ) {
    }
}

