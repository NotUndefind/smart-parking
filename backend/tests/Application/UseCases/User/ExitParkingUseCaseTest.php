<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\ExitParkingInput;
use App\Application\UseCases\User\ExitParkingUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ExitParkingUseCaseTest extends TestCase
{
    private StationnementRepositoryInterface $stationnementRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ExitParkingUseCase $useCase;

    protected function setUp(): void
    {
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        $this->useCase = new ExitParkingUseCase(
            $this->stationnementRepository,
            $this->parkingRepository,
            $this->reservationRepository
        );
    }

    public function testCanExitParking(): void
    {
        $input = new ExitParkingInput(
            stationnementId: 'stationnement_1'
        );

        $stationnement = $this->createMock(Stationnement::class);
        $stationnement->method('isActive')->willReturn(true);
        $stationnement->method('getId')->willReturn('stationnement_1');
        $stationnement->method('getParkingId')->willReturn('parking_1');
        $stationnement->method('getEntryTime')->willReturn(time() - 7200);
        $stationnement->method('getReservationId')->willReturn(null);
        $stationnement->method('getExitTime')->willReturn(time());
        $stationnement->method('getFinalPrice')->willReturn(20.0);
        $stationnement->method('getPenaltyAmount')->willReturn(0.0);
        $stationnement->method('getStatus')->willReturn('completed');

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');
        $parking->method('calculatePrice')->willReturn(20.0);

        $this->stationnementRepository->expects($this->once())
            ->method('findById')
            ->with('stationnement_1')
            ->willReturn($stationnement);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $stationnement->expects($this->once())
            ->method('exit')
            ->with($this->anything(), 20.0, 0.0);

        $this->stationnementRepository->expects($this->once())
            ->method('save')
            ->with($stationnement);

        $output = $this->useCase->execute($input);

        $this->assertEquals('stationnement_1', $output->id);
        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
    }
}
