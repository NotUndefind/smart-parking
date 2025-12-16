<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Input\RegisterUserInput;
use App\Application\DTOs\Output\UserOutput;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\PasswordHasher;

final class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailValidator $emailValidator,
        private PasswordValidator $passwordValidator,
        private PasswordHasher $passwordHasher
    ) {
    }

    public function execute(RegisterUserInput $input): UserOutput
    {
        // 1. Validation
        $this->emailValidator->validate($input->email);
        $this->passwordValidator->validate($input->password);

        // 2. Vérifier si l'email existe déjà
        $existingUser = $this->userRepository->findByEmail($input->email);
        if ($existingUser !== null) {
            throw new \InvalidArgumentException('Email already registered');
        }

        // 3. Hasher le mot de passe
        $passwordHash = $this->passwordHasher->hash($input->password);

        // 4. Créer l'entité User
        $user = new User(
            id: uniqid('user_', true),
            email: $input->email,
            passwordHash: $passwordHash,
            firstName: $input->firstName,
            lastName: $input->lastName
        );

        // 5. Sauvegarder
        $this->userRepository->save($user);

        // 6. Retourner le DTO Output
        return new UserOutput(
            id: $user->getId(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            fullName: $user->getFullName(),
            createdAt: $user->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}

