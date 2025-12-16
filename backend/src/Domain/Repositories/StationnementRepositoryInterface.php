<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Stationnement;

interface StationnementRepositoryInterface
{
    public function save(Stationnement $stationnement): void;
    public function findById(string $id): ?Stationnement;
    public function findByUserId(string $userId): array;
    public function findByParkingId(string $parkingId): array;
    public function findActiveByUserAndParking(string $userId, string $parkingId): ?Stationnement;
    public function delete(string $id): void;
}

