<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class ReservationOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $parkingId,
        public readonly string $parkingName,
        public readonly int $startTime,
        public readonly int $endTime,
        public readonly float $estimatedPrice,
        public readonly string $status,
        public readonly string $createdAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parking_id' => $this->parkingId,
            'parking_nom' => $this->parkingName,
            'debut' => $this->startTime,
            'fin' => $this->endTime,
            'prix_estime' => $this->estimatedPrice,
            'statut' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }
}

