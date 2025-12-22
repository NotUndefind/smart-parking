<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListAvailableSubscriptionsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Repositories\ParkingRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListAvailableSubscriptionsUseCaseTest extends TestCase
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
        $parking = $this->createMock(Parking::class);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('parking_1');

        $this->assertIsArray($output);
        $this->assertCount(4, $output);
        $this->assertEquals('daily', $output[0]->type);
        $this->assertEquals(10.0, $output[0]->price);
        $this->assertEquals(1, $output[0]->durationDays);
    }
}
