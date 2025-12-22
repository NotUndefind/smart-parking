<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\GetParkingDetailsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GetParkingDetailsUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private GetParkingDetailsUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $this->useCase = new GetParkingDetailsUseCase(
            $this->parkingRepository,
            $this->reservationRepository,
            $this->subscriptionRepository
        );
    }

    public function testCanGetParkingDetails(): void
    {
        $timestamp = time();

        $parking = $this->createMock(Parking::class);
        $parking->method('getId')->willReturn('parking_1');
        $parking->method('getName')->willReturn('Test Parking');
        $parking->method('getAddress')->willReturn('123 Test Street');
        $parking->method('getLatitude')->willReturn(48.8566);
        $parking->method('getLongitude')->willReturn(2.3522);
        $parking->method('getTotalSpots')->willReturn(100);
        $parking->method('getTariffs')->willReturn(['hourly' => 5.0]);
        $parking->method('getSchedule')->willReturn(['monday' => '00:00-23:59']);
        $parking->method('isOpenAt')->willReturn(true);

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

        $output = $this->useCase->execute('parking_1', $timestamp);

        $this->assertEquals('parking_1', $output->id);
        $this->assertEquals('Test Parking', $output->name);
        $this->assertEquals('123 Test Street', $output->address);
        $this->assertEquals(100, $output->totalSpots);
        $this->assertEquals(100, $output->availableSpots);
        $this->assertTrue($output->isOpen);
    }
}
