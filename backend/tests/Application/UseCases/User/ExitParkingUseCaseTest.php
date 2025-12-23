<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\ExitParkingInput;
use App\Application\UseCases\User\ExitParkingUseCase;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Stationnement;

#[CoversClass(ExitParkingUseCase::class)]
final class ExitParkingUseCaseTest extends TestCase
{
    private StationnementRepositoryInterface $stationnementRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private ExitParkingUseCase $useCase;

    protected function setUp(): void
    {
        // Mock all repositories (interfaces)
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        $this->useCase = new ExitParkingUseCase(
            $this->stationnementRepository,
            $this->parkingRepository,
            $this->reservationRepository
        );
    }

    public function testSuccessfulExitWithCorrectPrice(): void
    {
        $entryTime = time() - 7200; // 2 hours ago
        $input = ExitParkingInput::create(
            stationnementId: 'stationnement_1'
        );

        $stationnement = EntityFactory::createStationnement([
            'id' => 'stationnement_1',
            'parkingId' => 'parking_1',
            'entryTime' => $entryTime,
            'exitTime' => null,
            'reservationId' => null,
            'status' => 'active'
        ]);

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking',
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.5]
            ]
        ]);

        $this->stationnementRepository->expects($this->once())
            ->method('findById')
            ->with('stationnement_1')
            ->willReturn($stationnement);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($stat) {
                return $stat->getStatus() === 'completed' &&
                       $stat->getExitTime() !== null &&
                       $stat->getFinalPrice() > 0;
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('stationnement_1', $output->id);
        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('completed', $output->status);
        $this->assertEquals($entryTime, $output->entryTime);
        $this->assertNotNull($output->exitTime);
        $this->assertGreaterThan(0, $output->finalPrice);
        $this->assertEquals(0.0, $output->penaltyAmount);
    }

    public function testExitWithPenaltyForOverstay(): void
    {
        $entryTime = time() - 7200; // 2 hours ago
        $reservationEndTime = time() - 3600; // Reservation ended 1 hour ago (overstay!)

        $input = ExitParkingInput::create(
            stationnementId: 'stationnement_1'
        );

        $stationnement = EntityFactory::createStationnement([
            'id' => 'stationnement_1',
            'parkingId' => 'parking_1',
            'reservationId' => 'reservation_1',
            'entryTime' => $entryTime,
            'exitTime' => null,
            'status' => 'active'
        ]);

        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking',
            'tariffs' => [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.5]
            ]
        ]);

        $reservation = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'startTime' => $entryTime,
            'endTime' => $reservationEndTime
        ]);

        $this->stationnementRepository->expects($this->once())
            ->method('findById')
            ->willReturn($stationnement);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation);

        $this->stationnementRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($stat) {
                return $stat->getStatus() === 'completed' &&
                       $stat->getPenaltyAmount() > 0;
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('completed', $output->status);
        $this->assertGreaterThan(0, $output->penaltyAmount); // Should have penalty for overstay
    }

    public function testThrowsExceptionWhenStationnementNotFound(): void
    {
        $input = ExitParkingInput::create(
            stationnementId: 'nonexistent_stationnement'
        );

        $this->stationnementRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_stationnement')
            ->willReturn(null);

        $this->stationnementRepository->expects($this->never())
            ->method('save');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Active stationnement not found');

        $this->useCase->execute($input);
    }
}
