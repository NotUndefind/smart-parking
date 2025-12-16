<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class ExitParkingInput
{
    private function __construct(public readonly string $stationnementId) {}

    public static function create(string $stationnementId): self
    {
        return new self(stationnementId: $stationnementId);
    }
}
