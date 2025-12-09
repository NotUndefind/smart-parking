<?php

namespace App\Domain\Exceptions\ParkingOwner;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all parking owner-related errors.
 */
abstract class ParkingOwnerException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $parkingOwnerId = null
    ) {
        parent::__construct($message);
    }
}
