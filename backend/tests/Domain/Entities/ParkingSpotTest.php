<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\ParkingSpot;
use App\Domain\Entities\User;
use App\Domain\Entities\Parking;
use Tests\Helpers\EntityFactory;

#[CoversClass(ParkingSpot::class)]
#[CoversClass(User::class)]
#[CoversClass(Parking::class)]
final class ParkingSpotTest extends TestCase
{
    public function testCanCreateParkingSpot(): void
    {
        $user = EntityFactory::createUser();
        $parking = EntityFactory::createParking();

        $spot = new ParkingSpot(
            id: 1,
            user: $user,
            startTime: '08:00',
            endTime: '18:00',
            parking: $parking
        );

        $this->assertEquals(1, $spot->getId());
        $this->assertSame($user, $spot->getUser());
        $this->assertEquals('08:00', $spot->getStartTime());
        $this->assertEquals('18:00', $spot->getEndTime());
        $this->assertSame($parking, $spot->getParking());
    }

    public function testCanUpdateUser(): void
    {
        $user1 = EntityFactory::createUser(['id' => 'user_1']);
        $user2 = EntityFactory::createUser(['id' => 'user_2']);
        $parking = EntityFactory::createParking();

        $spot = new ParkingSpot(
            id: 1,
            user: $user1,
            startTime: '08:00',
            endTime: '18:00',
            parking: $parking
        );

        $spot->updateUser($user2);

        $this->assertSame($user2, $spot->getUser());
        $this->assertEquals('user_2', $spot->getUser()->getId());
    }

    public function testCanUpdateStartTime(): void
    {
        $user = EntityFactory::createUser();
        $parking = EntityFactory::createParking();

        $spot = new ParkingSpot(
            id: 1,
            user: $user,
            startTime: '08:00',
            endTime: '18:00',
            parking: $parking
        );

        $spot->updateStartTime('09:00');

        $this->assertEquals('09:00', $spot->getStartTime());
    }

    public function testCanUpdateEndTime(): void
    {
        $user = EntityFactory::createUser();
        $parking = EntityFactory::createParking();

        $spot = new ParkingSpot(
            id: 1,
            user: $user,
            startTime: '08:00',
            endTime: '18:00',
            parking: $parking
        );

        $spot->updateEndTime('19:00');

        $this->assertEquals('19:00', $spot->getEndTime());
    }

    public function testGettersReturnCorrectValues(): void
    {
        $user = EntityFactory::createUser(['email' => 'test@example.com']);
        $parking = EntityFactory::createParking(['name' => 'Test Parking']);

        $spot = new ParkingSpot(
            id: 42,
            user: $user,
            startTime: '10:00',
            endTime: '20:00',
            parking: $parking
        );

        $this->assertEquals(42, $spot->getId());
        $this->assertEquals('test@example.com', $spot->getUser()->getEmail());
        $this->assertEquals('Test Parking', $spot->getParking()->getName());
        $this->assertEquals('10:00', $spot->getStartTime());
        $this->assertEquals('20:00', $spot->getEndTime());
    }
}
