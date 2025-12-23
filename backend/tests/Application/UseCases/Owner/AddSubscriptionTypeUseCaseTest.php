<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\AddSubscriptionTypeInput;
use App\Application\DTOs\Output\SubscriptionTypeOutput;
use App\Application\UseCases\Owner\AddSubscriptionTypeUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;

#[CoversClass(AddSubscriptionTypeUseCase::class)]
final class AddSubscriptionTypeUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private AddSubscriptionTypeUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->useCase = new AddSubscriptionTypeUseCase($this->parkingRepository);
    }

    public function testCanAddMonthlySubscriptionType(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: 'parking_1',
            type: 'monthly',
            price: 100.0,
            durationDays: 30
        );

        $parking = EntityFactory::createParking(['id' => 'parking_1']);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute($input);

        $this->assertInstanceOf(SubscriptionTypeOutput::class, $output);
        $this->assertEquals('monthly', $output->type);
        $this->assertEquals(100.0, $output->price);
        $this->assertEquals(30, $output->durationDays);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: 'nonexistent_parking',
            type: 'monthly',
            price: 100.0,
            durationDays: 30
        );

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionForInvalidSubscriptionType(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: 'parking_1',
            type: 'invalid_type',
            price: 100.0,
            durationDays: 30
        );

        $parking = EntityFactory::createParking(['id' => 'parking_1']);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid subscription type. Must be one of: daily, weekly, monthly, yearly');

        $this->useCase->execute($input);
    }
}
