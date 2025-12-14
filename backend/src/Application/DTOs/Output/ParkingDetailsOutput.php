<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class ParkingDetailsOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly int $totalSpots,
        public readonly int $availableSpots,
        public readonly array $tariffs,
        public readonly array $schedule,
        public readonly bool $isOpen
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
            'available_spots' => $this->availableSpots,
            'tariffs' => $this->tariffs,
            'schedule' => $this->schedule,
            'is_open' => $this->isOpen,
        ];
    }
}

