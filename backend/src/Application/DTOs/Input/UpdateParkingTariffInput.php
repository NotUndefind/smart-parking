<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class UpdateParkingTariffInput
{
    private function __construct(
        public readonly string $parkingId,
        public readonly array $tariffs,
    ) {}

    public static function create(string $parkingId, array $tariffs): self
    {
        return new self(parkingId: $parkingId, tariffs: $tariffs);
    }
}
