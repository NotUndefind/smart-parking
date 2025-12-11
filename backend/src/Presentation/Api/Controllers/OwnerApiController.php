<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controllers;

use App\Application\DTOs\Input\AuthenticateOwnerInput;
use App\Application\DTOs\Input\RegisterOwnerInput;
use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
use App\Application\UseCases\Owner\RegisterOwnerUseCase;

final class OwnerApiController
{
    public function __construct(
        private RegisterOwnerUseCase $registerOwnerUseCase,
        private AuthenticateOwnerUseCase $authenticateOwnerUseCase
    ) {
    }

    public function register(): void
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['email'], $input['password'], $input['company_name'], $input['first_name'], $input['last_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }

            $registerInput = new RegisterOwnerInput(
                email: $input['email'],
                password: $input['password'],
                companyName: $input['company_name'],
                firstName: $input['first_name'],
                lastName: $input['last_name']
            );

            $output = $this->registerOwnerUseCase->execute($registerInput);

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

            $authInput = new AuthenticateOwnerInput(
                email: $input['email'],
                password: $input['password']
            );

            $output = $this->authenticateOwnerUseCase->execute($authInput);

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

