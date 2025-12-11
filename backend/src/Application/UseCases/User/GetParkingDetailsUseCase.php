<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\ParkingDetailsOutput;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

final class GetParkingDetailsUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function execute(string $parkingId, int $timestamp): ParkingDetailsOutput
    {
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new UserNotFoundException('Parking not found');
        }

        // Calculer les places disponibles à ce timestamp
        $activeReservations = $this->reservationRepository->findActiveByParking(
            $parkingId,
            $timestamp,
            $timestamp + 3600 // 1 heure après
        );

        // Compter les abonnements actifs à ce timestamp
        $activeSubscriptions = $this->subscriptionRepository->findByParkingId($parkingId);
        $activeSubscriptionsCount = count(array_filter($activeSubscriptions, function ($sub) use ($timestamp) {
            return $sub->isValidAt($timestamp);
        }));

        $occupiedSpots = count($activeReservations) + $activeSubscriptionsCount;
        $availableSpots = max(0, $parking->getTotalSpots() - $occupiedSpots);

        return new ParkingDetailsOutput(
            id: $parking->getId(),
            name: $parking->getName(),
            address: $parking->getAddress(),
            latitude: $parking->getLatitude(),
            longitude: $parking->getLongitude(),
            totalSpots: $parking->getTotalSpots(),
            availableSpots: $availableSpots,
            tariffs: $parking->getTariffs(),
            schedule: $parking->getSchedule(),
            isOpen: $parking->isOpenAt($timestamp)
        );
    }
}

