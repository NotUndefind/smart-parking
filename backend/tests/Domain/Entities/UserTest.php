<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use App\Domain\Entities\User;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $user = EntityFactory::createUser([
            'id' => 'user_123',
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        $this->assertEquals('user_123', $user->getId());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertNotNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    public function testUpdatePassword(): void
    {
        $user = EntityFactory::createUser();

        $newPasswordHash = '$2y$12$newHashValue';
        $user->updatePassword($newPasswordHash);

        $this->assertEquals($newPasswordHash, $user->getPasswordHash());
        $this->assertNotNull($user->getUpdatedAt());
    }

    public function testUpdateProfile(): void
    {
        $user = EntityFactory::createUser([
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        $user->updateProfile('Jane', 'Smith');

        $this->assertEquals('Jane', $user->getFirstName());
        $this->assertEquals('Smith', $user->getLastName());
        $this->assertNotNull($user->getUpdatedAt());
    }

    public function testGetFullName(): void
    {
        $user = EntityFactory::createUser([
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $user->getFullName());
    }
}
