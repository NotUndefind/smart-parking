<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;

final class OwnerNotFoundException extends Exception
{
    public function __construct(string $message = 'Owner not found')
    {
        parent::__construct($message, 404);
    }
}

