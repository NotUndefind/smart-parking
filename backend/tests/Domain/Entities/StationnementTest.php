<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use App\Domain\Entities\Stationnement;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(Stationnement::class)]
final class StationnementTest extends TestCase
{
    public function testGetDurationMinutesForActiveStationnement(): void
    {
        $entryTime = time() - 3600; // 1 hour ago

        $stationnement = EntityFactory::createStationnement([
            'entryTime' => $entryTime,
            'exitTime' => null,
            'status' => 'active'
        ]);

        $duration = $stationnement->getDurationMinutes();

        // Should be approximately 3600 seconds (allowing for test execution time)
        $this->assertGreaterThan(3500, $duration);
        $this->assertLessThan(3700, $duration);
    }

    public function testGetDurationMinutesForCompletedStationnement(): void
    {
        $entryTime = 1000;
        $exitTime = 4600; // 3600 seconds later

        $stationnement = EntityFactory::createStationnement([
            'entryTime' => $entryTime,
            'exitTime' => $exitTime,
            'status' => 'completed'
        ]);

        $duration = $stationnement->getDurationMinutes();

        // 3600 seconds / 60 = 60 minutes
        $this->assertEquals(60, $duration);
    }

    public function testExitUpdatesAllFields(): void
    {
        $stationnement = EntityFactory::createStationnement([
            'exitTime' => null,
            'finalPrice' => 0.0,
            'penaltyAmount' => 0.0,
            'status' => 'active'
        ]);

        $exitTime = time();
        $finalPrice = 15.50;
        $penaltyAmount = 5.00;

        $stationnement->exit($exitTime, $finalPrice, $penaltyAmount);

        $this->assertEquals($exitTime, $stationnement->getExitTime());
        $this->assertEquals($finalPrice, $stationnement->getFinalPrice());
        $this->assertEquals($penaltyAmount, $stationnement->getPenaltyAmount());
        $this->assertEquals('completed', $stationnement->getStatus());
        $this->assertNotNull($stationnement->getUpdatedAt());
    }

    public function testIsActiveReturnsTrueWhenNoEndTime(): void
    {
        $activeStationnement = EntityFactory::createStationnement([
            'exitTime' => null,
            'status' => 'active'
        ]);

        $this->assertTrue($activeStationnement->isActive());

        $completedStationnement = EntityFactory::createStationnement([
            'exitTime' => time(),
            'status' => 'completed'
        ]);

        $this->assertFalse($completedStationnement->isActive());
    }

    public function testGettersReturnCorrectValues(): void
    {
        $now = time();

        $stationnement = EntityFactory::createStationnement([
            'id' => 'stationnement_123',
            'userId' => 'user_456',
            'parkingId' => 'parking_789',
            'reservationId' => 'reservation_101',
            'subscriptionId' => 'subscription_202',
            'entryTime' => $now,
            'exitTime' => $now + 3600,
            'finalPrice' => 25.50,
            'penaltyAmount' => 10.00,
            'status' => 'completed'
        ]);

        $this->assertEquals('stationnement_123', $stationnement->getId());
        $this->assertEquals('user_456', $stationnement->getUserId());
        $this->assertEquals('parking_789', $stationnement->getParkingId());
        $this->assertEquals('reservation_101', $stationnement->getReservationId());
        $this->assertEquals('subscription_202', $stationnement->getSubscriptionId());
        $this->assertEquals($now, $stationnement->getEntryTime());
        $this->assertEquals($now + 3600, $stationnement->getExitTime());
        $this->assertEquals(25.50, $stationnement->getFinalPrice());
        $this->assertEquals(10.00, $stationnement->getPenaltyAmount());
        $this->assertEquals('completed', $stationnement->getStatus());
    }

    public function testCreatedAtIsSet(): void
    {
        $stationnement = EntityFactory::createStationnement();

        $this->assertInstanceOf(\DateTimeImmutable::class, $stationnement->getCreatedAt());
        $this->assertNull($stationnement->getUpdatedAt());
    }
}
