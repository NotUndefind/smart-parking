<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListUserReservationsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListUserReservationsUseCaseTest extends TestCase
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
        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getId')->willReturn('reservation_1');
        $reservation->method('getParkingId')->willReturn('parking_1');
        $reservation->method('getStartTime')->willReturn(time() + 3600);
        $reservation->method('getEndTime')->willReturn(time() + 7200);
        $reservation->method('getEstimatedPrice')->willReturn(15.0);
        $reservation->method('getStatus')->willReturn('active');
        $reservation->method('getCreatedAt')->willReturn(new \DateTime());

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $this->reservationRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_1')
            ->willReturn([$reservation]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('user_1');

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('reservation_1', $output[0]->id);
        $this->assertEquals('Test Parking', $output[0]->parkingName);
    }
}
