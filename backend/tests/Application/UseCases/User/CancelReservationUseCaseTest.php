<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\CancelReservationUseCase;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;

#[CoversClass(CancelReservationUseCase::class)]
final class CancelReservationUseCaseTest extends TestCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private CancelReservationUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repositories (interfaces)
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new CancelReservationUseCase(
            $this->reservationRepository,
            $this->parkingRepository
        );
    }

    public function testCanCancelReservation(): void
    {
        $reservation = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'status' => 'active'
        ]);

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation);

        $this->reservationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($res) {
                return $res->getStatus() === 'cancelled';
            }));

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('reservation_1', 'user_1');

        $this->assertEquals('reservation_1', $output->id);
        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('cancelled', $output->status);
    }

    public function testThrowsExceptionWhenReservationNotFound(): void
    {
        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_reservation')
            ->willReturn(null);

        $this->reservationRepository->expects($this->never())
            ->method('save');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Reservation not found');

        $this->useCase->execute('nonexistent_reservation', 'user_1');
    }

    public function testThrowsExceptionWhenAlreadyCompleted(): void
    {
        $reservation = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'userId' => 'user_1',
            'status' => 'completed'
        ]);

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation);

        $this->reservationRepository->expects($this->never())
            ->method('save');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot cancel a completed reservation');

        $this->useCase->execute('reservation_1', 'user_1');
    }
}
