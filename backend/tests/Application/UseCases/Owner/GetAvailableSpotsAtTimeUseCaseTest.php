<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\GetAvailableSpotsAtTimeUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GetAvailableSpotsAtTimeUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private GetAvailableSpotsAtTimeUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $this->useCase = new GetAvailableSpotsAtTimeUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->subscriptionRepository
        );
    }

    public function testCanGetAvailableSpotsAtTime(): void
    {
        $timestamp = time();

        $parking = $this->createMock(Parking::class);
        $parking->method('getTotalSpots')->willReturn(100);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findActiveByParking')
            ->with('parking_1', $timestamp, $timestamp + 3600)
            ->willReturn([]);

        $this->subscriptionRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([]);

        $availableSpots = $this->useCase->execute('parking_1', $timestamp);

        $this->assertEquals(100, $availableSpots);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute('nonexistent', time());
    }
}
