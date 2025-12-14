<?php

declare(strict_types=1);

namespace App\Application\UseCases\Owner;

use App\Application\DTOs\Input\RegisterOwnerInput;
use App\Application\DTOs\Output\OwnerOutput;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Entities\Owner;
use App\Domain\Repositories\OwnerRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;

final class RegisterOwnerUseCase
{
    public function __construct(
        private OwnerRepositoryInterface $ownerRepository,
        private EmailValidator $emailValidator,
        private PasswordValidator $passwordValidator,
        private PasswordHasher $passwordHasher
    ) {
    }

    public function execute(RegisterOwnerInput $input): OwnerOutput
    {
        // 1. Validation
        $this->emailValidator->validate($input->email);
        $this->passwordValidator->validate($input->password);

        // 2. Vérifier si l'email existe déjà
        $existingOwner = $this->ownerRepository->findByEmail($input->email);
        if ($existingOwner !== null) {
            throw new \InvalidArgumentException('Email already registered');
        }

        // 3. Hasher le mot de passe
        $passwordHash = $this->passwordHasher->hash($input->password);

        // 4. Créer l'entité Owner
        $owner = new Owner(
            id: uniqid('owner_', true),
            email: $input->email,
            passwordHash: $passwordHash,
            companyName: $input->companyName,
            firstName: $input->firstName,
            lastName: $input->lastName
        );

        // 5. Sauvegarder
        $this->ownerRepository->save($owner);

        // 6. Retourner le DTO Output
        return new OwnerOutput(
            id: $owner->getId(),
            email: $owner->getEmail(),
            companyName: $owner->getCompanyName(),
            firstName: $owner->getFirstName(),
            lastName: $owner->getLastName(),
            fullName: $owner->getFullName(),
            createdAt: $owner->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}

