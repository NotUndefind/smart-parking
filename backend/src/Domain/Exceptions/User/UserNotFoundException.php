<?php

namespace App\Domain\Exceptions\User;

class UserNotFoundException extends UserException
{
    public function __construct(
        ?int $userId = null,
        ?string $email = null,
        string $message = "User not found."
    ) {
        parent::__construct($message, $userId, $email);
    }
}
