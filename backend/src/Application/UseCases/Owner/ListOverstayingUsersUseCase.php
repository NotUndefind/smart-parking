<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class ListOverstayingUsersUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository,
        private StationnementRepositoryInterface $stationnementRepository,
        private ReservationRepositoryInterface $reservationRepository,
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

        // 2. Récupérer les stationnements actifs
        $stationnements = $this->stationnementRepository->findByParkingId($parkingId);
        $activeStationnements = array_filter($stationnements, function ($s) {
            return $s->isActive();
        });

        $overstaying = [];

        // 3. Vérifier chaque stationnement actif
        foreach ($activeStationnements as $stationnement) {
            $currentTime = time();
            $isOverstaying = false;

            // Si c'est une réservation, vérifier le dépassement
            if ($stationnement->getReservationId() !== null) {
                $reservation = $this->reservationRepository->findById($stationnement->getReservationId());
                if ($reservation !== null && $currentTime > $reservation->getEndTime()) {
                    $isOverstaying = true;
                }
            }
            // Si c'est un abonnement, vérifier la date d'expiration
            elseif ($stationnement->getSubscriptionId() !== null) {
                // Les abonnements sont gérés par Subscription, on considère qu'ils sont toujours valides
                // sauf si on veut vérifier autre chose
            }

            if ($isOverstaying) {
                $overstaying[] = $stationnement;
            }
        }

        // 4. Retourner les DTOs Output
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
        }, $overstaying);
    }
}

