<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\ListParkingStationnementsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListParkingStationnementsUseCaseTest extends TestCase
{
    private StationnementRepositoryInterface $stationnementRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ListParkingStationnementsUseCase $useCase;

    protected function setUp(): void
    {
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListParkingStationnementsUseCase(
            $this->stationnementRepository,
            $this->parkingRepository
        );
    }

    public function testCanListParkingStationnements(): void
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $stationnement = $this->createMock(Stationnement::class);
        $stationnement->method('getId')->willReturn('stationnement_1');
        $stationnement->method('getParkingId')->willReturn('parking_1');
        $stationnement->method('getEntryTime')->willReturn(time() - 7200);
        $stationnement->method('getExitTime')->willReturn(time());
        $stationnement->method('getFinalPrice')->willReturn(20.0);
        $stationnement->method('getPenaltyAmount')->willReturn(0.0);
        $stationnement->method('getStatus')->willReturn('completed');

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([$stationnement]);

        $output = $this->useCase->execute('parking_1');

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('stationnement_1', $output[0]->id);
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
