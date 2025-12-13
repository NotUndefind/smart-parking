<?php

namespace Tests\Domain\Entities;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Domain\Entities\User;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $user = new User(1, "test@example.com", "password123", "John", "Doe");

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals("test@example.com", $user->getEmail());
        $this->assertEquals("password123", $user->getPassword());
        $this->assertEquals("John", $user->getFirstName());
        $this->assertEquals("Doe", $user->getLastName());
    }

    public function testCanUpdatePassword(): void
    {
        $user = new User(1, "test@example.com", "password123", "John", "Doe");
        $user->updatePassword("newpassword456");
        $user->updateEmail("newemail@example.com");
        $user->updateFirstName("NewFirstName");
        $user->updateLastName("NewLastName");
        $this->assertEquals("newpassword456", $user->getPassword());
        $this->assertEquals("newemail@example.com", $user->getEmail());
        $this->assertEquals("NewFirstName", $user->getFirstName());
        $this->assertEquals("NewLastName", $user->getLastName());
    }

    public function testGetFullName(): void
    {
        $user = new User(1, "John@gmail.com", "password123", "John", "Doe");
        $this->assertEquals("John Doe", $user->getFullName());
    }
}
