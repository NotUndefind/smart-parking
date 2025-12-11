<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Input\CreateReservationInput;
use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Entities\Reservation;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class CreateReservationUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository
    ) {
    }

    public function execute(CreateReservationInput $input): ReservationOutput
    {
        // 1. Vérifier que l'utilisateur existe
        $user = $this->userRepository->findById($input->userId);
        if ($user === null) {
            throw new UserNotFoundException('User not found');
        }

        // 2. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($input->parkingId);
        if ($parking === null) {
            throw new UserNotFoundException('Parking not found');
        }

        // 3. Vérifier que le parking est ouvert aux heures demandées
        if (!$parking->isOpenAt($input->startTime) || !$parking->isOpenAt($input->endTime)) {
            throw new \InvalidArgumentException('Parking is closed during the requested time slot');
        }

        // 4. Vérifier la disponibilité
        $activeReservations = $this->reservationRepository->findActiveByParking(
            $input->parkingId,
            $input->startTime,
            $input->endTime
        );

        if (count($activeReservations) >= $parking->getTotalSpots()) {
            throw new \RuntimeException('Parking is full during the requested time slot');
        }

        // 5. Calculer le prix estimé
        $durationMinutes = ($input->endTime - $input->startTime) / 60;
        $estimatedPrice = $parking->calculatePrice((int)$durationMinutes);

        // 6. Créer la réservation
        $reservation = new Reservation(
            id: uniqid('reservation_', true),
            userId: $input->userId,
            parkingId: $input->parkingId,
            startTime: $input->startTime,
            endTime: $input->endTime,
            estimatedPrice: $estimatedPrice,
            status: 'active'
        );

        // 7. Sauvegarder
        $this->reservationRepository->save($reservation);

        // 8. Retourner le DTO Output
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
    }
}

