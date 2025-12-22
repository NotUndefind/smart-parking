<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class AvailableSpotsOutput
{
    public function __construct(
        public readonly int $availableSpots,
        public readonly int $totalSpots,
        public readonly int $occupiedSpots
    ) {
    }

    public function toArray(): array
    {
        return [
            'available_spots' => $this->availableSpots,
            'total_spots' => $this->totalSpots,
            'occupied_spots' => $this->occupiedSpots,
        ];
    }
}
