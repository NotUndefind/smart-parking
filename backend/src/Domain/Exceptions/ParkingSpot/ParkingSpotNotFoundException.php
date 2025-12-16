<?php

namespace App\Domain\Exceptions\ParkingSpot;

class ParkingSpotNotFoundException extends ParkingSpotException
{
    public function __construct(
        ?int $parkingSpotId = null,
        ?int $parkingId = null,
        string $message = "Parking spot not found."
    ) {
        parent::__construct($message, $parkingSpotId, $parkingId);
    }
}
