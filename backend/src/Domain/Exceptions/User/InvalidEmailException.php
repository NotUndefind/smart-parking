<?php

namespace App\Domain\Exceptions\User;

class InvalidEmailException extends UserException
{
    public function __construct(
        ?string $email = null,
        string $message = "The provided email address is invalid."
    ) {
        parent::__construct($message, null, $email);
    }
}
