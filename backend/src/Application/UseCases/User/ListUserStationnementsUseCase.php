<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;

final class ListUserStationnementsUseCase
{
    public function __construct(
        private StationnementRepositoryInterface $stationnementRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return StationnementOutput[]
     */
    public function execute(string $userId): array
    {
        $stationnements = $this->stationnementRepository->findByUserId($userId);

        return array_map(function ($stationnement) {
            $parking = $this->parkingRepository->findById($stationnement->getParkingId());
            return new StationnementOutput(
                id: $stationnement->getId(),
                parkingId: $stationnement->getParkingId(),
                parkingName: $parking?->getName() ?? 'Unknown',
                entryTime: $stationnement->getEntryTime(),
                exitTime: $stationnement->getExitTime(),
                finalPrice: $stationnement->getFinalPrice(),
                penaltyAmount: $stationnement->getPenaltyAmount(),
                status: $stationnement->getStatus()
            );
        }, $stationnements);
    }
}

