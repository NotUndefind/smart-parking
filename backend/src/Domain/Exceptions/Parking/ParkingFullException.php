<?php

namespace App\Domain\Exceptions\Parking;

class ParkingFullException extends ParkingException
{
    public function __construct(
        ?int $parkingId = null,
        string $message = "Parking is full."
    ) {
        parent::__construct($message, $parkingId);
    }
}
