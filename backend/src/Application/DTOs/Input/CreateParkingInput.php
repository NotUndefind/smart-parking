<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class CreateParkingInput
{
    private function __construct(
        public readonly string $ownerId,
        public readonly string $name,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly int $totalSpots,
        public readonly array $tariffs = [],
        public readonly array $schedule = [],
    ) {}

    public static function create(
        string $ownerId,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
        int $totalSpots,
        array $tariffs = [],
        array $schedule = [],
    ): self {
        return new self(
            ownerId: $ownerId,
            name: $name,
            address: $address,
            latitude: $latitude,
            longitude: $longitude,
            totalSpots: $totalSpots,
            tariffs: $tariffs,
            schedule: $schedule,
        );
    }
}
