<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\Subscription;
use Tests\Helpers\EntityFactory;

#[CoversClass(Subscription::class)]
final class SubscriptionTest extends TestCase
{
    public function testCanCreateSubscription(): void
    {
        $now = time();
        $subscription = new Subscription(
            id: 'subscription_1',
            parkingId: 'parking_1',
            userId: 'user_1',
            type: 'monthly',
            price: 29.99,
            startDate: $now,
            endDate: $now + (30 * 24 * 3600),
            isActive: true
        );

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('subscription_1', $subscription->getId());
        $this->assertEquals('parking_1', $subscription->getParkingId());
        $this->assertEquals('user_1', $subscription->getUserId());
        $this->assertEquals('monthly', $subscription->getType());
        $this->assertEquals(29.99, $subscription->getPrice());
        $this->assertTrue($subscription->isActive());
    }

    public function testCanDeactivateSubscription(): void
    {
        $now = time();
        $subscription = new Subscription(
            id: 'subscription_1',
            parkingId: 'parking_1',
            userId: 'user_1',
            type: 'monthly',
            price: 29.99,
            startDate: $now,
            endDate: $now + (30 * 24 * 3600),
            isActive: true
        );

        $this->assertTrue($subscription->isActive());

        $subscription->deactivate();

        $this->assertFalse($subscription->isActive());
    }

    public function testIsValidAtReturnsTrueForCurrentTime(): void
    {
        $now = time();
        $subscription = new Subscription(
            id: 'subscription_1',
            parkingId: 'parking_1',
            userId: 'user_1',
            type: 'monthly',
            price: 29.99,
            startDate: $now - 86400, // Started yesterday
            endDate: $now + (29 * 24 * 3600), // Ends in 29 days
            isActive: true
        );

        $this->assertTrue($subscription->isValidAt($now));
    }

    public function testIsValidAtReturnsFalseBeforeStartDate(): void
    {
        $now = time();
        $subscription = new Subscription(
            id: 'subscription_1',
            parkingId: 'parking_1',
            userId: 'user_1',
            type: 'monthly',
            price: 29.99,
            startDate: $now + 86400, // Starts tomorrow
            endDate: $now + (30 * 24 * 3600),
            isActive: true
        );

        $this->assertFalse($subscription->isValidAt($now));
    }

    public function testIsValidAtReturnsFalseAfterEndDate(): void
    {
        $now = time();
        $subscription = new Subscription(
            id: 'subscription_1',
            parkingId: 'parking_1',
            userId: 'user_1',
            type: 'monthly',
            price: 29.99,
            startDate: $now - (30 * 24 * 3600), // Started 30 days ago
            endDate: $now - 86400, // Ended yesterday
            isActive: true
        );

        $this->assertFalse($subscription->isValidAt($now));
    }

    public function testGettersReturnCorrectValues(): void
    {
        $startDate = time();
        $endDate = $startDate + (30 * 24 * 3600);

        $subscription = EntityFactory::createSubscription([
            'id' => 'subscription_123',
            'parkingId' => 'parking_456',
            'userId' => 'user_789',
            'type' => 'yearly',
            'price' => 199.99,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isActive' => true
        ]);

        $this->assertEquals('subscription_123', $subscription->getId());
        $this->assertEquals('parking_456', $subscription->getParkingId());
        $this->assertEquals('user_789', $subscription->getUserId());
        $this->assertEquals('yearly', $subscription->getType());
        $this->assertEquals(199.99, $subscription->getPrice());
        $this->assertEquals($startDate, $subscription->getStartDate());
        $this->assertEquals($endDate, $subscription->getEndDate());
        $this->assertTrue($subscription->isActive());
    }

    public function testCreatedAtIsSet(): void
    {
        $subscription = EntityFactory::createSubscription();

        $this->assertInstanceOf(\DateTimeImmutable::class, $subscription->getCreatedAt());
        $this->assertNull($subscription->getUpdatedAt());
    }
}
