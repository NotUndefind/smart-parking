<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\UpdateParkingTariffInput;
use App\Application\UseCases\Owner\UpdateParkingTariffUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

class UpdateParkingTariffUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private UpdateParkingTariffUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->useCase = new UpdateParkingTariffUseCase($this->parkingRepository);
    }

    public function testCanUpdateParkingTariff(): void
    {
        $input = new UpdateParkingTariffInput(
            parkingId: 'parking_1',
            tariffs: ['hourly' => 5.0, 'daily' => 30.0]
        );

        $parking = $this->createMock(Parking::class);
        $parking->method('getId')->willReturn('parking_1');
        $parking->method('getName')->willReturn('Test Parking');
        $parking->method('getAddress')->willReturn('123 Test St');
        $parking->method('getLatitude')->willReturn(48.8566);
        $parking->method('getLongitude')->willReturn(2.3522);
        $parking->method('getTotalSpots')->willReturn(100);
        $parking->method('getTariffs')->willReturn(['hourly' => 5.0, 'daily' => 30.0]);
        $parking->method('getSchedule')->willReturn(['monday' => '00:00-23:59']);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $parking->expects($this->once())
            ->method('updateTariffs')
            ->with(['hourly' => 5.0, 'daily' => 30.0]);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($parking);

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->id);
        $this->assertEquals(['hourly' => 5.0, 'daily' => 30.0], $output->tariffs);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = new UpdateParkingTariffInput(
            parkingId: 'nonexistent',
            tariffs: ['hourly' => 5.0]
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute($input);
    }
}
