<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use App\Domain\Entities\Parking;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(Parking::class)]
final class ParkingTest extends TestCase
{
    public function testCalculatePriceWithSimpleTariff(): void
    {
        $parking = EntityFactory::createParking([
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 3.0]
            ]
        ]);

        // 120 minutes = 2 hours
        $price = $parking->calculatePrice(120);

        $this->assertEquals(6.0, $price);
    }

    public function testCalculatePriceWithMultipleTariffs(): void
    {
        $currentHour = (int)date('H');

        $parking = EntityFactory::createParking([
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 12, 'price_per_hour' => 2.0],
                ['start_hour' => 12, 'end_hour' => 24, 'price_per_hour' => 5.0]
            ]
        ]);

        // 60 minutes = 1 hour
        $price = $parking->calculatePrice(60);

        if ($currentHour >= 12) {
            $this->assertEquals(5.0, $price);
        } else {
            $this->assertEquals(2.0, $price);
        }
    }

    public function testIsOpenAtReturnsTrueWhenOpen(): void
    {
        // Create timestamp for Monday at 10:00
        $monday10am = strtotime('next Monday 10:00');

        $parking = EntityFactory::createParking([
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00']
            ],
            'isActive' => true
        ]);

        $this->assertTrue($parking->isOpenAt($monday10am));
    }

    public function testIsOpenAtReturnsFalseWhenClosed(): void
    {
        // Create timestamp for Monday at 22:00 (after close)
        $monday10pm = strtotime('next Monday 22:00');

        $parking = EntityFactory::createParking([
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00']
            ],
            'isActive' => true
        ]);

        $this->assertFalse($parking->isOpenAt($monday10pm));
    }

    public function testIsOpenAtReturnsFalseWhenInactive(): void
    {
        // Create timestamp for Monday at 10:00
        $monday10am = strtotime('next Monday 10:00');

        $parking = EntityFactory::createParking([
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00']
            ],
            'isActive' => false
        ]);

        $this->assertFalse($parking->isOpenAt($monday10am));
    }

    public function testCalculateDistanceReturnsCorrectValue(): void
    {
        // Paris coordinates
        $parking = EntityFactory::createParking([
            'latitude' => 48.8566,
            'longitude' => 2.3522
        ]);

        // Lyon coordinates (approximately 400km from Paris)
        $distance = $parking->calculateDistance(45.7640, 4.8357);

        // Should be around 390-400 km
        $this->assertGreaterThan(390.0, $distance);
        $this->assertLessThan(410.0, $distance);
    }

    public function testUpdateTariffsChangesValue(): void
    {
        $parking = EntityFactory::createParking([
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.0]
            ]
        ]);

        $newTariffs = [
            ['start_hour' => 0, 'end_hour' => 12, 'price_per_hour' => 1.5],
            ['start_hour' => 12, 'end_hour' => 24, 'price_per_hour' => 3.0]
        ];

        $parking->updateTariffs($newTariffs);

        $this->assertEquals($newTariffs, $parking->getTariffs());
        $this->assertNotNull($parking->getUpdatedAt());
    }

    public function testUpdateScheduleChangesValue(): void
    {
        $parking = EntityFactory::createParking([
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00']
            ]
        ]);

        $newSchedule = [
            'monday' => ['open' => '09:00', 'close' => '21:00'],
            'tuesday' => ['open' => '09:00', 'close' => '21:00']
        ];

        $parking->updateSchedule($newSchedule);

        $this->assertEquals($newSchedule, $parking->getSchedule());
        $this->assertNotNull($parking->getUpdatedAt());
    }

    public function testGettersReturnCorrectValues(): void
    {
        $parking = EntityFactory::createParking([
            'id' => 'parking_123',
            'ownerId' => 'owner_456',
            'name' => 'Central Parking',
            'address' => '42 Main Street',
            'totalSpots' => 150
        ]);

        $this->assertEquals('parking_123', $parking->getId());
        $this->assertEquals('owner_456', $parking->getOwnerId());
        $this->assertEquals('Central Parking', $parking->getName());
        $this->assertEquals('42 Main Street', $parking->getAddress());
        $this->assertEquals(150, $parking->getTotalSpots());
    }

    public function testCoordinatesReturnCorrectValues(): void
    {
        $parking = EntityFactory::createParking([
            'latitude' => 45.7640,
            'longitude' => 4.8357
        ]);

        $this->assertEquals(45.7640, $parking->getLatitude());
        $this->assertEquals(4.8357, $parking->getLongitude());
    }

    public function testCreatedAtIsSet(): void
    {
        $parking = EntityFactory::createParking();

        $this->assertInstanceOf(\DateTimeImmutable::class, $parking->getCreatedAt());
        $this->assertNull($parking->getUpdatedAt());
    }
}
