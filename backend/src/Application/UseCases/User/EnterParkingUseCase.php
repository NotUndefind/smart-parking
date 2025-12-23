<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Input\EnterParkingInput;
use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Entities\Stationnement;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class EnterParkingUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private StationnementRepositoryInterface $stationnementRepository
    ) {
    }

    public function execute(EnterParkingInput $input): StationnementOutput
    {
        $currentTime = time();

        // 1. Vérifier que l'utilisateur existe
        $user = $this->userRepository->findById($input->userId);
        if ($user === null) {
            throw new UserNotFoundException('User not found');
        }

        // 2. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($input->parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 3. Vérifier qu'il n'y a pas déjà un stationnement actif
        $activeStationnement = $this->stationnementRepository->findActiveByUserAndParking(
            $input->userId,
            $input->parkingId
        );
        if ($activeStationnement !== null) {
            throw new \RuntimeException('User already has an active parking session');
        }

        // 4. Valider réservation ou abonnement
        if ($input->reservationId !== null) {
            $reservation = $this->reservationRepository->findById($input->reservationId);
            if ($reservation === null || $reservation->getUserId() !== $input->userId || !$reservation->isActive()) {
                throw new \InvalidArgumentException('Invalid or inactive reservation');
            }
            if ($currentTime < $reservation->getStartTime() || $currentTime > $reservation->getEndTime()) {
                throw new \InvalidArgumentException('Reservation time slot has not started or has expired');
            }
        } elseif ($input->subscriptionId !== null) {
            $subscription = $this->subscriptionRepository->findById($input->subscriptionId);
            if ($subscription === null || $subscription->getUserId() !== $input->userId || !$subscription->isValidAt($currentTime)) {
                throw new \InvalidArgumentException('Invalid or expired subscription');
            }
        } else {
            throw new \InvalidArgumentException('Either reservation or subscription must be provided');
        }

        // 5. Créer le stationnement
        $stationnement = new Stationnement(
            id: uniqid('stationnement_', true),
            userId: $input->userId,
            parkingId: $input->parkingId,
            reservationId: $input->reservationId,
            subscriptionId: $input->subscriptionId,
            entryTime: $currentTime,
            status: 'active'
        );

        // 6. Sauvegarder
        $this->stationnementRepository->save($stationnement);

        // 7. Retourner le DTO Output
        return new StationnementOutput(
            id: $stationnement->getId(),
            parkingId: $stationnement->getParkingId(),
            parkingName: $parking->getName(),
            entryTime: $stationnement->getEntryTime(),
            exitTime: $stationnement->getExitTime(),
            finalPrice: $stationnement->getFinalPrice(),
            penaltyAmount: $stationnement->getPenaltyAmount(),
            status: $stationnement->getStatus(),
            userEmail: null
        );
    }
}

