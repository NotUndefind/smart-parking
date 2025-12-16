<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class SearchParkingsByLocationInput
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $radiusKm = 5.0
    ) {
    }
}

