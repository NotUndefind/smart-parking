<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

final class ParkingNotFoundException extends Exception
{
    public function __construct(string $message = 'Parking not found')
    {
        parent::__construct($message, 404);
    }
}

