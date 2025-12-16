<?php

namespace App\Domain\Exceptions\ParkingSpot;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all parking spot-related errors.
 */
abstract class ParkingSpotException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $parkingSpotId = null,
        public readonly ?int $parkingId = null
    ) {
        parent::__construct($message);
    }
}
