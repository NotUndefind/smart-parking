<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Input\AuthenticateOwnerInput;
use App\Application\DTOs\Output\AuthTokenOutput;
use App\Application\DTOs\Output\OwnerOutput;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;

final class AuthenticateOwnerUseCase
{
    public function __construct(
        private OwnerRepositoryInterface $ownerRepository,
        private PasswordHasher $passwordHasher,
        private JWTService $jwtService
    ) {
    }

    public function execute(AuthenticateOwnerInput $input): AuthTokenOutput
    {
        // 1. Trouver le propriétaire par email
        $owner = $this->ownerRepository->findByEmail($input->email);
        if ($owner === null) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        // 2. Vérifier le mot de passe
        if (!$this->passwordHasher->verify($input->password, $owner->getPasswordHash())) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        // 3. Générer le token JWT
        $token = $this->jwtService->generateToken(
            userId: $owner->getId(),
            email: $owner->getEmail(),
            role: 'owner'
        );

        // 4. Créer le DTO OwnerOutput (converti en UserOutput pour AuthTokenOutput)
        $ownerOutput = new OwnerOutput(
            id: $owner->getId(),
            email: $owner->getEmail(),
            companyName: $owner->getCompanyName(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            createdAt: $owner->getCreatedAt()->format('Y-m-d H:i:s')
        );

        // 5. Convertir OwnerOutput en UserOutput pour compatibilité avec AuthTokenOutput
        $userOutput = new \App\Application\DTOs\Output\UserOutput(
            id: $ownerOutput->id,
            email: $ownerOutput->email,
            firstName: $ownerOutput->firstName,
            lastName: $ownerOutput->lastName,
            fullName: $ownerOutput->fullName,
            createdAt: $ownerOutput->createdAt
        );

        // 6. Retourner le token avec les infos propriétaire
        return new AuthTokenOutput(
            token: $token,
            type: 'Bearer',
            expiresIn: 3600,
            user: $userOutput
        );
    }
}

