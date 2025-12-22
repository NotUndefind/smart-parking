<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\UpdateParkingScheduleInput;
use App\Application\UseCases\Owner\UpdateParkingScheduleUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

class UpdateParkingScheduleUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private UpdateParkingScheduleUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->useCase = new UpdateParkingScheduleUseCase($this->parkingRepository);
    }

    public function testCanUpdateParkingSchedule(): void
    {
        $input = new UpdateParkingScheduleInput(
            parkingId: 'parking_1',
            schedule: ['monday' => '09:00-18:00', 'tuesday' => '09:00-18:00']
        );

        $parking = $this->createMock(Parking::class);
        $parking->method('getId')->willReturn('parking_1');
        $parking->method('getName')->willReturn('Test Parking');
        $parking->method('getAddress')->willReturn('123 Test St');
        $parking->method('getLatitude')->willReturn(48.8566);
        $parking->method('getLongitude')->willReturn(2.3522);
        $parking->method('getTotalSpots')->willReturn(100);
        $parking->method('getTariffs')->willReturn(['hourly' => 5.0]);
        $parking->method('getSchedule')->willReturn(['monday' => '09:00-18:00', 'tuesday' => '09:00-18:00']);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $parking->expects($this->once())
            ->method('updateSchedule')
            ->with(['monday' => '09:00-18:00', 'tuesday' => '09:00-18:00']);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($parking);

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->id);
        $this->assertEquals(['monday' => '09:00-18:00', 'tuesday' => '09:00-18:00'], $output->schedule);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = new UpdateParkingScheduleInput(
            parkingId: 'nonexistent',
            schedule: ['monday' => '09:00-18:00']
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute($input);
    }
}
