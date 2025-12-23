<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListUserReservationsUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;

#[CoversClass(ListUserReservationsUseCase::class)]
final class ListUserReservationsUseCaseTest extends TestCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ListUserReservationsUseCase $useCase;

    protected function setUp(): void
    {
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListUserReservationsUseCase(
            $this->reservationRepository,
            $this->parkingRepository
        );
    }

    public function testCanListUserReservations(): void
    {
        $reservation1 = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'startTime' => 1000,
            'endTime' => 2000,
            'estimatedPrice' => 10.50,
            'status' => 'active'
        ]);

        $reservation2 = EntityFactory::createReservation([
            'id' => 'reservation_2',
            'userId' => 'user_1',
            'parkingId' => 'parking_2',
            'startTime' => 3000,
            'endTime' => 4000,
            'estimatedPrice' => 15.00,
            'status' => 'completed'
        ]);

        $parking1 = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Central Parking'
        ]);

        $parking2 = EntityFactory::createParking([
            'id' => 'parking_2',
            'name' => 'Airport Parking'
        ]);

        $this->reservationRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_1')
            ->willReturn([$reservation1, $reservation2]);

        $this->parkingRepository->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function ($parkingId) use ($parking1, $parking2) {
                return match ($parkingId) {
                    'parking_1' => $parking1,
                    'parking_2' => $parking2,
                    default => null
                };
            });

        $result = $this->useCase->execute('user_1');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ReservationOutput::class, $result[0]);
        $this->assertEquals('reservation_1', $result[0]->id);
        $this->assertEquals('Central Parking', $result[0]->parkingName);
        $this->assertEquals('active', $result[0]->status);
        $this->assertEquals('reservation_2', $result[1]->id);
        $this->assertEquals('Airport Parking', $result[1]->parkingName);
        $this->assertEquals('completed', $result[1]->status);
    }

    public function testReturnsEmptyArrayWhenNoReservations(): void
    {
        $this->reservationRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_without_reservations')
            ->willReturn([]);

        $this->parkingRepository->expects($this->never())
            ->method('findById');

        $result = $this->useCase->execute('user_without_reservations');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
