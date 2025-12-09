<?php

namespace App\Domain\Exceptions\Reservation;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all reservation-related errors.
 */
abstract class ReservationException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $reservationId = null,
        public readonly ?int $userId = null
    ) {
        parent::__construct($message);
    }
}
