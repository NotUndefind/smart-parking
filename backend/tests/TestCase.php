<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use App\Infrastructure\Security\PasswordHasher;

abstract class TestCase extends BaseTestCase
{
    protected function createPasswordHasher(): PasswordHasher
    {
        return new PasswordHasher();
    }

    protected function mockRepository(string $interface): mixed
    {
        return $this->createMock($interface);
    }
}
