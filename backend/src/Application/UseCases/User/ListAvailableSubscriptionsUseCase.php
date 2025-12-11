<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\SubscriptionTypeOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class ListAvailableSubscriptionsUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return SubscriptionTypeOutput[]
     */
    public function execute(string $parkingId): array
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Retourner les types d'abonnements disponibles (configuration standard)
        return [
            new SubscriptionTypeOutput(type: 'daily', price: 10.0, durationDays: 1),
            new SubscriptionTypeOutput(type: 'weekly', price: 50.0, durationDays: 7),
            new SubscriptionTypeOutput(type: 'monthly', price: 150.0, durationDays: 30),
            new SubscriptionTypeOutput(type: 'yearly', price: 1500.0, durationDays: 365),
        ];
    }
}

