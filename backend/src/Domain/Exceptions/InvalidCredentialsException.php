<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

final class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Invalid credentials')
    {
        parent::__construct($message, 401);
    }
}

