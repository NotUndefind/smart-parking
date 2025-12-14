<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

final class GetMonthlyRevenueUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private StationnementRepositoryInterface $stationnementRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function execute(string $parkingId, int $year, int $month): float
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Calculer le début et la fin du mois
        $startTimestamp = mktime(0, 0, 0, $month, 1, $year);
        $endTimestamp = mktime(23, 59, 59, $month, date('t', $startTimestamp), $year);

        $revenue = 0.0;

        // 3. Revenus des réservations complétées
        $reservations = $this->reservationRepository->findByParkingId($parkingId);
        foreach ($reservations as $reservation) {
            if ($reservation->getStatus() === 'completed' && 
                $reservation->getCreatedAt()->getTimestamp() >= $startTimestamp &&
                $reservation->getCreatedAt()->getTimestamp() <= $endTimestamp) {
                $revenue += $reservation->getEstimatedPrice();
            }
        }

        // 4. Revenus des stationnements (prix final + pénalités)
        $stationnements = $this->stationnementRepository->findByParkingId($parkingId);
        foreach ($stationnements as $stationnement) {
            if ($stationnement->getStatus() === 'completed' && 
                $stationnement->getExitTime() !== null &&
                $stationnement->getExitTime() >= $startTimestamp &&
                $stationnement->getExitTime() <= $endTimestamp) {
                $revenue += $stationnement->getFinalPrice() + $stationnement->getPenaltyAmount();
            }
        }

        // 5. Revenus des abonnements créés ce mois
        $subscriptions = $this->subscriptionRepository->findByParkingId($parkingId);
        foreach ($subscriptions as $subscription) {
            if ($subscription->getCreatedAt()->getTimestamp() >= $startTimestamp &&
                $subscription->getCreatedAt()->getTimestamp() <= $endTimestamp) {
                $revenue += $subscription->getPrice();
            }
        }

        return round($revenue, 2);
    }
}

