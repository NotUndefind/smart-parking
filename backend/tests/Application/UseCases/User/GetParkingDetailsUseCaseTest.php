<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\GetParkingDetailsUseCase;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\ParkingDetailsOutput;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Subscription;

#[CoversClass(GetParkingDetailsUseCase::class)]
final class GetParkingDetailsUseCaseTest extends TestCase
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

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Central Parking',
            'address' => '123 Main St',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'totalSpots' => 100,
            'tariffs' => [['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 3.0]],
            'schedule' => ['monday' => ['open' => '08:00', 'close' => '20:00']],
            'isActive' => true
        ]);

        $reservation1 = EntityFactory::createReservation([
            'parkingId' => 'parking_1',
            'status' => 'active'
        ]);

        $reservation2 = EntityFactory::createReservation([
            'parkingId' => 'parking_1',
            'status' => 'active'
        ]);

        $subscription1 = EntityFactory::createSubscription([
            'parkingId' => 'parking_1',
            'startDate' => $timestamp - 86400,
            'endDate' => $timestamp + 86400,
            'isActive' => true
        ]);

        $subscription2 = EntityFactory::createSubscription([
            'parkingId' => 'parking_1',
            'startDate' => $timestamp + 86400, // Starts tomorrow, not valid yet
            'endDate' => $timestamp + (2 * 86400),
            'isActive' => true
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findActiveByParking')
            ->with('parking_1', $timestamp, $timestamp + 3600)
            ->willReturn([$reservation1, $reservation2]);

        $this->subscriptionRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([$subscription1, $subscription2]);

        $result = $this->useCase->execute('parking_1', $timestamp);

        $this->assertInstanceOf(ParkingDetailsOutput::class, $result);
        $this->assertEquals('parking_1', $result->id);
        $this->assertEquals('Central Parking', $result->name);
        $this->assertEquals('123 Main St', $result->address);
        $this->assertEquals(48.8566, $result->latitude);
        $this->assertEquals(2.3522, $result->longitude);
        $this->assertEquals(100, $result->totalSpots);
        // 2 active reservations + 1 valid subscription = 3 occupied spots
        // 100 total - 3 occupied = 97 available
        $this->assertEquals(97, $result->availableSpots);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->reservationRepository->expects($this->never())
            ->method('findActiveByParking');

        $this->subscriptionRepository->expects($this->never())
            ->method('findByParkingId');

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute('nonexistent_parking', time());
    }
}
