<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\User;

use App\Application\UseCases\User\GenerateInvoiceUseCase;
use App\Domain\Entities\Parking;
use App\Domain\Entities\Stationnement;
use App\Domain\Entities\User;
use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class GenerateInvoiceUseCaseTest extends TestCase
{
    private StationnementRepositoryInterface $stationnementRepository;
    private ParkingRepositoryInterface $parkingRepository;
    private UserRepositoryInterface $userRepository;
    private GenerateInvoiceUseCase $useCase;

    protected function setUp(): void
    {
        $this->stationnementRepository = $this->createMock(StationnementRepositoryInterface::class);
        $this->parkingRepository = $this->createMock(ParkingRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->useCase = new GenerateInvoiceUseCase(
            $this->stationnementRepository,
            $this->parkingRepository,
            $this->userRepository
        );
    }

    public function testCanGenerateInvoice(): void
    {
        $stationnement = $this->createMock(Stationnement::class);
        $stationnement->method('getId')->willReturn('stationnement_1');
        $stationnement->method('getStatus')->willReturn('completed');
        $stationnement->method('getParkingId')->willReturn('parking_1');
        $stationnement->method('getUserId')->willReturn('user_1');
        $stationnement->method('getEntryTime')->willReturn(time() - 7200);
        $stationnement->method('getExitTime')->willReturn(time());
        $stationnement->method('getFinalPrice')->willReturn(20.0);
        $stationnement->method('getPenaltyAmount')->willReturn(5.0);

        $parking = $this->createMock(Parking::class);
        $parking->method('getName')->willReturn('Test Parking');

        $user = new User(
            id: 'user_1',
            email: 'test@example.com',
            passwordHash: 'hash',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->stationnementRepository->expects($this->once())
            ->method('findById')
            ->with('stationnement_1')
            ->willReturn($stationnement);

        $this->parkingRepository->expects($this->once())
            ->method('findById')
            ->with('parking_1')
            ->willReturn($parking);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('user_1')
            ->willReturn($user);

        $output = $this->useCase->execute('stationnement_1');

        $this->assertEquals('stationnement_1', $output->stationnementId);
        $this->assertEquals('Test Parking', $output->parkingName);
        $this->assertEquals('John Doe', $output->userName);
        $this->assertEquals(20.0, $output->finalPrice);
        $this->assertEquals(5.0, $output->penaltyAmount);
        $this->assertEquals(25.0, $output->totalAmount);
    }
}
