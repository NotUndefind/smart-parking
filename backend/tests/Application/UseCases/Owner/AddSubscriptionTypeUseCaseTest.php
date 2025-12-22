<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\AddSubscriptionTypeInput;
use App\Application\UseCases\Owner\AddSubscriptionTypeUseCase;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddSubscriptionTypeInput::class)]
class AddSubscriptionTypeUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private AddSubscriptionTypeUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(
            ParkingRepositoryInterface::class,
        );
        $this->useCase = new AddSubscriptionTypeUseCase(
            $this->parkingRepository,
        );
    }

    public function testCanAddSubscriptionType(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: "parking_1",
            type: "monthly",
            price: 150.0,
            durationDays: 30,
        );

        // Mock: parking existe
        $parking = $this->createMock(\App\Domain\Entities\Parking::class);

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("parking_1")
            ->willReturn($parking);

        $output = $this->useCase->execute($input);

        $this->assertEquals("monthly", $output->type);
        $this->assertEquals(150.0, $output->price);
        $this->assertEquals(30, $output->durationDays);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: "nonexistent",
            type: "monthly",
            price: 150.0,
            durationDays: 30,
        );

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("nonexistent")
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionForInvalidType(): void
    {
        $input = AddSubscriptionTypeInput::create(
            parkingId: "parking_1",
            type: "invalid",
            price: 150.0,
            durationDays: 30,
        );

        // Mock: parking existe
        $parking = $this->createMock(\App\Domain\Entities\Parking::class);

        $this->parkingRepository
            ->expects($this->once())
            ->method("findById")
            ->with("parking_1")
            ->willReturn($parking);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid subscription type");

        $this->useCase->execute($input);
    }
}
