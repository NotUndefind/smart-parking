<?php

namespace App\Domain\Exceptions\ParkingSpot;

class ParkingSpotAlreadyActiveException extends ParkingSpotException
{
    public function __construct(
        ?int $parkingSpotId = null,
        string $message = "Parking spot is already active."
    ) {
        parent::__construct($message, $parkingSpotId);
    }
}
