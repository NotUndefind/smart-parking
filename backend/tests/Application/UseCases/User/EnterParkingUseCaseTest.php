<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\EnterParkingInput;
use App\Application\UseCases\User\EnterParkingUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Stationnement;
use App\Domain\Entities\User;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class EnterParkingUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private EnterParkingUseCase $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->reservationRepository = $this->createMock(ReservationRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);

        $this->useCase = new EnterParkingUseCase(
            $this->userRepository,
            $this->parkingRepository,
            $this->reservationRepository,
            $this->subscriptionRepository,
            $this->stationnementRepository
        );
    }

    public function testCanEnterParkingWithReservation(): void
    {
        $currentTime = time();
        $input = new EnterParkingInput(
            userId: 'user_1',
            parkingId: 'parking_1',
            reservationId: 'reservation_1',
            subscriptionId: null
        );

        $user = new User(
            id: 'user_1',
            email: 'test@example.com',
            passwordHash: 'hash',
            firstName: 'John',
            lastName: 'Doe'
        );

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $reservation = $this->createMock(Reservation::class);
        $reservation->method('getUserId')->willReturn('user_1');
        $reservation->method('isActive')->willReturn(true);
        $reservation->method('getStartTime')->willReturn($currentTime - 600);
        $reservation->method('getEndTime')->willReturn($currentTime + 3600);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('user_1')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findActiveByUserAndParking')
            ->with('user_1', 'parking_1')
            ->willReturn(null);

        $this->reservationRepository->expects($this->once())
            ->method('findById')
            ->with('reservation_1')
            ->willReturn($reservation);

        $this->stationnementRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Stationnement::class));

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('active', $output->status);
    }
}
