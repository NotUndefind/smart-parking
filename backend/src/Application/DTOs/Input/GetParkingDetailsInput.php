<?php
declare(strict_types=1);
namespace App\Application\DTOs\Input;
final class GetMounthlyRevenueInput
{
    private function __construct(
        public readonly string $parkingId,
        public readonly int $month,
        public readonly int $year,
    ) {}
    public static function create(
        string $parkingId,
        int $month,
        int $year,
    ): self {
        return new self(parkingId: $parkingId, month: $month, year: $year);
    }
}
