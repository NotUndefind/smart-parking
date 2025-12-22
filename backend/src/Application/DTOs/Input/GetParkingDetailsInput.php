<?php

declare(strict_types=1);

namespace App\Application\DTOs\Input;

final class GetParkingDetailsInput
{
    private function __construct(
        public readonly string $parkingId,
        public readonly int $timestamp,
    ) {}

    public static function create(
        string $parkingId,
        ?int $timestamp = null,
    ): self {
        return new self(
            parkingId: $parkingId,
            timestamp: $timestamp ?? time()
        );
    }
}
