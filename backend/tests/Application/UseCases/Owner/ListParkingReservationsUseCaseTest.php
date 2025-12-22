<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\ListParkingReservationsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListParkingReservationsUseCaseTest extends TestCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ListParkingReservationsUseCase $useCase;

    protected function setUp(): void
    {
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListParkingReservationsUseCase(
            $this->reservationRepository,
            $this->parkingRepository
        );
    }

    public function testCanListParkingReservations(): void
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getId')->willReturn('reservation_1');
        $reservation->method('getParkingId')->willReturn('parking_1');
        $reservation->method('getStartTime')->willReturn(time() + 3600);
        $reservation->method('getEndTime')->willReturn(time() + 7200);
        $reservation->method('getEstimatedPrice')->willReturn(15.0);
        $reservation->method('getStatus')->willReturn('active');
        $reservation->method('getCreatedAt')->willReturn(new \DateTime());

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([$reservation]);

        $output = $this->useCase->execute('parking_1');

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('reservation_1', $output[0]->id);
        $this->assertEquals('Test Parking', $output[0]->parkingName);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute('nonexistent');
    }
}
