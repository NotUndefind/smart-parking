<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Input\AddSubscriptionTypeInput;
use App\Application\DTOs\Output\SubscriptionTypeOutput;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class AddSubscriptionTypeUseCase
{
    public function __construct(
        private ParkingRepositoryInterface $parkingRepository
    ) {
    }

    public function execute(AddSubscriptionTypeInput $input): SubscriptionTypeOutput
    {
        // 1. Vérifier que le parking existe
        $parking = $this->parkingRepository->findById($input->parkingId);
        if ($parking === null) {
            throw new ParkingNotFoundException('Parking not found');
        }

        // 2. Valider le type d'abonnement
        $validTypes = ['daily', 'weekly', 'monthly', 'yearly'];
        if (!in_array($input->type, $validTypes)) {
            throw new \InvalidArgumentException('Invalid subscription type. Must be one of: ' . implode(', ', $validTypes));
        }

        // 3. Retourner le DTO Output (le type d'abonnement est juste une configuration, pas une entité)
        return new SubscriptionTypeOutput(
            type: $input->type,
            price: $input->price,
            durationDays: $input->durationDays
        );
    }
}

