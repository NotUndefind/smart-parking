<?php

namespace App\Domain\Exceptions\ParkingOwner;

class ParkingOwnerNotFoundException extends ParkingOwnerException
{
    public function __construct(
        ?int $parkingOwnerId = null,
        string $message = "Parking owner not found."
    ) {
        parent::__construct($message, $parkingOwnerId);
    }
}
