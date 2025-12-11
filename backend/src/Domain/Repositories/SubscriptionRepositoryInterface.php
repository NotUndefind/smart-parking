<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Subscription;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;
    public function findById(string $id): ?Subscription;
    public function findByUserId(string $userId): array;
    public function findByParkingId(string $parkingId): array;
    public function findActiveByUserAndParking(string $userId, string $parkingId, int $timestamp): ?Subscription;
    public function delete(string $id): void;
}

