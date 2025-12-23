<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\DTOs\Input\SubscribeToPlanInput;
use App\Application\UseCases\User\SubscribeToPlanUseCase;
use App\Domain\Exceptions\ParkingNotFoundException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\SubscriptionOutput;
use App\Domain\Entities\User;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Subscription;

#[CoversClass(SubscribeToPlanUseCase::class)]
final class SubscribeToPlanUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscribeToPlanUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repositories (interfaces)
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
        $currentTime = time();
        $input = SubscribeToPlanInput::create(
            userId: 'user_1',
            parkingId: 'parking_1',
            type: 'monthly',
            startDate: $currentTime,
            price: 150.0
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);
        $parking = EntityFactory::createParking([
            'id' => 'parking_1',
            'name' => 'Test Parking'
        ]);

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
            ->with($this->callback(function ($subscription) {
                return $subscription->getUserId() === 'user_1' &&
                       $subscription->getParkingId() === 'parking_1' &&
                       $subscription->getType() === 'monthly' &&
                       $subscription->isActive();
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('parking_1', $output->parkingId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('monthly', $output->type);
        $this->assertTrue($output->isActive);
        $this->assertNotNull($output->id);
        $this->assertNotNull($output->startDate);
        $this->assertNotNull($output->endDate);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $currentTime = time();
        $input = SubscribeToPlanInput::create(
            userId: 'nonexistent_user',
            parkingId: 'parking_1',
            type: 'monthly',
            startDate: $currentTime,
            price: 150.0
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_user')
            ->willReturn(null);

        $this->parkingRepository->expects($this->never())
            ->method('findById');

        $this->subscriptionRepository->expects($this->never())
            ->method('save');

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenParkingNotFound(): void
    {
        $currentTime = time();
        $input = SubscribeToPlanInput::create(
            userId: 'user_1',
            parkingId: 'nonexistent_parking',
            type: 'monthly',
            startDate: $currentTime,
            price: 150.0
        );

        $user = EntityFactory::createUser(['id' => 'user_1']);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('user_1')
            ->willReturn($user);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('nonexistent_parking')
            ->willReturn(null);

        $this->subscriptionRepository->expects($this->never())
            ->method('save');

        $this->expectException(ParkingNotFoundException::class);
        $this->expectExceptionMessage('Parking not found');

        $this->useCase->execute($input);
    }
}
