<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\SubscriptionOutput;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

final class ListUserSubscriptionsUseCase
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    /**
     * @return SubscriptionOutput[]
     */
    public function execute(string $userId): array
    {
        $subscriptions = $this->subscriptionRepository->findByUserId($userId);

        return array_map(function ($subscription) {
            $parking = $this->parkingRepository->findById($subscription->getParkingId());
            return new SubscriptionOutput(
                id: $subscription->getId(),
                parkingId: $subscription->getParkingId(),
                parkingName: $parking?->getName() ?? 'Unknown',
                type: $subscription->getType(),
                price: $subscription->getPrice(),
                startDate: $subscription->getStartDate(),
                endDate: $subscription->getEndDate(),
                isActive: $subscription->isActive()
            );
        }, $subscriptions);
    }
}

