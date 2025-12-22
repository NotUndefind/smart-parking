<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\CreateReservationInput;
use App\Application\UseCases\User\CreateReservationUseCase;
use App\Application\Validators\TimeSlotValidator;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\User;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateReservationUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private TimeSlotValidator $timeSlotValidator;
    private CreateReservationUseCase $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->timeSlotValidator = $this->createMock(TimeSlotValidator::class);

        $this->useCase = new CreateReservationUseCase(
            $this->userRepository,
            $this->parkingRepository,
            $this->reservationRepository,
            $this->timeSlotValidator
        );
    }

    public function testCanCreateReservation(): void
    {
        $this->assertTrue(true); // Placeholder test
    }
}
