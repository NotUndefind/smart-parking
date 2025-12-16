<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class CreateParkingInput
{
    public function __construct(
        public readonly string $ownerId,
        public readonly string $name,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly int $totalSpots,
        public readonly array $tariffs = [],
        public readonly array $schedule = []
    ) {
    }
}

