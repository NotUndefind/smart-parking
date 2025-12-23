<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Output\StationnementOutput;
use App\Application\UseCases\Owner\ListParkingStationnementsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(ListParkingStationnementsUseCase::class)]
final class ListParkingStationnementsUseCaseTest extends TestCase
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
        $parkingId = 'parking_1';
        $parking = EntityFactory::createParking([
            'id' => $parkingId,
            'name' => 'Test Parking'
        ]);

        $stationnement1 = EntityFactory::createStationnement([
            'id' => 'stat_1',
            'parkingId' => $parkingId,
            'entryTime' => time() - 3600,
            'exitTime' => time(),
            'finalPrice' => 5.0,
            'penaltyAmount' => 0.0,
            'status' => 'completed'
        ]);

        $stationnement2 = EntityFactory::createStationnement([
            'id' => 'stat_2',
            'parkingId' => $parkingId,
            'entryTime' => time(),
            'exitTime' => null,
            'finalPrice' => 0.0,
            'penaltyAmount' => 0.0,
            'status' => 'active'
        ]);

        $stationnements = [$stationnement1, $stationnement2];

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with($parkingId)
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with($parkingId)
            ->willReturn($stationnements);

        $outputs = $this->useCase->execute($parkingId);

        $this->assertIsArray($outputs);
        $this->assertCount(2, $outputs);
        $this->assertInstanceOf(StationnementOutput::class, $outputs[0]);
        $this->assertInstanceOf(StationnementOutput::class, $outputs[1]);
        $this->assertEquals('stat_1', $outputs[0]->id);
        $this->assertEquals('stat_2', $outputs[1]->id);
        $this->assertEquals($parkingId, $outputs[0]->parkingId);
        $this->assertEquals('Test Parking', $outputs[0]->parkingName);
        $this->assertEquals('completed', $outputs[0]->status);
        $this->assertEquals('active', $outputs[1]->status);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $parkingId = 'nonexistent_parking';

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with($parkingId)
            ->willReturn(null);

        $this->stationnementRepository->expects($this->never())
            ->method('findByParkingId');

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($parkingId);
    }

    public function testReturnsEmptyArrayWhenNoStationnements(): void
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

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with($parkingId)
            ->willReturn([]);

        $outputs = $this->useCase->execute($parkingId);

        $this->assertIsArray($outputs);
        $this->assertEmpty($outputs);
    }
}
