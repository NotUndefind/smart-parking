<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class UpdateParkingTariffInput
{
    public function __construct(
        public readonly string $parkingId,
        public readonly array $tariffs
    ) {
    }
}

