<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListUserStationnementsUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;

#[CoversClass(ListUserStationnementsUseCase::class)]
final class ListUserStationnementsUseCaseTest extends TestCase
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
        $stationnement1 = EntityFactory::createStationnement([
            'id' => 'stationnement_1',
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'entryTime' => 1000,
            'exitTime' => 2000,
            'finalPrice' => 12.50,
            'penaltyAmount' => 0.0,
            'status' => 'completed'
        ]);

        $stationnement2 = EntityFactory::createStationnement([
            'id' => 'stationnement_2',
            'userId' => 'user_1',
            'parkingId' => 'parking_2',
            'entryTime' => 3000,
            'exitTime' => null,
            'finalPrice' => 0.0,
            'penaltyAmount' => 0.0,
            'status' => 'active'
        ]);

        $parking1 = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Downtown Parking'
        ]);

        $parking2 = EntityFactory::createParking([
            'id' => 'parking_2',
            'name' => 'Mall Parking'
        ]);

        $this->stationnementRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_1')
            ->willReturn([$stationnement1, $stationnement2]);

        $this->parkingRepository->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function ($parkingId) use ($parking1, $parking2) {
                return match ($parkingId) {
                    'parking_1' => $parking1,
                    'parking_2' => $parking2,
                    default => null
                };
            });

        $result = $this->useCase->execute('user_1');

        $this->assertCount(2, $result);
        $this->assertInstanceOf(StationnementOutput::class, $result[0]);
        $this->assertEquals('stationnement_1', $result[0]->id);
        $this->assertEquals('Downtown Parking', $result[0]->parkingName);
        $this->assertEquals('completed', $result[0]->status);
        $this->assertEquals(12.50, $result[0]->finalPrice);
        $this->assertEquals('stationnement_2', $result[1]->id);
        $this->assertEquals('Mall Parking', $result[1]->parkingName);
        $this->assertEquals('active', $result[1]->status);
    }

    public function testReturnsEmptyArrayWhenNoStationnements(): void
    {
        $this->stationnementRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_without_stationnements')
            ->willReturn([]);

        $this->parkingRepository->expects($this->never())
            ->method('findById');

        $result = $this->useCase->execute('user_without_stationnements');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
