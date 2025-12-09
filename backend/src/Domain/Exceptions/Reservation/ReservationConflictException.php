<?php

namespace App\Domain\Exceptions\Reservation;

class ReservationConflictException extends ReservationException
{
    public function __construct(
        ?int $userId = null,
        string $message = "Reservation conflict."
    ) {
        parent::__construct($message, null, $userId);
    }
}
