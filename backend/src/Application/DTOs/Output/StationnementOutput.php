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
            'parking_nom' => $this->parkingName,
            'heure_entree' => $this->entryTime,
            'heure_sortie' => $this->exitTime,
            'prix_total' => $this->finalPrice,
            'penalite' => $this->penaltyAmount,
            'statut' => $this->status,
        ];
    }
}

