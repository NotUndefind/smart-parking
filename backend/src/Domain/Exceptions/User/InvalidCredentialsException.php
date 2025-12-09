<?php

namespace App\Domain\Exceptions\User;

class InvalidCredentialsException extends UserException
{
    public function __construct(
        ?string $email = null,
        string $message = "Invalid email or password."
    ) {
        parent::__construct($message, null, $email);
    }
}
