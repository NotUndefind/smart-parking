<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use App\Domain\Entities\Reservation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(Reservation::class)]
final class ReservationTest extends TestCase
{
    public function testCanCancelActiveReservation(): void
    {
        $reservation = EntityFactory::createReservation([
            'status' => 'active'
        ]);

        $reservation->cancel();

        $this->assertEquals('cancelled', $reservation->getStatus());
        $this->assertNotNull($reservation->getUpdatedAt());
    }

    public function testCannotCancelCompletedReservation(): void
    {
        $reservation = EntityFactory::createReservation([
            'status' => 'completed'
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot cancel a completed reservation');

        $reservation->cancel();
    }

    public function testCanCompleteReservation(): void
    {
        $reservation = EntityFactory::createReservation([
            'status' => 'active'
        ]);

        $reservation->complete();

        $this->assertEquals('completed', $reservation->getStatus());
        $this->assertNotNull($reservation->getUpdatedAt());
    }

    public function testIsActiveReturnsTrueForActiveStatus(): void
    {
        $activeReservation = EntityFactory::createReservation([
            'status' => 'active'
        ]);

        $this->assertTrue($activeReservation->isActive());

        $cancelledReservation = EntityFactory::createReservation([
            'status' => 'cancelled'
        ]);

        $this->assertFalse($cancelledReservation->isActive());
    }

    public function testIsOverlappingDetectsConflict(): void
    {
        // Reservation from 1000 to 2000
        $reservation = EntityFactory::createReservation([
            'startTime' => 1000,
            'endTime' => 2000
        ]);

        // Completely before: no overlap
        $this->assertFalse($reservation->isOverlapping(500, 900));

        // Completely after: no overlap
        $this->assertFalse($reservation->isOverlapping(2100, 2500));

        // Overlaps at start
        $this->assertTrue($reservation->isOverlapping(900, 1500));

        // Overlaps at end
        $this->assertTrue($reservation->isOverlapping(1500, 2100));

        // Completely inside
        $this->assertTrue($reservation->isOverlapping(1200, 1800));

        // Completely contains
        $this->assertTrue($reservation->isOverlapping(500, 2500));
    }

    public function testGettersReturnCorrectValues(): void
    {
        $now = time();
        $endTime = $now + 3600;

        $reservation = EntityFactory::createReservation([
            'id' => 'reservation_123',
            'userId' => 'user_456',
            'parkingId' => 'parking_789',
            'startTime' => $now,
            'endTime' => $endTime,
            'estimatedPrice' => 15.50
        ]);

        $this->assertEquals('reservation_123', $reservation->getId());
        $this->assertEquals('user_456', $reservation->getUserId());
        $this->assertEquals('parking_789', $reservation->getParkingId());
        $this->assertEquals($now, $reservation->getStartTime());
        $this->assertEquals($endTime, $reservation->getEndTime());
        $this->assertEquals(15.50, $reservation->getEstimatedPrice());
    }

    public function testCreatedAtIsSet(): void
    {
        $reservation = EntityFactory::createReservation();

        $this->assertInstanceOf(\DateTimeImmutable::class, $reservation->getCreatedAt());
        $this->assertNull($reservation->getUpdatedAt());
    }
}
