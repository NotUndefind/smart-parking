<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\GetMonthlyRevenueUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GetMonthlyRevenueUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private GetMonthlyRevenueUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $this->useCase = new GetMonthlyRevenueUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->stationnementRepository,
            $this->subscriptionRepository
        );
    }

    public function testCanGetMonthlyRevenue(): void
    {
        $parking = $this->createMock(Parking::class);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([]);

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([]);

        $this->subscriptionRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([]);

        $revenue = $this->useCase->execute('parking_1', 2024, 1);

        $this->assertEquals(0.0, $revenue);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute('nonexistent', 2024, 1);
    }
}
