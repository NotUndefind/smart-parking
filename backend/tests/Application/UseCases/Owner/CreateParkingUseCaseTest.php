<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\CreateParkingInput;
use App\Application\UseCases\Owner\CreateParkingUseCase;
use App\Domain\Entities\Owner;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\OwnerNotFoundException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateParkingUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private CreateParkingUseCase $useCase;

    protected function setUp(): void
    {
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new CreateParkingUseCase(
            $this->ownerRepository,
            $this->parkingRepository
        );
    }

    public function testCanCreateParking(): void
    {
        $input = new CreateParkingInput(
            ownerId: 'owner_1',
            name: 'Test Parking',
            address: '123 Test St',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 100,
            tariffs: ['hourly' => 2.5],
            schedule: ['monday' => '08:00-20:00']
        );

        $owner = new Owner(
            id: 'owner_1',
            email: 'owner@example.com',
            passwordHash: 'hash',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with($input->ownerId)
            ->willReturn($owner);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Parking::class));

        $output = $this->useCase->execute($input);

        $this->assertEquals('Test Parking', $output->name);
        $this->assertEquals('123 Test St', $output->address);
        $this->assertEquals(48.8566, $output->latitude);
        $this->assertEquals(2.3522, $output->longitude);
        $this->assertEquals(100, $output->totalSpots);
    }

    public function testThrowsExceptionWhenOwnerNotFound(): void
    {
        $input = new CreateParkingInput(
            ownerId: 'nonexistent',
            name: 'Test Parking',
            address: '123 Test St',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 100,
            tariffs: ['hourly' => 2.5],
            schedule: ['monday' => '08:00-20:00']
        );

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with($input->ownerId)
            ->willReturn(null);

        $this->expectException(OwnerNotFoundException::class);

        $this->useCase->execute($input);
    }
}
