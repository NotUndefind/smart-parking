<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\Reservation;

#[CoversClass(Reservation::class)]
class ReservationTest extends TestCase
{
    public function testCanCreateReservation(): void
    {
        // Mock User and ParkingSpot entities
        $user = $this->createMock(\App\Domain\Entities\User::class);
        $parkingSpot = $this->createMock(
            \App\Domain\Entities\ParkingSpot::class,
        );

        $reservation = new Reservation(
            1,
            $user,
            $parkingSpot,
            "2024-01-01 10:00:00",
            "2024-01-01 12:00:00",
        );

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals(1, $reservation->getId());
        $this->assertEquals($user, $reservation->getUser());
        $this->assertEquals($parkingSpot, $reservation->getParkingSpot());
        $this->assertEquals(
            "2024-01-01 10:00:00",
            $reservation->getStartTime(),
        );
        $this->assertEquals("2024-01-01 12:00:00", $reservation->getEndTime());
    }

    public function testCanUpdateReservationTimes(): void
    {
        // Mock User and ParkingSpot entities
        $user = $this->createMock(\App\Domain\Entities\User::class);
        $parkingSpot = $this->createMock(
            \App\Domain\Entities\ParkingSpot::class,
        );

        $reservation = new Reservation(
            1,
            $user,
            $parkingSpot,
            "2024-01-01 10:00:00",
            "2024-01-01 12:00:00",
        );
        $reservation->updateStartTime("2024-01-01 11:00:00");
        $reservation->updateEndTime("2024-01-01 13:00:00");

        $this->assertEquals(
            "2024-01-01 11:00:00",
            $reservation->getStartTime(),
        );
        $this->assertEquals("2024-01-01 13:00:00", $reservation->getEndTime());
    }
}
