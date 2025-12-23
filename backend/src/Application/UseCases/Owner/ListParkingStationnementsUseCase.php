<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class ListParkingStationnementsUseCase
{
    public function __construct(
        private StationnementRepositoryInterface $stationnementRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @return StationnementOutput[]
     */
    public function execute(string $parkingId): array
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Récupérer les stationnements
        $stationnements = $this->stationnementRepository->findByParkingId($parkingId);

        // 3. Retourner les DTOs Output
        return array_map(function ($stationnement) use ($parking) {
            $user = $this->userRepository->findById($stationnement->getUserId());
            return new StationnementOutput(
                id: $stationnement->getId(),
                parkingId: $stationnement->getParkingId(),
                parkingName: $parking->getName(),
                entryTime: $stationnement->getEntryTime(),
                exitTime: $stationnement->getExitTime(),
                finalPrice: $stationnement->getFinalPrice(),
                penaltyAmount: $stationnement->getPenaltyAmount(),
                status: $stationnement->getStatus(),
                userEmail: $user?->getEmail()
            );
        }, $stationnements);
    }
}

