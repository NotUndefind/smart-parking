<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\ParkingOwner;

#[CoversClass(ParkingOwner::class)]
class ParkinkOwnerTest extends TestCase
{
    public function testCanCreateParkingOwner(): void
    {
        $owner = new ParkingOwner(
            1,
            "Jane",
            "Smith",
            "example@email.com",
            "password",
        );
        $this->assertInstanceOf(ParkingOwner::class, $owner);
        $this->assertEquals(1, $owner->getId());
        $this->assertEquals("Jane", $owner->getFirstName());
        $this->assertEquals("Smith", $owner->getLastName());
        $this->assertEquals("example@email.com", $owner->getEmail());
        $this->assertEquals("password", $owner->getPassword());
    }

    public function testCanUpdateParkingOwnerDetails(): void
    {
        $owner = new ParkingOwner(
            1,
            "Jane",
            "Smith",
            "example@email.com",
            "password",
        );
        $owner->updateFirstName("Janet");
        $owner->updateLastName("Doe");
        $owner->updateEmail("new@email.com");
        $owner->updatePassword("newpassword");
        $this->assertEquals("Janet", $owner->getFirstName());
        $this->assertEquals("Doe", $owner->getLastName());
        $this->assertEquals("new@email.com", $owner->getEmail());
        $this->assertEquals("newpassword", $owner->getPassword());
    }
}
