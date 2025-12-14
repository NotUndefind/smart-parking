<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class StationnementOutput
{
    public function __construct(
        public readonly string $id,
        public readonly string $parkingId,
        public readonly string $parkingName,
        public readonly int $entryTime,
        public readonly ?int $exitTime,
        public readonly float $finalPrice,
        public readonly float $penaltyAmount,
        public readonly string $status
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parking_id' => $this->parkingId,
            'parking_name' => $this->parkingName,
            'entry_time' => $this->entryTime,
            'exit_time' => $this->exitTime,
            'final_price' => $this->finalPrice,
            'penalty_amount' => $this->penaltyAmount,
            'status' => $this->status,
        ];
    }
}

