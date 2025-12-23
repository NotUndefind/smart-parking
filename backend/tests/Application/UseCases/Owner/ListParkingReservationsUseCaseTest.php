<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Output\ReservationOutput;
use App\Application\UseCases\Owner\ListParkingReservationsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(ListParkingReservationsUseCase::class)]
final class ListParkingReservationsUseCaseTest extends TestCase
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
        $parkingId = 'parking_1';
        $parking = EntityFactory::createParking([
            'id' => $parkingId,
            'name' => 'Test Parking'
        ]);

        $reservation1 = EntityFactory::createReservation([
            'id' => 'res_1',
            'parkingId' => $parkingId,
            'startTime' => time(),
            'endTime' => time() + 3600,
            'estimatedPrice' => 5.0,
            'status' => 'active'
        ]);

        $reservation2 = EntityFactory::createReservation([
            'id' => 'res_2',
            'parkingId' => $parkingId,
            'startTime' => time() + 7200,
            'endTime' => time() + 10800,
            'estimatedPrice' => 7.5,
            'status' => 'active'
        ]);

        $reservations = [$reservation1, $reservation2];

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with($parkingId)
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findByParkingId')
            ->with($parkingId)
            ->willReturn($reservations);

        $outputs = $this->useCase->execute($parkingId);

        $this->assertIsArray($outputs);
        $this->assertCount(2, $outputs);
        $this->assertInstanceOf(ReservationOutput::class, $outputs[0]);
        $this->assertInstanceOf(ReservationOutput::class, $outputs[1]);
        $this->assertEquals('res_1', $outputs[0]->id);
        $this->assertEquals('res_2', $outputs[1]->id);
        $this->assertEquals($parkingId, $outputs[0]->parkingId);
        $this->assertEquals('Test Parking', $outputs[0]->parkingName);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $parkingId = 'nonexistent_parking';

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with($parkingId)
            ->willReturn(null);

        $this->reservationRepository->expects($this->never())
            ->method('findByParkingId');

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($parkingId);
    }

    public function testReturnsEmptyArrayWhenNoReservations(): void
    {
        $parkingId = 'parking_1';
        $parking = EntityFactory::createParking([
            'id' => $parkingId,
            'name' => 'Empty Parking'
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with($parkingId)
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findByParkingId')
            ->with($parkingId)
            ->willReturn([]);

        $outputs = $this->useCase->execute($parkingId);

        $this->assertIsArray($outputs);
        $this->assertEmpty($outputs);
    }
}
