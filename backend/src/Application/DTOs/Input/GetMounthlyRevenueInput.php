<?php
declare(strict_types=1);
namespace App\Application\DTOs\Input;

final class GenerateInvoiceInput
{
    private function __construct(
        public readonly string $reservationId,
        public readonly string $userId,
    ) {}
    public static function create(string $reservationId, string $userId): self
    {
        return new self(reservationId: $reservationId, userId: $userId);
    }
}
