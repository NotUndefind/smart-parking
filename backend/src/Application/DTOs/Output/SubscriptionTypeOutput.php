<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class SubscriptionTypeOutput
{
    public function __construct(
        public readonly string $type,
        public readonly float $price,
        public readonly int $durationDays
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'price' => $this->price,
            'duration_days' => $this->durationDays,
        ];
    }
}

