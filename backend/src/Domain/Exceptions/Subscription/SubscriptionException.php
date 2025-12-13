<?php

namespace App\Domain\Exceptions\Subscription;

use App\Domain\Exceptions\DomainException;

/**
 * Base exception for all subscription-related errors.
 */
abstract class SubscriptionException extends DomainException
{
    public function __construct(
        string $message = "",
        public readonly ?int $subscriptionId = null,
        public readonly ?int $userId = null
    ) {
        parent::__construct($message);
    }
}
