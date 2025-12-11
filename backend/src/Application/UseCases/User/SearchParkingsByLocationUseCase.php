<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Input\SearchParkingsByLocationInput;
use App\Application\DTOs\Output\ParkingOutput;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class SearchParkingsByLocationUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return ParkingOutput[]
     */
    public function execute(SearchParkingsByLocationInput $input): array
    {
        $parkings = $this->parkingRepository->findByLocation(
            $input->latitude,
            $input->longitude,
            $input->radiusKm
        );

        return array_map(function ($parking) use ($input) {
            return new ParkingOutput(
                id: $parking->getId(),
                name: $parking->getName(),
                address: $parking->getAddress(),
                latitude: $parking->getLatitude(),
                longitude: $parking->getLongitude(),
                totalSpots: $parking->getTotalSpots(),
                tariffs: $parking->getTariffs(),
                schedule: $parking->getSchedule(),
                distanceKm: $parking->calculateDistance($input->latitude, $input->longitude)
            );
        }, $parkings);
    }
}

