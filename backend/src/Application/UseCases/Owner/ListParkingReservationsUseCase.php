<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;

final class ListParkingReservationsUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return ReservationOutput[]
     */
    public function execute(string $parkingId): array
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Récupérer les réservations
        $reservations = $this->reservationRepository->findByParkingId($parkingId);

        // 3. Retourner les DTOs Output
        return array_map(function ($reservation) use ($parking) {
            return new ReservationOutput(
                id: $reservation->getId(),
                parkingId: $reservation->getParkingId(),
                parkingName: $parking->getName(),
                startTime: $reservation->getStartTime(),
                endTime: $reservation->getEndTime(),
                estimatedPrice: $reservation->getEstimatedPrice(),
                status: $reservation->getStatus(),
                createdAt: $reservation->getCreatedAt()->format('Y-m-d H:i:s')
            );
        }, $reservations);
    }
}

