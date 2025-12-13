<?php

namespace App\Domain\Exceptions\Reservation;

class NoActiveReservationException extends ReservationException
{
    public function __construct(
        ?int $userId = null,
        string $message = "No active reservation found."
    ) {
        parent::__construct($message, null, $userId);
    }
}
