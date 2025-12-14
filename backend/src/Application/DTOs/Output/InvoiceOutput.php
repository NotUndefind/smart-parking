<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class InvoiceOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $stationnementId,
        public readonly string $parkingName,
        public readonly string $userName,
        public readonly int $entryTime,
        public readonly int $exitTime,
        public readonly float $finalPrice,
        public readonly float $penaltyAmount,
        public readonly float $totalAmount,
        public readonly string $generatedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'stationnement_id' => $this->stationnementId,
            'parking_name' => $this->parkingName,
            'user_name' => $this->userName,
            'entry_time' => $this->entryTime,
            'exit_time' => $this->exitTime,
            'final_price' => $this->finalPrice,
            'penalty_amount' => $this->penaltyAmount,
            'total_amount' => $this->totalAmount,
            'generated_at' => $this->generatedAt,
        ];
    }
}

