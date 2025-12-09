<?php

namespace App\Domain\Exceptions\Parking;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all parking-related errors.
 */
abstract class ParkingException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $parkingId = null
    ) {
        parent::__construct($message);
    }
}
