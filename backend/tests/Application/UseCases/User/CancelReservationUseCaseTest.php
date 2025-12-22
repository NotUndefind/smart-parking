<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\CancelReservationUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CancelReservationUseCaseTest extends TestCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private CancelReservationUseCase $useCase;

    protected function setUp(): void
    {
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new CancelReservationUseCase(
            $this->reservationRepository,
            $this->parkingRepository
        );
    }

    public function testCanCancelReservation(): void
    {
        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getId')->willReturn('reservation_1');
        $reservation->method('getUserId')->willReturn('user_1');
        $reservation->method('getParkingId')->willReturn('parking_1');
        $reservation->method('getStartTime')->willReturn(time() + 3600);
        $reservation->method('getEndTime')->willReturn(time() + 7200);
        $reservation->method('getEstimatedPrice')->willReturn(15.0);
        $reservation->method('getStatus')->willReturn('cancelled');
        $reservation->method('getCreatedAt')->willReturn(new \DateTime());

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation);

        $reservation->expects($this->once())
            ->method('cancel');

        $this->reservationRepository->expects($this->once())
            ->method('save')
            ->with($reservation);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('reservation_1', 'user_1');

        $this->assertEquals('reservation_1', $output->id);
        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('cancelled', $output->status);
    }
}
