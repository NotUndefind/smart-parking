<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\ListOverstayingUsersUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Entities\Reservation;

#[CoversClass(ListOverstayingUsersUseCase::class)]
#[CoversClass(Parking::class)]
#[CoversClass(Stationnement::class)]
#[CoversClass(Reservation::class)]
final class ListOverstayingUsersUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ListOverstayingUsersUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        $this->useCase = new ListOverstayingUsersUseCase(
            $this->parkingRepository,
            $this->stationnementRepository,
            $this->reservationRepository
        );
    }

    public function testCanListOverstayingUsers(): void
    {
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);

        $now = time();
        $reservation1 = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'endTime' => $now - 3600, // Ended 1 hour ago
            'status' => 'active'
        ]);

        $stationnement1 = EntityFactory::createStationnement([
            'id' => 'stationnement_1',
            'parkingId' => 'parking_1',
            'reservationId' => 'reservation_1',
            'status' => 'active'
        ]);

        $stationnement2 = EntityFactory::createStationnement([
            'id' => 'stationnement_2',
            'parkingId' => 'parking_1',
            'reservationId' => null,
            'status' => 'active'
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([$stationnement1, $stationnement2]);

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation1);

        $result = $this->useCase->execute('parking_1');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('stationnement_1', $result[0]->id);
        $this->assertEquals('Test Parking', $result[0]->parkingName);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->stationnementRepository->expects($this->never())
            ->method('findByParkingId');

        $this->expectException(\App\Domain\Exceptions\ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute('nonexistent_parking');
    }
}
