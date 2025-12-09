<?php

namespace App\Domain\Exceptions\Parking;

class ParkingNotFoundException extends ParkingException
{
    public function __construct(
        ?int $parkingId = null,
        string $message = "Parking not found."
    ) {
        parent::__construct($message, $parkingId);
    }
}
