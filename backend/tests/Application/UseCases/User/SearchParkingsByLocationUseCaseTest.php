<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\SearchParkingsByLocationInput;
use App\Application\UseCases\User\SearchParkingsByLocationUseCase;
use App\Application\Validators\GPSCoordinatesValidator;
use App\Domain\Entities\Parking;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

class SearchParkingsByLocationUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private GPSCoordinatesValidator $gpsCoordinatesValidator;
    private SearchParkingsByLocationUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->gpsCoordinatesValidator = $this->createMock(GPSCoordinatesValidator::class);

        $this->useCase = new SearchParkingsByLocationUseCase(
            $this->parkingRepository,
            $this->gpsCoordinatesValidator
        );
    }

    public function testCanSearchParkingsByLocation(): void
    {
        $input = new SearchParkingsByLocationInput(
            latitude: 48.8566,
            longitude: 2.3522,
            radiusKm: 5.0
        );

        $parking = $this->createMock(Parking::class);
        $parking->method('getId')->willReturn('parking_1');
        $parking->method('getName')->willReturn('Test Parking');
        $parking->method('getAddress')->willReturn('123 Test Street');
        $parking->method('getLatitude')->willReturn(48.8566);
        $parking->method('getLongitude')->willReturn(2.3522);
        $parking->method('getTotalSpots')->willReturn(100);
        $parking->method('getTariffs')->willReturn(['hourly' => 5.0]);
        $parking->method('getSchedule')->willReturn(['monday' => '00:00-23:59']);
        $parking->method('calculateDistance')->willReturn(1.5);

        $this->gpsCoordinatesValidator->expects($this->once())
            ->method('validate')
            ->with(48.8566, 2.3522);

        $this->parkingRepository->expects($this->once())
            ->method('findByLocation')
            ->with(48.8566, 2.3522, 5.0)
            ->willReturn([$parking]);

        $output = $this->useCase->execute($input);

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('parking_1', $output[0]->id);
        $this->assertEquals('Test Parking', $output[0]->name);
        $this->assertEquals(1.5, $output[0]->distanceKm);
    }
}
