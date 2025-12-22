<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\ListUserSubscriptionsUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Subscription;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListUserSubscriptionsUseCaseTest extends TestCase
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private ListUserSubscriptionsUseCase $useCase;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);

        $this->useCase = new ListUserSubscriptionsUseCase(
            $this->subscriptionRepository,
            $this->parkingRepository
        );
    }

    public function testCanListUserSubscriptions(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getId')->willReturn('subscription_1');
        $subscription->method('getParkingId')->willReturn('parking_1');
        $subscription->method('getType')->willReturn('monthly');
        $subscription->method('getPrice')->willReturn(150.0);
        $subscription->method('getStartDate')->willReturn(time());
        $subscription->method('getEndDate')->willReturn(time() + 2592000);
        $subscription->method('isActive')->willReturn(true);

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $this->subscriptionRepository->expects($this->once())
            ->method('findByUserId')
            ->with('user_1')
            ->willReturn([$subscription]);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $output = $this->useCase->execute('user_1');

        $this->assertIsArray($output);
        $this->assertCount(1, $output);
        $this->assertEquals('subscription_1', $output[0]->id);
        $this->assertEquals('Test Parking', $output[0]->parkingName);
        $this->assertEquals('monthly', $output[0]->type);
    }
}
