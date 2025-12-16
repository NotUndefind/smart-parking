<?php

namespace App\Domain\Exceptions\User;

class UserAlreadyExistException extends UserException
{
    public function __construct(
        ?string $email = null,
        string $message = "User with this email already exists."
    ) {
        parent::__construct($message, null, $email);
    }
}
