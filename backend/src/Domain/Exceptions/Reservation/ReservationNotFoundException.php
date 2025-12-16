<?php

namespace App\Domain\Exceptions\Reservation;

class ReservationNotFoundException extends ReservationException
{
    public function __construct(
        ?int $reservationId = null,
        ?int $userId = null,
        string $message = "Reservation not found."
    ) {
        parent::__construct($message, $reservationId, $userId);
    }
}
