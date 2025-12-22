<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Auth;

use App\Application\DTOs\Input\AuthenticateUserInput;
use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Domain\Entities\User;
use App\Domain\Exceptions\User\InvalidCredentialsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;

class AuthenticateUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private PasswordHasher $passwordHasher;
    private JWTService $jwtService;
    private AuthenticateUserUseCaseTest $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);
        $this->jwtService = $this->createMock(JWTService::class);

        $this->useCase = new AuthenticateUserUseCase(
            $this->userRepository,
            $this->passwordHasher,
            $this->jwtService
        );
    }

    public function testCanAuthenticateUser(): void
    {
        $input = new AuthenticateUserInput(
            email: 'test@example.com',
            password: 'Password123!'
        );

        $user = new User(
            id: 'user_1',
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->with($input->password, 'hashed_password')
            ->willReturn(true);

        $this->jwtService->expects($this->once())
            ->method('generateToken')
            ->with('user_1', 'test@example.com', 'user')
            ->willReturn('jwt_token_here');

        $output = $this->useCase->execute($input);

        $this->assertEquals('jwt_token_here', $output->token);
        $this->assertEquals('Bearer', $output->type);
        $this->assertEquals(3600, $output->expiresIn);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $input = new AuthenticateUserInput(
            email: 'notfound@example.com',
            password: 'Password123!'
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenPasswordInvalid(): void
    {
        $input = new AuthenticateUserInput(
            email: 'test@example.com',
            password: 'WrongPassword'
        );

        $user = new User(
            id: 'user_1',
            email: 'test@example.com',
            passwordHash: 'hashed_password',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($user);

        $this->passwordHasher->expects($this->once())
            ->method('verify')
            ->with($input->password, 'hashed_password')
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        $this->useCase->execute($input);
    }
}
