<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controllers;

use App\Application\DTOs\Input\AuthenticateUserInput;
use App\Application\DTOs\Input\RegisterUserInput;
use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;

final class AuthApiController
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase,
        private AuthenticateUserUseCase $authenticateUserUseCase
    ) {
    }

    public function register(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email'], $input['password'], $input['first_name'], $input['last_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            $registerInput = new RegisterUserInput(
                email: $input['email'],
                password: $input['password'],
                firstName: $input['first_name'],
                lastName: $input['last_name']
            );

            $output = $this->registerUserUseCase->execute($registerInput);

            http_response_code(201);
            echo json_encode($output->toArray());
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }

    public function login(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email'], $input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing email or password']);
                return;
            }

            $authInput = new AuthenticateUserInput(
                email: $input['email'],
                password: $input['password']
            );

            $output = $this->authenticateUserUseCase->execute($authInput);

            http_response_code(200);
            echo json_encode($output->toArray());
        } catch (\App\Domain\Exceptions\InvalidCredentialsException $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}

