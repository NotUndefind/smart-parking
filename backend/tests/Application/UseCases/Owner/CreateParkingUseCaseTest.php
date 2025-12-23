<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\CreateParkingInput;
use App\Application\DTOs\Output\ParkingOutput;
use App\Application\UseCases\Owner\CreateParkingUseCase;
use App\Domain\Entities\Owner;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\OwnerNotFoundException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(CreateParkingUseCase::class)]
final class CreateParkingUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private CreateParkingUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repositories (interfaces)
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new CreateParkingUseCase(
            $this->ownerRepository,
            $this->parkingRepository
        );
    }

    public function testCanCreateParking(): void
    {
        $input = CreateParkingInput::create(
            ownerId: 'owner_1',
            name: 'Parking Central',
            address: '123 Main St',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 100,
            tariffs: [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.5]
            ],
            schedule: [
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00']
            ]
        );

        $owner = EntityFactory::createOwner(['id' => 'owner_1']);

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with('owner_1')
            ->willReturn($owner);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($parking) {
                return $parking->getOwnerId() === 'owner_1' &&
                       $parking->getName() === 'Parking Central' &&
                       $parking->getTotalSpots() === 100 &&
                       $parking->isActive();
            }));

        $output = $this->useCase->execute($input);

        $this->assertInstanceOf(ParkingOutput::class, $output);
        $this->assertEquals('Parking Central', $output->name);
        $this->assertEquals('123 Main St', $output->address);
        $this->assertEquals(48.8566, $output->latitude);
        $this->assertEquals(2.3522, $output->longitude);
        $this->assertEquals(100, $output->totalSpots);
        $this->assertNotEmpty($output->id);
    }

    public function testThrowsExceptionWhenOwnerNotFound(): void
    {
        $input = CreateParkingInput::create(
            ownerId: 'nonexistent_owner',
            name: 'Parking Central',
            address: '123 Main St',
            latitude: 48.8566,
            longitude: 2.3522,
            totalSpots: 100
        );

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_owner')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('save');

        $this->expectException(OwnerNotFoundException::class);
        $this->expectExceptionMessage('Owner not found');

        $this->useCase->execute($input);
    }

    public function testCanCreateParkingWithCustomSchedule(): void
    {
        $customSchedule = [
            'monday' => ['open' => '09:00', 'close' => '18:00'],
            'tuesday' => ['open' => '09:00', 'close' => '18:00'],
            'wednesday' => ['open' => '09:00', 'close' => '18:00'],
            'thursday' => ['open' => '09:00', 'close' => '18:00'],
            'friday' => ['open' => '09:00', 'close' => '18:00'],
            'saturday' => ['open' => '10:00', 'close' => '16:00'],
            'sunday' => ['open' => '10:00', 'close' => '16:00']
        ];

        $input = CreateParkingInput::create(
            ownerId: 'owner_1',
            name: 'Weekend Parking',
            address: '456 Side St',
            latitude: 48.8500,
            longitude: 2.3400,
            totalSpots: 50,
            tariffs: [
                ['start_hour' => 0, 'end_hour' => 12, 'price_per_hour' => 2.0],
                ['start_hour' => 12, 'end_hour' => 24, 'price_per_hour' => 3.0]
            ],
            schedule: $customSchedule
        );

        $owner = EntityFactory::createOwner(['id' => 'owner_1']);

        $this->ownerRepository->expects($this->once())
            ->method('findById')
            ->willReturn($owner);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($parking) use ($customSchedule) {
                return $parking->getSchedule() === $customSchedule &&
                       count($parking->getTariffs()) === 2;
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('Weekend Parking', $output->name);
        $this->assertEquals($customSchedule, $output->schedule);
        $this->assertCount(2, $output->tariffs);
    }
}
