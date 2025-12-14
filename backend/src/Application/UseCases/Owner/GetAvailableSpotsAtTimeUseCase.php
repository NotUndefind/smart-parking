<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

final class GetAvailableSpotsAtTimeUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function execute(string $parkingId, int $timestamp): int
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Calculer les places occupées par les réservations actives
        $activeReservations = $this->reservationRepository->findActiveByParking(
            $parkingId,
            $timestamp,
            $timestamp + 3600 // 1 heure après
        );

        // 3. Compter les abonnements actifs
        $activeSubscriptions = $this->subscriptionRepository->findByParkingId($parkingId);
        $activeSubscriptionsCount = count(array_filter($activeSubscriptions, function ($sub) use ($timestamp) {
            return $sub->isValidAt($timestamp);
        }));

        // 4. Calculer les places disponibles
        $occupiedSpots = count($activeReservations) + $activeSubscriptionsCount;
        return max(0, $parking->getTotalSpots() - $occupiedSpots);
    }
}

