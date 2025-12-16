<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\ParkingSpot;

#[CoversClass(ParkingSpot::class)]
class ParkinkSpotTest extends TestCase
{
    public function testCanCreateParkingSpot(): void
    {
        // Mock User and Parking entities for association
        $user = $this->createMock(\App\Domain\Entities\User::class);
        $parking = $this->createMock(\App\Domain\Entities\Parking::class);

        $parkingSpot = new ParkingSpot(
            1,
            $user,
            "2024-10-01 10:00:00",
            "2024-10-01 12:00:00",
            $parking,
        );

        $this->assertInstanceOf(ParkingSpot::class, $parkingSpot);
        $this->assertEquals(1, $parkingSpot->getId());
        $this->assertEquals($user, $parkingSpot->getUser());
        $this->assertEquals(
            "2024-10-01 10:00:00",
            $parkingSpot->getStartTime(),
        );
        $this->assertEquals("2024-10-01 12:00:00", $parkingSpot->getEndTime());
        $this->assertEquals($parking, $parkingSpot->getParking());
    }

    public function testCanUpdateParkingSpotDetails(): void
    {
        // Mock User and Parking entities for association
        $user1 = $this->createMock(\App\Domain\Entities\User::class);
        $user2 = $this->createMock(\App\Domain\Entities\User::class);
        $parking = $this->createMock(\App\Domain\Entities\Parking::class);

        $parkingSpot = new ParkingSpot(
            1,
            $user1,
            "2024-10-01 10:00:00",
            "2024-10-01 12:00:00",
            $parking,
        );
        $parkingSpot->updateUser($user2);
        $parkingSpot->updateStartTime("2024-10-01 11:00:00");
        $parkingSpot->updateEndTime("2024-10-01 13:00:00");

        $this->assertEquals($user2, $parkingSpot->getUser());
        $this->assertEquals(
            "2024-10-01 11:00:00",
            $parkingSpot->getStartTime(),
        );
        $this->assertEquals("2024-10-01 13:00:00", $parkingSpot->getEndTime());
    }
}
