<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\DTOs\Input\ExitParkingInput;
use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;

final class ExitParkingUseCase
{
    private const PENALTY_RATE_PER_HOUR = 5.0; // € par heure de dépassement

    public function __construct(
        private StationnementRepositoryInterface $stationnementRepository,
        private ParkingRepositoryInterface $parkingRepository,
        private ReservationRepositoryInterface $reservationRepository
    ) {
    }

    public function execute(ExitParkingInput $input): StationnementOutput
    {
        $exitTime = time();

        // 1. Trouver le stationnement
        $stationnement = $this->stationnementRepository->findById($input->stationnementId);
        if ($stationnement === null || !$stationnement->isActive()) {
            throw new UserNotFoundException('Active stationnement not found');
        }

        // 2. Récupérer le parking
        $parking = $this->parkingRepository->findById($stationnement->getParkingId());
        if ($parking === null) {
            throw new UserNotFoundException('Parking not found');
        }

        // 3. Calculer le prix final et les pénalités
        $durationMinutes = ($exitTime - $stationnement->getEntryTime()) / 60;
        $finalPrice = $parking->calculatePrice((int)$durationMinutes);
        $penaltyAmount = 0.0;

        // 4. Vérifier les pénalités si réservation
        if ($stationnement->getReservationId() !== null) {
            $reservation = $this->reservationRepository->findById($stationnement->getReservationId());
            if ($reservation !== null && $exitTime > $reservation->getEndTime()) {
                $overtimeHours = ($exitTime - $reservation->getEndTime()) / 3600;
                $penaltyAmount = $overtimeHours * self::PENALTY_RATE_PER_HOUR;
            }
        }

        // 5. Mettre à jour le stationnement
        $stationnement->exit($exitTime, $finalPrice, $penaltyAmount);

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
            status: $stationnement->getStatus()
        );
    }
}

