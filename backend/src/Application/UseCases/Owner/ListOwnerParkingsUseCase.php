<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\OwnerRepositoryInterface;

final class ListOwnerParkingsUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository,
        private OwnerRepositoryInterface $ownerRepository
    ) {
    }

    public function execute(string $ownerId): array
    {
        $owner = $this->ownerRepository->findById($ownerId);
        if ($owner === null) {
            throw new \InvalidArgumentException('Owner not found');
        }

        $parkings = $this->parkingRepository->findByOwnerId($ownerId);

        return array_map(function ($parking) {
            return [
                'id' => $parking->getId(),
                'name' => $parking->getName(),
                'address' => $parking->getAddress(),
                'latitude' => $parking->getLatitude(),
                'longitude' => $parking->getLongitude(),
                'total_spots' => $parking->getTotalSpots(),
                'available_spots' => $parking->getTotalSpots(), // TODO: Calculate based on active stationnements
                'tariffs' => $parking->getTariffs(),
                'schedule' => $parking->getSchedule(),
            ];
        }, $parkings);
    }
}
