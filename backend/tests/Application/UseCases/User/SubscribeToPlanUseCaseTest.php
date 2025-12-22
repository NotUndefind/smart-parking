<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\SubscribeToPlanInput;
use App\Application\UseCases\User\SubscribeToPlanUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Subscription;
use App\Domain\Entities\User;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class SubscribeToPlanUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscribeToPlanUseCase $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);

        $this->useCase = new SubscribeToPlanUseCase(
            $this->userRepository,
            $this->parkingRepository,
            $this->subscriptionRepository
        );
    }

    public function testCanSubscribeToPlan(): void
    {
        $input = new SubscribeToPlanInput(
            userId: 'user_1',
            parkingId: 'parking_1',
            type: 'monthly',
            price: 150.0
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

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('user_1')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Subscription::class));

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('monthly', $output->type);
        $this->assertEquals(150.0, $output->price);
        $this->assertTrue($output->isActive);
    }
}
