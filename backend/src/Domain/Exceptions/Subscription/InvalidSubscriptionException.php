<?php

namespace App\Domain\Exceptions\Subscription;

class InvalidSubscriptionException extends SubscriptionException
{
    public function __construct(
        string $message = "Invalid subscription."
    ) {
        parent::__construct($message);
    }
}
