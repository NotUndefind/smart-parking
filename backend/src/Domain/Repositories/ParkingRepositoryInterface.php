<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Parking;

interface ParkingRepositoryInterface
{
    public function save(Parking $parking): void;
    public function findById(string $id): ?Parking;
    public function findByOwnerId(string $ownerId): array;
    public function findByLocation(float $latitude, float $longitude, float $radiusKm = 5.0): array;
    public function delete(string $id): void;
}

