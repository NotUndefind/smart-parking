<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Input\UpdateParkingScheduleInput;
use App\Application\DTOs\Output\ParkingOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class UpdateParkingScheduleUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    public function execute(UpdateParkingScheduleInput $input): ParkingOutput
    {
        // 1. Trouver le parking
        $parking = $this->parkingRepository->findById($input->parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Mettre à jour les horaires
        $parking->updateSchedule($input->schedule);

        // 3. Sauvegarder
        $this->parkingRepository->save($parking);

        // 4. Retourner le DTO Output
        return new ParkingOutput(
            id: $parking->getId(),
            name: $parking->getName(),
            address: $parking->getAddress(),
            latitude: $parking->getLatitude(),
            longitude: $parking->getLongitude(),
            totalSpots: $parking->getTotalSpots(),
            tariffs: $parking->getTariffs(),
            schedule: $parking->getSchedule()
        );
    }
}

