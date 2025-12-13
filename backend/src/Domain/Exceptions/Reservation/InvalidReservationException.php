<?php

namespace App\Domain\Exceptions\Reservation;

class InvalidReservationException extends ReservationException
{
    public function __construct(
        string $message = "Invalid reservation."
    ) {
        parent::__construct($message);
    }
}
