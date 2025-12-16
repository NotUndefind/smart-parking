<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Input\SubscribeToPlanInput;
use App\Application\DTOs\Output\SubscriptionOutput;
use App\Domain\Entities\Subscription;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class SubscribeToPlanUseCase
{
    private const DURATION_DAYS = [
        'daily' => 1,
        'weekly' => 7,
        'monthly' => 30,
        'yearly' => 365,
    ];

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    public function execute(SubscribeToPlanInput $input): SubscriptionOutput
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

        // 3. Valider le type d'abonnement
        if (!isset(self::DURATION_DAYS[$input->type])) {
            throw new \InvalidArgumentException('Invalid subscription type');
        }

        // 4. Calculer les dates
        $durationDays = self::DURATION_DAYS[$input->type];
        $startDate = $currentTime;
        $endDate = $currentTime + ($durationDays * 24 * 3600);

        // 5. Créer l'abonnement
        $subscription = new Subscription(
            id: uniqid('subscription_', true),
            parkingId: $input->parkingId,
            userId: $input->userId,
            type: $input->type,
            price: $input->price,
            startDate: $startDate,
            endDate: $endDate,
            isActive: true
        );

        // 6. Sauvegarder
        $this->subscriptionRepository->save($subscription);

        // 7. Retourner le DTO Output
        return new SubscriptionOutput(
            id: $subscription->getId(),
            parkingId: $subscription->getParkingId(),
            parkingName: $parking->getName(),
            type: $subscription->getType(),
            price: $subscription->getPrice(),
            startDate: $subscription->getStartDate(),
            endDate: $subscription->getEndDate(),
            isActive: $subscription->isActive()
        );
    }
}

