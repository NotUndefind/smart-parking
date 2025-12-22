<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Auth;

use App\Application\DTOs\Input\RegisterUserInput;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;

class RegisterUserUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private EmailValidator $emailValidator;
    private PasswordValidator $passwordValidator;
    private PasswordHasher $passwordHasher;
    private RegisterUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->emailValidator = $this->createMock(EmailValidator::class);
        $this->passwordValidator = $this->createMock(PasswordValidator::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);

        $this->useCase = new RegisterUserUseCase(
            $this->userRepository,
            $this->emailValidator,
            $this->passwordValidator,
            $this->passwordHasher
        );
    }

    public function testCanRegisterUser(): void
    {
        $input = new RegisterUserInput(
            email: 'test@example.com',
            password: 'Password123!',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->emailValidator->expects($this->once())
            ->method('validate')
            ->with($input->email);

        $this->passwordValidator->expects($this->once())
            ->method('validate')
            ->with($input->password);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hash')
            ->with($input->password)
            ->willReturn('hashed_password');

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $output = $this->useCase->execute($input);

        $this->assertEquals('test@example.com', $output->email);
        $this->assertEquals('John', $output->firstName);
        $this->assertEquals('Doe', $output->lastName);
        $this->assertEquals('John Doe', $output->fullName);
    }

    public function testThrowsExceptionWhenEmailAlreadyExists(): void
    {
        $input = new RegisterUserInput(
            email: 'existing@example.com',
            password: 'Password123!',
            firstName: 'John',
            lastName: 'Doe'
        );

        $existingUser = new User(
            id: 'user_1',
            email: 'existing@example.com',
            passwordHash: 'hash',
            firstName: 'Jane',
            lastName: 'Smith'
        );

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($existingUser);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->useCase->execute($input);
    }
}
