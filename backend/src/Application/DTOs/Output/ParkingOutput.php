<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class ParkingOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly int $totalSpots,
        public readonly array $tariffs,
        public readonly array $schedule,
        public readonly ?float $distanceKm = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'total_spots' => $this->totalSpots,
            'tariffs' => $this->tariffs,
            'schedule' => $this->schedule,
            'distance_km' => $this->distanceKm,
        ];
    }
}

