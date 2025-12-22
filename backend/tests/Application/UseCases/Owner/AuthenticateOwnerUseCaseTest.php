<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\AuthenticateOwnerInput;
use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
use App\Domain\Entities\Owner;
use App\Domain\Exceptions\User\InvalidCredentialsException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;

class AuthenticateOwnerUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private PasswordHasher $passwordHasher;
    private JWTService $jwtService;
    private AuthenticateOwnerUseCase $useCase;

    protected function setUp(): void
    {
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);
        $this->jwtService = $this->createMock(JWTService::class);

        $this->useCase = new AuthenticateOwnerUseCase(
            $this->ownerRepository,
            $this->passwordHasher,
            $this->jwtService
        );
    }

    public function testCanAuthenticateOwner(): void
    {
        $input = new AuthenticateOwnerInput(
            email: 'owner@example.com',
            password: 'Password123!'
        );

        $owner = new Owner(
            id: 'owner_1',
            email: 'owner@example.com',
            passwordHash: 'hashed_password',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($owner);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->with($input->password, 'hashed_password')
            ->willReturn(true);

        $this->jwtService->expects($this->once())
            ->method('generateToken')
            ->with('owner_1', 'owner@example.com', 'owner')
            ->willReturn('jwt_token_here');

        $output = $this->useCase->execute($input);

        $this->assertEquals('jwt_token_here', $output->token);
        $this->assertEquals('Bearer', $output->type);
        $this->assertEquals(3600, $output->expiresIn);
    }

    public function testThrowsExceptionWhenOwnerNotFound(): void
    {
        $input = new AuthenticateOwnerInput(
            email: 'notfound@example.com',
            password: 'Password123!'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenPasswordInvalid(): void
    {
        $input = new AuthenticateOwnerInput(
            email: 'owner@example.com',
            password: 'WrongPassword'
        );

        $owner = new Owner(
            id: 'owner_1',
            email: 'owner@example.com',
            passwordHash: 'hashed_password',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($owner);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->with($input->password, 'hashed_password')
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute($input);
    }
}
