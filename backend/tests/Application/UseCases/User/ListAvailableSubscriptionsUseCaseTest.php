<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListAvailableSubscriptionsUseCase;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\SubscriptionTypeOutput;
use App\Domain\Entities\Parking;

#[CoversClass(ListAvailableSubscriptionsUseCase::class)]
final class ListAvailableSubscriptionsUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private ListAvailableSubscriptionsUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListAvailableSubscriptionsUseCase(
            $this->parkingRepository
        );
    }

    public function testCanListAvailableSubscriptions(): void
    {
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Central Parking'
        ]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $result = $this->useCase->execute('parking_1');

        $this->assertIsArray($result);
        $this->assertCount(4, $result);

        $this->assertInstanceOf(SubscriptionTypeOutput::class, $result[0]);
        $this->assertEquals('daily', $result[0]->type);
        $this->assertEquals(10.0, $result[0]->price);
        $this->assertEquals(1, $result[0]->durationDays);

        $this->assertEquals('weekly', $result[1]->type);
        $this->assertEquals(50.0, $result[1]->price);
        $this->assertEquals(7, $result[1]->durationDays);

        $this->assertEquals('monthly', $result[2]->type);
        $this->assertEquals(150.0, $result[2]->price);
        $this->assertEquals(30, $result[2]->durationDays);

        $this->assertEquals('yearly', $result[3]->type);
        $this->assertEquals(1500.0, $result[3]->price);
        $this->assertEquals(365, $result[3]->durationDays);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute('nonexistent_parking');
    }
}
