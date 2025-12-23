<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\UpdateParkingScheduleUseCase;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Parking;

#[CoversClass(UpdateParkingScheduleUseCase::class)]
#[CoversClass(Parking::class)]
final class UpdateParkingScheduleUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private UpdateParkingScheduleUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new UpdateParkingScheduleUseCase(
            $this->parkingRepository
        );
    }

    public function testCanUpdateParkingSchedule(): void
    {
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);

        $newSchedule = [
            'monday' => ['open' => '09:00', 'close' => '21:00'],
            'tuesday' => ['open' => '09:00', 'close' => '21:00']
        ];

        $input = \App\Application\DTOs\Input\UpdateParkingScheduleInput::create(
            parkingId: 'parking_1',
            schedule: $newSchedule
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($p) use ($newSchedule) {
                return $p instanceof Parking &&
                       $p->getSchedule() === $newSchedule;
            }));

        $result = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $result->id);
        $this->assertEquals('Test Parking', $result->name);
        $this->assertEquals($newSchedule, $result->schedule);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $newSchedule = [
            'monday' => ['open' => '09:00', 'close' => '21:00']
        ];

        $input = \App\Application\DTOs\Input\UpdateParkingScheduleInput::create(
            parkingId: 'nonexistent_parking',
            schedule: $newSchedule
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('save');

        $this->expectException(\App\Domain\Exceptions\ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($input);
    }
}
