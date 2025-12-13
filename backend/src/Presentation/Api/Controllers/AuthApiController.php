<?php

namespace Presentation\Api\Controllers;

// use Application\UseCases\Auth\RegisterUserUseCase;
// use Application\UseCases\Auth\RegisterOwnerUseCase;
// use Application\UseCases\Auth\LoginUseCase;

// use Application\DTOs\Input\RegisterUserInput;
// use Application\DTOs\Input\RegisterOwnerInput;
// use Application\DTOs\Input\LoginInput;
// use Application\DTOs\Output\AuthTokenOutput;

class AuthApiController
{
    // TODO: Injecter les Use Cases via le constructeur
    // private RegisterUserUseCase $registerUserUseCase;
    // private RegisterOwnerUseCase $registerOwnerUseCase;
    // private LoginUseCase $loginUseCase;

    public function __construct()
    {
        // TODO: Injection de dépendances
        // $this->registerUserUseCase = $registerUserUseCase;
        // $this->registerOwnerUseCase = $registerOwnerUseCase;
        // $this->loginUseCase = $loginUseCase;
    }
    public function registerUser(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['email']) || !isset($data['password']) || 
                !isset($data['nom']) || !isset($data['prenom'])) {
                $this->jsonResponse(['error' => 'Données manquantes'], 400);
                return;
            }
            // $input = new RegisterUserInput(
            //     email: $data['email'],
            //     password: $data['password'],
            //     nom: $data['nom'],
            //     prenom: $data['prenom']
            // );

            // $output = $this->registerUserUseCase->execute($input);

            // $this->jsonResponse([
            //     'success' => true,
            //     'user' => [
            //         'id' => $output->id,
            //         'email' => $output->email,
            //         'nom' => $output->nom,
            //         'prenom' => $output->prenom
            //     ],
            //     'token' => $output->token
            // ], 201);

            // Temporaire (en attendant les Use Cases)
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case RegisterUserUseCase'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function registerOwner(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['email']) || !isset($data['password']) || 
                !isset($data['nom']) || !isset($data['prenom'])) {
                $this->jsonResponse(['error' => 'Données manquantes'], 400);
                return;
            }

            // TODO: Créer Input DTO et appeler Use Case
            // $input = new RegisterOwnerInput(...);
            // $output = $this->registerOwnerUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case RegisterOwnerUseCase'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['email']) || !isset($data['password'])) {
                $this->jsonResponse(['error' => 'Email et mot de passe requis'], 400);
                return;
            }

            // TODO: Créer Input DTO et appeler Use Case
            // $input = new LoginInput(
            //     email: $data['email'],
            //     password: $data['password']
            // );
            // $output = $this->loginUseCase->execute($input);

            // TODO: Retourner token JWT
            // $this->jsonResponse([
            //     'success' => true,
            //     'token' => $output->token,
            //     'user' => [
            //         'id' => $output->userId,
            //         'email' => $output->email,
            //         'role' => $output->role // 'user' ou 'owner'
            //     ]
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case LoginUseCase'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}


