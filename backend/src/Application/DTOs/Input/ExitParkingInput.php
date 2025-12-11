<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class ExitParkingInput
{
    public function __construct(
        public readonly string $stationnementId
    ) {
    }
}

