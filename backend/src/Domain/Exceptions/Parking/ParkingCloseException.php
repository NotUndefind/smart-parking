<?php

namespace App\Domain\Exceptions\Parking;

class ParkingCloseException extends ParkingException
{
    public function __construct(
        ?int $parkingId = null,
        string $message = "Parking is closed."
    ) {
        parent::__construct($message, $parkingId);
    }
}
