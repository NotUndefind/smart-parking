<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Reservation;

interface ReservationRepositoryInterface
{
    public function save(Reservation $reservation): void;
    public function findById(string $id): ?Reservation;
    public function findByUserId(string $userId): array;
    public function findByParkingId(string $parkingId): array;
    public function findActiveByParking(string $parkingId, int $startTime, int $endTime): array;
    public function delete(string $id): void;
}

