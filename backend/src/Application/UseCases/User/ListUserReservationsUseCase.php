<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;

final class ListUserReservationsUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return ReservationOutput[]
     */
    public function execute(string $userId): array
    {
        $reservations = $this->reservationRepository->findByUserId($userId);

        return array_map(function ($reservation) {
            $parking = $this->parkingRepository->findById($reservation->getParkingId());
            return new ReservationOutput(
                id: $reservation->getId(),
                parkingId: $reservation->getParkingId(),
                parkingName: $parking?->getName() ?? 'Unknown',
                startTime: $reservation->getStartTime(),
                endTime: $reservation->getEndTime(),
                estimatedPrice: $reservation->getEstimatedPrice(),
                status: $reservation->getStatus(),
                createdAt: $reservation->getCreatedAt()->format('Y-m-d H:i:s')
            );
        }, $reservations);
    }
}

