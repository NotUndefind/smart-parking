<?php

namespace App\Domain\Exceptions\User;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all user-related errors.
 */
abstract class UserException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $userId = null,
        public readonly ?string $email = null
    ) {
        parent::__construct($message);
    }
}
