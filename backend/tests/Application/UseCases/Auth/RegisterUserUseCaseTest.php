<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Auth;

use App\Application\DTOs\Input\RegisterUserInput;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\User;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Application\DTOs\Output\UserOutput;

#[CoversClass(RegisterUserUseCase::class)]
#[CoversClass(User::class)]
final class RegisterUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private RegisterUserUseCase $useCase;

    protected function setUp(): void
    {
        // Mock repository (interface)
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        // Use real instances of validators and services (lightweight, no external dependencies)
        $emailValidator = new EmailValidator();
        $passwordValidator = new PasswordValidator();
        $passwordHasher = new PasswordHasher();

        $this->useCase = new RegisterUserUseCase(
            $this->userRepository,
            $emailValidator,
            $passwordValidator,
            $passwordHasher
        );
    }

    public function testCanRegisterNewUser(): void
    {
        $input = RegisterUserInput::create(
            email: 'test@example.com',
            password: 'Password123!',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($user) {
                return $user->getEmail() === 'test@example.com' &&
                       $user->getFirstName() === 'John' &&
                       $user->getLastName() === 'Doe';
            }));

        $output = $this->useCase->execute($input);

        $this->assertEquals('test@example.com', $output->email);
        $this->assertEquals('John', $output->firstName);
        $this->assertEquals('Doe', $output->lastName);
        $this->assertEquals('John Doe', $output->fullName);
        $this->assertNotNull($output->id);
        $this->assertNotNull($output->createdAt);
    }

    public function testThrowsExceptionWhenEmailAlreadyExists(): void
    {
        $input = RegisterUserInput::create(
            email: 'existing@example.com',
            password: 'Password123!',
            firstName: 'John',
            lastName: 'Doe'
        );

        $existingUser = EntityFactory::createUser([
            'email' => 'existing@example.com'
        ]);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn($existingUser);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->useCase->execute($input);
    }

    public function testValidatesEmailAndPassword(): void
    {
        $input = RegisterUserInput::create(
            email: 'invalid-email',
            password: 'weak',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->userRepository->expects($this->never())
            ->method('findByEmail');

        $this->userRepository->expects($this->never())
            ->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->useCase->execute($input);
    }
}
