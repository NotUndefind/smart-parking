<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\UpdateParkingTariffInput;
use App\Application\DTOs\Output\ParkingOutput;
use App\Application\UseCases\Owner\UpdateParkingTariffUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(UpdateParkingTariffUseCase::class)]
final class UpdateParkingTariffUseCaseTest extends TestCase
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
        $newTariffs = [
            ['start_hour' => 0, 'end_hour' => 12, 'price_per_hour' => 2.0],
            ['start_hour' => 12, 'end_hour' => 24, 'price_per_hour' => 3.5]
        ];

        $input = UpdateParkingTariffInput::create(
            parkingId: 'parking_1',
            tariffs: $newTariffs
        );

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($p) use ($newTariffs) {
                return $p->getTariffs() === $newTariffs;
            }));

        $output = $this->useCase->execute($input);

        $this->assertInstanceOf(ParkingOutput::class, $output);
        $this->assertEquals('parking_1', $output->id);
        $this->assertEquals($newTariffs, $output->tariffs);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = UpdateParkingTariffInput::create(
            parkingId: 'nonexistent_parking',
            tariffs: [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.0]
            ]
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('save');

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($input);
    }

    public function testCanUpdateToComplexTariffStructure(): void
    {
        $complexTariffs = [
            ['start_hour' => 0, 'end_hour' => 6, 'price_per_hour' => 1.0],
            ['start_hour' => 6, 'end_hour' => 12, 'price_per_hour' => 2.5],
            ['start_hour' => 12, 'end_hour' => 18, 'price_per_hour' => 3.0],
            ['start_hour' => 18, 'end_hour' => 24, 'price_per_hour' => 2.0]
        ];

        $input = UpdateParkingTariffInput::create(
            parkingId: 'parking_1',
            tariffs: $complexTariffs
        );

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.5]
            ]
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->parkingRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($p) use ($complexTariffs) {
                $tariffs = $p->getTariffs();
                return count($tariffs) === 4 &&
                       $tariffs === $complexTariffs;
            }));

        $output = $this->useCase->execute($input);

        $this->assertCount(4, $output->tariffs);
        $this->assertEquals($complexTariffs, $output->tariffs);
    }
}
