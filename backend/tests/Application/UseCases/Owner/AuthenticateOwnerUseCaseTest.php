<?php

declare(strict_types=1);

namespace Tests\Application\UseCases\Owner;

use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
use App\Application\DTOs\Input\AuthenticateOwnerInput;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Helpers\EntityFactory;
use App\Domain\Entities\Owner;

#[CoversClass(AuthenticateOwnerUseCase::class)]
#[CoversClass(Owner::class)]
final class AuthenticateOwnerUseCaseTest extends TestCase
{
    private OwnerRepositoryInterface $ownerRepository;
    private PasswordHasher $passwordHasher;
    private JWTService $jwtService;
    private AuthenticateOwnerUseCase $useCase;

    protected function setUp(): void
    {
        $this->ownerRepository = $this->createMock(OwnerRepositoryInterface::class);
        $this->passwordHasher = new PasswordHasher();
        $this->jwtService = new JWTService("test-secret-key");

        $this->useCase = new AuthenticateOwnerUseCase(
            $this->ownerRepository,
            $this->passwordHasher,
            $this->jwtService
        );
    }

    public function testSuccessfulAuthentication(): void
    {
        $hashedPassword = $this->passwordHasher->hash('correct_password');

        $owner = EntityFactory::createOwner([
            'id' => 'owner_1',
            'email' => 'owner@example.com',
            'passwordHash' => $hashedPassword,
            'companyName' => 'Test Company'
        ]);

        $input = AuthenticateOwnerInput::create(
            email: 'owner@example.com',
            password: 'correct_password'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with('owner@example.com')
            ->willReturn($owner);

        $result = $this->useCase->execute($input);

        $this->assertNotEmpty($result->token);
        $this->assertEquals('Bearer', $result->type);
        $this->assertEquals(3600, $result->expiresIn);
        $this->assertEquals('owner_1', $result->user->id);
        $this->assertEquals('owner@example.com', $result->user->email);
    }

    public function testThrowsExceptionWhenOwnerNotFound(): void
    {
        $input = AuthenticateOwnerInput::create(
            email: 'nonexistent@example.com',
            password: 'any_password'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with('nonexistent@example.com')
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $this->useCase->execute($input);
    }

    public function testThrowsExceptionWhenPasswordInvalid(): void
    {
        $hashedPassword = $this->passwordHasher->hash('correct_password');

        $owner = EntityFactory::createOwner([
            'email' => 'owner@example.com',
            'passwordHash' => $hashedPassword
        ]);

        $input = AuthenticateOwnerInput::create(
            email: 'owner@example.com',
            password: 'wrong_password'
        );

        $this->ownerRepository->expects($this->once())
            ->method('findByEmail')
            ->with('owner@example.com')
            ->willReturn($owner);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid email or password');

        $this->useCase->execute($input);
    }
}
