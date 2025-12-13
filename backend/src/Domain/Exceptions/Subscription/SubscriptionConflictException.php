<?php

namespace App\Domain\Exceptions\Subscription;

class SubscriptionConflictException extends SubscriptionException
{
    public function __construct(
        ?int $userId = null,
        string $message = "Subscription conflict occurred."
    ) {
        parent::__construct($message, null, $userId);
    }
}
