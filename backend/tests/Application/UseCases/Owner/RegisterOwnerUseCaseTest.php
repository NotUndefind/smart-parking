<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\DTOs\Input\RegisterOwnerInput;
use App\Application\UseCases\Owner\RegisterOwnerUseCase;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Entities\Owner;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;

class RegisterOwnerUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private EmailValidator $emailValidator;
    private PasswordValidator $passwordValidator;
    private PasswordHasher $passwordHasher;
    private RegisterOwnerUseCase $useCase;

    protected function setUp(): void
    {
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->emailValidator = $this->createMock(EmailValidator::class);
        $this->passwordValidator = $this->createMock(PasswordValidator::class);
        $this->passwordHasher = $this->createMock(PasswordHasher::class);

        $this->useCase = new RegisterOwnerUseCase(
            $this->ownerRepository,
            $this->emailValidator,
            $this->passwordValidator,
            $this->passwordHasher
        );
    }

    public function testCanRegisterOwner(): void
    {
        $input = new RegisterOwnerInput(
            email: 'owner@example.com',
            password: 'Password123!',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->emailValidator->expects($this->once())
            ->method('validate')
            ->with($input->email);

        $this->passwordValidator->expects($this->once())
            ->method('validate')
            ->with($input->password);

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hash')
            ->with($input->password)
            ->willReturn('hashed_password');

        $this->ownerRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Owner::class));

        $output = $this->useCase->execute($input);

        $this->assertEquals('owner@example.com', $output->email);
        $this->assertEquals('Test Company', $output->companyName);
        $this->assertEquals('John', $output->firstName);
        $this->assertEquals('Doe', $output->lastName);
        $this->assertEquals('John Doe', $output->fullName);
    }

    public function testThrowsExceptionWhenEmailAlreadyExists(): void
    {
        $input = new RegisterOwnerInput(
            email: 'existing@example.com',
            password: 'Password123!',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $existingOwner = new Owner(
            id: 'owner_1',
            email: 'existing@example.com',
            passwordHash: 'hash',
            companyName: 'Existing Company',
            firstName: 'Jane',
            lastName: 'Smith'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with($input->email)
            ->willReturn($existingOwner);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already registered');

        $this->useCase->execute($input);
    }
}
