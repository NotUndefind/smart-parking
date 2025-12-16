<?php

namespace App\Domain\Exceptions\User;

class WeakPasswordException extends UserException
{
    public function __construct(
        string $message = "The provided password is too weak."
    ) {
        parent::__construct($message);
    }
}
