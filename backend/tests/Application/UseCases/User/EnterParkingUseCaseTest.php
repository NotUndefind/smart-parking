<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\EnterParkingInput;
use App\Application\UseCases\User\EnterParkingUseCase;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\StationnementOutput;
use App\Domain\Entities\User;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Reservation;
use App\Domain\Entities\Subscription;
use App\Domain\Entities\Stationnement;

#[CoversClass(EnterParkingUseCase::class)]
final class EnterParkingUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ReservationRepositoryInterface $reservationRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private StationnementRepositoryInterface $stationnementRepository;
    private EnterParkingUseCase $useCase;

    protected function setUp(): void
    {
        // Mock all repositories (interfaces)
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

    public function testCanEnterWithReservation(): void
    {
        $currentTime = time();
        $input = EnterParkingInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            reservationId: 'reservation_1',
            subscriptionId: null
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);
        $reservation = EntityFactory::createReservation([
            'id' => 'reservation_1',
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'startTime' => $currentTime - 600, // Started 10 min ago
            'endTime' => $currentTime + 3000, // Ends in 50 min
            'status' => 'active'
        ]);

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
            ->with($this->callback(function ($stat) {
                return $stat->getUserId() === 'user_1' &&
                       $stat->getParkingId() === 'parking_1' &&
                       $stat->getStatus() === 'active';
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('active', $output->status);
        $this->assertNotNull($output->id);
        $this->assertNotNull($output->entryTime);
    }

    public function testCanEnterWithoutReservation(): void
    {
        $currentTime = time();
        $input = EnterParkingInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            reservationId: null,
            subscriptionId: 'subscription_1'
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);
        $subscription = EntityFactory::createSubscription([
            'id' => 'subscription_1',
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'startDate' => $currentTime - 86400, // Started yesterday
            'endDate' => $currentTime + 86400 * 30, // Ends in 30 days
            'isActive' => true
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findActiveByUserAndParking')
            ->willReturn(null);

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with('subscription_1')
            ->willReturn($subscription);

        $this->stationnementRepository->expects($this->once())
            ->method('save');

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('active', $output->status);
    }

    public function testThrowsExceptionWhenParkingFull(): void
    {
        $input = EnterParkingInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            reservationId: 'reservation_1',
            subscriptionId: null
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking(['id' => 'parking_1']);
        $activeStationnement = EntityFactory::createStationnement([
            'userId' => 'user_1',
            'parkingId' => 'parking_1',
            'status' => 'active'
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->willReturn($parking);

        $this->stationnementRepository->expects($this->once())
            ->method('findActiveByUserAndParking')
            ->with('user_1', 'parking_1')
            ->willReturn($activeStationnement);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User already has an active parking session');

        $this->useCase->execute($input);
    }
}
