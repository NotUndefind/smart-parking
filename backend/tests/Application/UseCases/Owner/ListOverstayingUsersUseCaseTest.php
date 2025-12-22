<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\ListOverstayingUsersUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Exceptions\Parking\ParkingNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListOverstayingUsersUseCaseTest extends TestCase
{
    private ParkingRepositoryInterface $parkingRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ListOverstayingUsersUseCase $useCase;

    protected function setUp(): void
    {
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        $this->useCase = new ListOverstayingUsersUseCase(
            $this->parkingRepository,
            $this->stationnementRepository,
            $this->reservationRepository
        );
    }

    public function testCanListOverstayingUsers(): void
    {
        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findByParkingId')
            ->with('parking_1')
            ->willReturn([]);

        $output = $this->useCase->execute('parking_1');

        $this->assertIsArray($output);
        $this->assertCount(0, $output);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent')
            ->willReturn(null);

        $this->expectException(ParkingNotFoundException::class);

        $this->useCase->execute('nonexistent');
    }
}
