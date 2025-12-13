<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\Parking;

#[CoversClass(Parking::class)]
class ParkingTest extends TestCase
{
    public function testCanCreateParking(): void
    {
        $parking = new Parking(1, "123 Main St", 100, 2.5, "08:00-22:00");

        $this->assertInstanceOf(Parking::class, $parking);
        $this->assertEquals(1, $parking->getId());
        $this->assertEquals("123 Main St", $parking->getLocation());
        $this->assertEquals(100, $parking->getCapacity());
        $this->assertEquals(2.5, $parking->getPricePerQuarterHour());
        $this->assertEquals("08:00-22:00", $parking->getOpeningHours());
    }

    public function testCanUpdateParkingDetails(): void
    {
        $parking = new Parking(1, "123 Main St", 100, 2.5, "08:00-22:00");
        $parking->updateLocation("456 Elm St");
        $parking->updateCapacity(150);
        $parking->updatePricePerQuarterHour(3.0);
        $parking->updateOpeningHours("07:00-23:00");

        $this->assertEquals("456 Elm St", $parking->getLocation());
        $this->assertEquals(150, $parking->getCapacity());
        $this->assertEquals(3.0, $parking->getPricePerQuarterHour());
        $this->assertEquals("07:00-23:00", $parking->getOpeningHours());
    }
}
