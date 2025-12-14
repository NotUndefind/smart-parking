<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class SubscriptionOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $parkingId,
        public readonly string $parkingName,
        public readonly string $type,
        public readonly float $price,
        public readonly int $startDate,
        public readonly int $endDate,
        public readonly bool $isActive
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parking_id' => $this->parkingId,
            'parking_name' => $this->parkingName,
            'type' => $this->type,
            'price' => $this->price,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive,
        ];
    }
}

