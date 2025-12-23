<?php

declare(strict_types=1);

namespace Tests\Domain\Entities;

use App\Domain\Entities\Owner;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(Owner::class)]
final class OwnerTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $owner = EntityFactory::createOwner([
            'id' => 'owner_123',
            'email' => 'owner@example.com',
            'companyName' => 'ACME Corp',
            'firstName' => 'Jane',
            'lastName' => 'Smith'
        ]);

        $this->assertEquals('owner_123', $owner->getId());
        $this->assertEquals('owner@example.com', $owner->getEmail());
        $this->assertEquals('ACME Corp', $owner->getCompanyName());
        $this->assertEquals('Jane', $owner->getFirstName());
        $this->assertEquals('Smith', $owner->getLastName());
        $this->assertNotNull($owner->getCreatedAt());
        $this->assertNull($owner->getUpdatedAt());
    }

    public function testUpdatePassword(): void
    {
        $owner = EntityFactory::createOwner();

        $newPasswordHash = '$2y$12$newHashValue';
        $owner->updatePassword($newPasswordHash);

        $this->assertEquals($newPasswordHash, $owner->getPasswordHash());
        $this->assertNotNull($owner->getUpdatedAt());
    }

    public function testUpdateProfile(): void
    {
        $owner = EntityFactory::createOwner([
            'companyName' => 'Old Company',
            'firstName' => 'Jane',
            'lastName' => 'Smith'
        ]);

        $owner->updateProfile('New Company', 'John', 'Doe');

        $this->assertEquals('New Company', $owner->getCompanyName());
        $this->assertEquals('John', $owner->getFirstName());
        $this->assertEquals('Doe', $owner->getLastName());
        $this->assertNotNull($owner->getUpdatedAt());
    }

    public function testGetFullName(): void
    {
        $owner = EntityFactory::createOwner([
            'firstName' => 'Jane',
            'lastName' => 'Smith'
        ]);

        $this->assertEquals('Jane Smith', $owner->getFullName());
    }
}
