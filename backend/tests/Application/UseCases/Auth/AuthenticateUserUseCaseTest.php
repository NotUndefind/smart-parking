<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Auth;

use App\Application\DTOs\Input\AuthenticateUserInput;
use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Application\DTOs\Output\AuthTokenOutput;
use App\Application\DTOs\Output\UserOutput;
use App\Domain\Entities\User;

#[CoversClass(AuthenticateUserUseCase::class)]
#[CoversClass(User::class)]
final class AuthenticateUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private AuthenticateUserUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repository (interface)
        $this->userRepository = $this->createMock(
            UserRepositoryInterface::class,
        );

        // Use real instances of services (lightweight, no external dependencies)
        $passwordHasher = new PasswordHasher();
        $jwtService = new JWTService("test-secret-key");

        $this->useCase = new AuthenticateUserUseCase(
            $this->userRepository,
            $passwordHasher,
            $jwtService,
        );
    }

    public function testSuccessfulAuthentication(): void
    {
        $input = AuthenticateUserInput::create(
            email: "test@example.com",
            password: "Password123!",
        );

        // Create a user with a real hashed password
        $passwordHasher = new PasswordHasher();
        $hashedPassword = $passwordHasher->hash("Password123!");

        $user = EntityFactory::createUser([
            "email" => "test@example.com",
            "passwordHash" => $hashedPassword,
            "firstName" => "John",
            "lastName" => "Doe",
        ]);

        $this->userRepository
            ->expects($this->once())
            ->method("findByEmail")
            ->with("test@example.com")
            ->willReturn($user);

        $output = $this->useCase->execute($input);

        $this->assertNotEmpty($output->token);
        $this->assertEquals("Bearer", $output->type);
        $this->assertEquals(3600, $output->expiresIn);
        $this->assertEquals("test@example.com", $output->user->email);
        $this->assertEquals("John", $output->user->firstName);
        $this->assertEquals("Doe", $output->user->lastName);
        $this->assertEquals("John Doe", $output->user->fullName);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $input = AuthenticateUserInput::create(
            email: "notfound@example.com",
            password: "Password123!",
        );

        $this->userRepository
            ->expects($this->once())
            ->method("findByEmail")
            ->with("notfound@example.com")
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage("Invalid email or password");

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenPasswordInvalid(): void
    {
        $input = AuthenticateUserInput::create(
            email: "test@example.com",
            password: "WrongPassword",
        );

        // Create a user with a different password
        $passwordHasher = new PasswordHasher();
        $hashedPassword = $passwordHasher->hash("CorrectPassword");

        $user = EntityFactory::createUser([
            "email" => "test@example.com",
            "passwordHash" => $hashedPassword,
        ]);

        $this->userRepository
            ->expects($this->once())
            ->method("findByEmail")
            ->with("test@example.com")
            ->willReturn($user);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage("Invalid email or password");

        $this->useCase->execute($input);
    }
}
