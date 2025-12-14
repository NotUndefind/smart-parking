<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class EnterParkingInput
{
    public function __construct(
        public readonly string $userId,
        public readonly string $parkingId,
        public readonly ?string $reservationId = null,
        public readonly ?string $subscriptionId = null
    ) {
    }
}

