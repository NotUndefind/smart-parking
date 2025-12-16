<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class UpdateParkingScheduleInput
{
    private function __construct(
        public readonly string $parkingId,
        public readonly array $schedule,
    ) {}

    public static function create(string $parkingId, array $schedule): self
    {
        return new self(parkingId: $parkingId, schedule: $schedule);
    }
}
