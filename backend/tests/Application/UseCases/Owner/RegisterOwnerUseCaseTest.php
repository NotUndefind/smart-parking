<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\RegisterOwnerUseCase;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Owner;

#[CoversClass(RegisterOwnerUseCase::class)]
#[CoversClass(Owner::class)]
final class RegisterOwnerUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private EmailValidator $emailValidator;
    private PasswordValidator $passwordValidator;
    private PasswordHasher $passwordHasher;
    private RegisterOwnerUseCase $useCase;

    protected function setUp(): void
    {
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->emailValidator = new EmailValidator();
        $this->passwordValidator = new PasswordValidator();
        $this->passwordHasher = new PasswordHasher();

        $this->useCase = new RegisterOwnerUseCase(
            $this->ownerRepository,
            $this->emailValidator,
            $this->passwordValidator,
            $this->passwordHasher
        );
    }

    public function testCanRegisterOwner(): void
    {
        $input = \App\Application\DTOs\Input\RegisterOwnerInput::create(
            email: 'owner@example.com',
            password: 'ValidPassword123!',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with('owner@example.com')
            ->willReturn(null);

        $this->ownerRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($owner) {
                return $owner instanceof Owner &&
                       $owner->getEmail() === 'owner@example.com' &&
                       $owner->getCompanyName() === 'Test Company';
            }));

        $result = $this->useCase->execute($input);

        $this->assertEquals('owner@example.com', $result->email);
        $this->assertEquals('Test Company', $result->companyName);
        $this->assertEquals('John', $result->firstName);
        $this->assertEquals('Doe', $result->lastName);
    }

    public function testThrowsExceptionWhenEmailAlreadyExists(): void
    {
        $existingOwner = EntityFactory::createOwner([
            'email' => 'existing@example.com'
        ]);

        $input = \App\Application\DTOs\Input\RegisterOwnerInput::create(
            email: 'existing@example.com',
            password: 'ValidPassword123!',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with('existing@example.com')
            ->willReturn($existingOwner);

        $this->ownerRepository->expects($this->never())
            ->method('save');

        $this->expectException(\InvalidArgumentException::class);

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenEmailInvalid(): void
    {
        $input = \App\Application\DTOs\Input\RegisterOwnerInput::create(
            email: 'invalid-email',
            password: 'ValidPassword123!',
            companyName: 'Test Company',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->expectException(\InvalidArgumentException::class);

        $this->useCase->execute($input);
    }
}
