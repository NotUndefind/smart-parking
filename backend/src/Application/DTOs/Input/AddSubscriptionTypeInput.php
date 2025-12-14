<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class AddSubscriptionTypeInput
{
    public function __construct(
        public readonly string $parkingId,
        public readonly string $type,
        public readonly float $price,
        public readonly int $durationDays
    ) {
    }
}

