<?php

namespace App\Domain\Exceptions\Autorisation;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all authorisation-related errors.
 */
abstract class AutorisationException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $userId = null,
        public readonly ?string $resource = null
    ) {
        parent::__construct($message);
    }
}
