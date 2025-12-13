<?php

namespace App\Domain\Exceptions\Autorisation;

class UnauthorizedAccessException extends AutorisationException
{
    public function __construct(
        ?int $userId = null,
        ?string $resource = null,
        string $message = "Unauthorized access."
    ) {
        parent::__construct($message, $userId, $resource);
    }
}
