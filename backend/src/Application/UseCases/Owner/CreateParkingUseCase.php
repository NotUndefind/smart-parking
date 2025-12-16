<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Input\CreateParkingInput;
use App\Application\DTOs\Output\ParkingOutput;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\OwnerNotFoundException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class CreateParkingUseCase
{
    public function __construct(
        private OwnerRepositoryInterface $ownerRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    public function execute(CreateParkingInput $input): ParkingOutput
    {
        // 1. Vérifier que le propriétaire existe
        $owner = $this->ownerRepository->findById($input->ownerId);
        if ($owner === null) {
            throw new OwnerNotFoundException('Owner not found');
        }

        // 2. Créer l'entité Parking
        $parking = new Parking(
            id: uniqid('parking_', true),
            ownerId: $input->ownerId,
            name: $input->name,
            address: $input->address,
            latitude: $input->latitude,
            longitude: $input->longitude,
            totalSpots: $input->totalSpots,
            tariffs: $input->tariffs,
            schedule: $input->schedule,
            isActive: true
        );

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

