<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\GetMonthlyRevenueUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Stationnement;
use App\Domain\Entities\Subscription;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(GetMonthlyRevenueUseCase::class)]
#[CoversClass(Parking::class)]
#[CoversClass(Reservation::class)]
#[CoversClass(Stationnement::class)]
#[CoversClass(Subscription::class)]
final class GetMonthlyRevenueUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private GetMonthlyRevenueUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(
            ParkingRepositoryInterface::class,
        );
        $this->reservationRepository = $this->createMock(
            ReservationRepositoryInterface::class,
        );
        $this->stationnementRepository = $this->createMock(
            StationnementRepositoryInterface::class,
        );
        $this->subscriptionRepository = $this->createMock(
            SubscriptionRepositoryInterface::class,
        );

        $this->useCase = new GetMonthlyRevenueUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->stationnementRepository,
            $this->subscriptionRepository,
        );
    }

    public function testCalculatesMonthlyRevenueCorrectly(): void
    {
        $parking = EntityFactory::createParking(["id" => "parking_1"]);

        // January 2024: 1er janvier à minuit jusqu'au 31 janvier 23:59:59
        $januaryStart = mktime(0, 0, 0, 1, 1, 2024);
        $januaryMid = mktime(12, 0, 0, 1, 15, 2024);

        // Reservations du mois (completed)
        $reservation1 = EntityFactory::createReservation([
            "parkingId" => "parking_1",
            "estimatedPrice" => 25.5,
            "status" => "completed",
            "createdAt" => new \DateTimeImmutable("@" . $januaryMid),
        ]);

        // Stationnements du mois (completed avec prix final + pénalité)
        $stationnement1 = EntityFactory::createStationnement([
            "parkingId" => "parking_1",
            "exitTime" => $januaryMid,
            "finalPrice" => 15.0,
            "penaltyAmount" => 5.0,
            "status" => "completed",
        ]);

        // Subscriptions du mois
        $subscription1 = EntityFactory::createSubscription([
            "parkingId" => "parking_1",
            "price" => 99.99,
            "createdAt" => new \DateTimeImmutable("@" . $januaryMid),
        ]);

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("parking_1")
            ->willReturn($parking);

        $this->reservationRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([$reservation1]);

        $this->stationnementRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([$stationnement1]);

        $this->subscriptionRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([$subscription1]);

        $revenue = $this->useCase->execute("parking_1", 2024, 1);

        // 25.50 (reservation) + 15.00 (stationnement) + 5.00 (penalty) + 99.99 (subscription) = 145.49
        $this->assertEquals(145.49, $revenue);
    }

    public function testReturnsZeroWhenNoRevenue(): void
    {
        $parking = EntityFactory::createParking(["id" => "parking_1"]);

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("parking_1")
            ->willReturn($parking);

        $this->reservationRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([]);

        $this->stationnementRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([]);

        $this->subscriptionRepository
            ->expects($this->once())
            ->method("findByParkingId")
            ->with("parking_1")
            ->willReturn([]);

        $revenue = $this->useCase->execute("parking_1", 2024, 1);

        $this->assertEquals(0.0, $revenue);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("nonexistent_parking")
            ->willReturn(null);

        $this->reservationRepository
            ->expects($this->never())
            ->method("findByParkingId");

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage("Parking not found");

        $this->useCase->execute("nonexistent_parking", 2024, 1);
    }
}
