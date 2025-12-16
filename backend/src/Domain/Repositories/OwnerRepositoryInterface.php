<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Owner;

interface OwnerRepositoryInterface
{
    public function save(Owner $owner): void;
    public function findById(string $id): ?Owner;
    public function findByEmail(string $email): ?Owner;
    public function delete(string $id): void;
}

