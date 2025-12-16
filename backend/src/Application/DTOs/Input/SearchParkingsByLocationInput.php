<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class SearchParkingsByLocationInput
{
    private function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly float $radiusKm = 5.0,
    ) {}

    public static function create(
        float $latitude,
        float $longitude,
        float $radiusKm = 5.0,
    ): self {
        return new self(
            latitude: $latitude,
            longitude: $longitude,
            radiusKm: $radiusKm,
        );
    }
}
