<?php

namespace App\Domain\Exceptions\ParkingSpot;

class NoActiveParkingSpotException extends ParkingSpotException
{
    public function __construct(
        string $message = "No active parking spot found."
    ) {
        parent::__construct($message);
    }
}
