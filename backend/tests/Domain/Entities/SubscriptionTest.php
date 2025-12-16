<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\Subscription;

#[CoversClass(Subscription::class)]
class SubscriptionTest extends TestCase
{
    public function testCanCreateSubscription(): void
    {
        $subscription = new Subscription(1, "Monthly", 29.99, 1);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals(1, $subscription->getId());
        $this->assertEquals("Monthly", $subscription->getType());
        $this->assertEquals(29.99, $subscription->getPrice());
        $this->assertEquals(1, $subscription->getDuration());
    }

    public function testCanUpdateSubscriptionDetails(): void
    {
        $subscription = new Subscription(1, "Monthly", 29.99, 1);
        $subscription->updateType("Yearly");
        $subscription->updatePrice(299.99);
        $this->assertEquals("Yearly", $subscription->getType());
        $this->assertEquals(299.99, $subscription->getPrice());
    }
}
