<?php

namespace App\Domain\Exceptions\Subscription;

class SubscriptionNotFoundException extends SubscriptionException
{
    public function __construct(
        ?int $subscriptionId = null,
        ?int $userId = null,
        string $message = "Subscription not found."
    ) {
        parent::__construct($message, $subscriptionId, $userId);
    }
}
