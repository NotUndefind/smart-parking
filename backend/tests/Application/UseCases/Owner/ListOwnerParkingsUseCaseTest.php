<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\ListOwnerParkingsUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\OwnerRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Owner;

#[CoversClass(ListOwnerParkingsUseCase::class)]
#[CoversClass(Parking::class)]
#[CoversClass(Owner::class)]
final class ListOwnerParkingsUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private OwnerRepositoryInterface $ownerRepository;
    private ListOwnerParkingsUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);

        $this->useCase = new ListOwnerParkingsUseCase(
            $this->parkingRepository,
            $this->ownerRepository
        );
    }

    public function testCanListOwnerParkings(): void
    {
        $owner = EntityFactory::createOwner([
            'id' => 'owner_1'
        ]);

        $parking1 = EntityFactory::createParking([
            'id' => 'parking_1',
            'ownerId' => 'owner_1',
            'name' => 'Parking A',
            'totalSpots' => 100
        ]);

        $parking2 = EntityFactory::createParking([
            'id' => 'parking_2',
            'ownerId' => 'owner_1',
            'name' => 'Parking B',
            'totalSpots' => 50
        ]);

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with('owner_1')
            ->willReturn($owner);

        $this->parkingRepository->expects($this->once())
            ->method('findByOwnerId')
            ->with('owner_1')
            ->willReturn([$parking1, $parking2]);

        $result = $this->useCase->execute('owner_1');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('parking_1', $result[0]['id']);
        $this->assertEquals('Parking A', $result[0]['name']);
        $this->assertEquals(100, $result[0]['total_spots']);
        $this->assertEquals('parking_2', $result[1]['id']);
        $this->assertEquals('Parking B', $result[1]['name']);
    }

    public function testThrowsExceptionWhenOwnerNotFound(): void
    {
        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_owner')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('findByOwnerId');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Owner not found');

        $this->useCase->execute('nonexistent_owner');
    }
}
