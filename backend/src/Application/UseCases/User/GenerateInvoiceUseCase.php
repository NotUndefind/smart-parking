<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Output\InvoiceOutput;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

final class GenerateInvoiceUseCase
{
    public function __construct(
        private StationnementRepositoryInterface $stationnementRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function execute(string $stationnementId): InvoiceOutput
    {
        // 1. Trouver le stationnement
        $stationnement = $this->stationnementRepository->findById($stationnementId);
        if ($stationnement === null || $stationnement->getStatus() !== 'completed') {
            throw new UserNotFoundException('Completed stationnement not found');
        }

        // 2. Récupérer le parking
        $parking = $this->parkingRepository->findById($stationnement->getParkingId());
        if ($parking === null) {
            throw new UserNotFoundException('Parking not found');
        }

        // 3. Récupérer l'utilisateur
        $user = $this->userRepository->findById($stationnement->getUserId());
        if ($user === null) {
            throw new UserNotFoundException('User not found');
        }

        // 4. Calculer le total
        $totalAmount = $stationnement->getFinalPrice() + $stationnement->getPenaltyAmount();

        // 5. Retourner le DTO Output
        return new InvoiceOutput(
            id: uniqid('invoice_', true),
            stationnementId: $stationnement->getId(),
            parkingName: $parking->getName(),
            userName: $user->getFullName(),
            entryTime: $stationnement->getEntryTime(),
            exitTime: $stationnement->getExitTime() ?? 0,
            finalPrice: $stationnement->getFinalPrice(),
            penaltyAmount: $stationnement->getPenaltyAmount(),
            totalAmount: $totalAmount,
            generatedAt: date('Y-m-d H:i:s')
        );
    }
}

