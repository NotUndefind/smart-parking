<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListUserStationnementsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListUserStationnementsUseCaseTest extends TestCase
{
    private StationnementRepositoryInterface $stationnementRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ListUserStationnementsUseCase $useCase;

    protected function setUp(): void
    {
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListUserStationnementsUseCase(
            $this->stationnementRepository,
            $this->parkingRepository
        );
    }

    public function testCanListUserStationnements(): void
    {
        $stationnement = $this->createMock(Stationnement::class);
        $stationnement->method('getId')->willReturn('stationnement_1');
        $stationnement->method('getParkingId')->willReturn('parking_1');
        $stationnement->method('getEntryTime')->willReturn(time() - 7200);
        $stationnement->method('getExitTime')->willReturn(time());
        $stationnement->method('getFinalPrice')->willReturn(20.0);
        $stationnement->method('getPenaltyAmount')->willReturn(0.0);
        $stationnement->method('getStatus')->willReturn('completed');

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $this->stationnementRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_1')
            ->willReturn([$stationnement]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('user_1');

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('stationnement_1', $output[0]->id);
        $this->assertEquals('Test Parking', $output[0]->parkingName);
    }
}
