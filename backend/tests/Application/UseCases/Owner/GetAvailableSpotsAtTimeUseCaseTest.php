<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\GetAvailableSpotsAtTimeUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Subscription;

#[CoversClass(GetAvailableSpotsAtTimeUseCase::class)]
#[CoversClass(Parking::class)]
#[CoversClass(Reservation::class)]
#[CoversClass(Subscription::class)]
final class GetAvailableSpotsAtTimeUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private GetAvailableSpotsAtTimeUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(
            ParkingRepositoryInterface::class,
        );
        $this->reservationRepository = $this->createMock(
            ReservationRepositoryInterface::class,
        );
        $this->subscriptionRepository = $this->createMock(
            SubscriptionRepositoryInterface::class,
        );

        $this->useCase = new GetAvailableSpotsAtTimeUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->subscriptionRepository,
        );
    }

    public function testGetAvailableSpotsAtTime(): void
    {
        $parkingId = "parking_1";
        $timestamp = 1672531200; // Exemple de timestamp

        $parking = EntityFactory::createParking([
            "id" => $parkingId,
            "totalSpots" => 100,
        ]);

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with($parkingId)
            ->willReturn($parking);

        $this->reservationRepository
            ->expects($this->once())
            ->method("findActiveByParking")
            ->with($parkingId, $timestamp, $timestamp + 3600)
            ->willReturn([
                EntityFactory::createReservation([]),
                EntityFactory::createReservation([]),
            ]);

        $this->subscriptionRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with($parkingId)
            ->willReturn([
                EntityFactory::createSubscription([
                    "startDate" => $timestamp - 1000,
                    "endDate" => $timestamp + 1000,
                ]),
                EntityFactory::createSubscription([
                    "startDate" => $timestamp - 2000,
                    "endDate" => $timestamp - 1000,
                ]),
            ]);

        $output = $this->useCase->execute($parkingId, $timestamp);

        $this->assertEquals(97, $output->availableSpots);
        $this->assertEquals(100, $output->totalSpots);
        $this->assertEquals(3, $output->occupiedSpots);
    }

    // TODO: Ajouter vos tests ici
}
