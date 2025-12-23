<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;

final class CancelReservationUseCase
{
    public function __construct(
        private ReservationRepositoryInterface $reservationRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    public function execute(string $reservationId, string $userId): ReservationOutput
    {
        // 1. Trouver la réservation
        $reservation = $this->reservationRepository->findById($reservationId);
        if ($reservation === null) {
            throw new UserNotFoundException('Reservation not found');
        }

        // 2. Vérifier que la réservation appartient à l'utilisateur
        if ($reservation->getUserId() !== $userId) {
            throw new \RuntimeException('Reservation does not belong to this user');
        }

        // 3. Annuler la réservation
        $reservation->cancel();

        // 4. Sauvegarder
        $this->reservationRepository->save($reservation);

        // 5. Récupérer le parking pour le nom
        $parking = $this->parkingRepository->findById($reservation->getParkingId());

        // 6. Retourner le DTO Output
        return new ReservationOutput(
            id: $reservation->getId(),
            parkingId: $reservation->getParkingId(),
            parkingName: $parking?->getName() ?? 'Unknown',
            startTime: $reservation->getStartTime(),
            endTime: $reservation->getEndTime(),
            estimatedPrice: $reservation->getEstimatedPrice(),
            status: $reservation->getStatus(),
            createdAt: $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            userEmail: null
        );
    }
}

