<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Input\AuthenticateUserInput;
use App\Application\DTOs\Output\AuthTokenOutput;
use App\Application\DTOs\Output\UserOutput;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;

final class AuthenticateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasher $passwordHasher,
        private JWTService $jwtService,
    ) {}

    public function execute(AuthenticateUserInput $input): AuthTokenOutput
    {
        // 1. Trouver l'utilisateur par email
        $user = $this->userRepository->findByEmail($input->email);
        if ($user === null) {
            throw new InvalidCredentialsException("Invalid email or password");
        }

        // 2. Vérifier le mot de passe
        if (
            !$this->passwordHasher->verify(
                $input->password,
                $user->getPasswordHash(),
            )
        ) {
            throw new InvalidCredentialsException("Invalid email or password");
        }

        // 3. Générer le token JWT
        $token = $this->jwtService->generateToken(
            userId: $user->getId(),
            email: $user->getEmail(),
            role: "user",
        );

        // 4. Créer le DTO UserOutput
        $userOutput = new UserOutput(
            id: $user->getId(),
            email: $user->getEmail(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            fullName: $user->getFullName(),
            createdAt: $user->getCreatedAt()->format("Y-m-d H:i:s"),
        );

        // 5. Retourner le token avec les infos utilisateur
        return new AuthTokenOutput(
            token: $token,
            type: "Bearer",
            expiresIn: 3600,
            user: $userOutput,
        );
    }
}
