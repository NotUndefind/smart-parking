<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\CreateReservationInput;
use App\Application\UseCases\User\CreateReservationUseCase;
use App\Application\Validators\TimeSlotValidator;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\ReservationOutput;
use App\Domain\Entities\User;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;

#[CoversClass(CreateReservationUseCase::class)]
final class CreateReservationUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private CreateReservationUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repositories (interfaces)
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);

        // Use real validator (lightweight, no external dependencies)
        $timeSlotValidator = new TimeSlotValidator();

        $this->useCase = new CreateReservationUseCase(
            $this->userRepository,
            $this->parkingRepository,
            $this->reservationRepository,
            $timeSlotValidator
        );
    }

    public function testCanCreateReservation(): void
    {
        $startTime = time() + 3600; // 1 hour from now
        $endTime = $startTime + 7200; // 2 hours duration

        $input = CreateReservationInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            startTime: $startTime,
            endTime: $endTime
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking',
            'totalSpots' => 100,
            'isActive' => true,
            'schedule' => [
                'monday' => ['open' => '00:00', 'close' => '23:59'],
                'tuesday' => ['open' => '00:00', 'close' => '23:59'],
                'wednesday' => ['open' => '00:00', 'close' => '23:59'],
                'thursday' => ['open' => '00:00', 'close' => '23:59'],
                'friday' => ['open' => '00:00', 'close' => '23:59'],
                'saturday' => ['open' => '00:00', 'close' => '23:59'],
                'sunday' => ['open' => '00:00', 'close' => '23:59']
            ]
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('user_1')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findActiveByParking')
            ->willReturn([]); // No existing reservations

        $this->reservationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($reservation) use ($startTime, $endTime) {
                return $reservation->getUserId() === 'user_1' &&
                       $reservation->getParkingId() === 'parking_1' &&
                       $reservation->getStartTime() === $startTime &&
                       $reservation->getEndTime() === $endTime &&
                       $reservation->getStatus() === 'active';
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals($startTime, $output->startTime);
        $this->assertEquals($endTime, $output->endTime);
        $this->assertEquals('active', $output->status);
        $this->assertNotNull($output->id);
        $this->assertGreaterThan(0, $output->estimatedPrice);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $input = CreateReservationInput::create(
            userId: 'nonexistent_user',
            parkingId: 'parking_1',
            startTime: time() + 3600,
            endTime: time() + 7200
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_user')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('findById');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $input = CreateReservationInput::create(
            userId: 'user_1',
            parkingId: 'nonexistent_parking',
            startTime: time() + 3600,
            endTime: time() + 7200
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenParkingClosed(): void
    {
        // Create a timestamp for a time when parking is closed
        $closedTime = strtotime('next Monday 22:00'); // Assuming parking closes at 20:00

        $input = CreateReservationInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            startTime: $closedTime,
            endTime: $closedTime + 3600
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'schedule' => [
                'monday' => ['open' => '08:00', 'close' => '20:00']
            ],
            'isActive' => true
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parking is closed during the requested time slot');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenParkingFull(): void
    {
        $startTime = time() + 3600;
        $endTime = $startTime + 3600;

        $input = CreateReservationInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            startTime: $startTime,
            endTime: $endTime
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'totalSpots' => 2, // Only 2 spots
            'isActive' => true,
            'schedule' => [
                'monday' => ['open' => '00:00', 'close' => '23:59'],
                'tuesday' => ['open' => '00:00', 'close' => '23:59'],
                'wednesday' => ['open' => '00:00', 'close' => '23:59'],
                'thursday' => ['open' => '00:00', 'close' => '23:59'],
                'friday' => ['open' => '00:00', 'close' => '23:59'],
                'saturday' => ['open' => '00:00', 'close' => '23:59'],
                'sunday' => ['open' => '00:00', 'close' => '23:59']
            ]
        ]);

        // Create 2 existing reservations (parking is full)
        $existingReservations = [
            EntityFactory::createReservation(['parkingId' => 'parking_1']),
            EntityFactory::createReservation(['parkingId' => 'parking_1'])
        ];

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->reservationRepository->expects($this->once())
            ->method('findActiveByParking')
            ->willReturn($existingReservations);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Parking is full during the requested time slot');

        $this->useCase->execute($input);
    }
}
