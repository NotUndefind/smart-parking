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
        private AuthenticateUserUseCase $authenticateUserUseCase,
    ) {}

    public function register(): void
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            if (
                !isset(
                    $input["email"],
                    $input["password"],
                    $input["first_name"],
                    $input["last_name"],
                )
            ) {
                $this->jsonResponse(
                    ["error" => "Missing required fields"],
                    400,
                );
                return;
            }

            $registerInput = RegisterUserInput::create(
                email: $input["email"],
                password: $input["password"],
                firstName: $input["first_name"],
                lastName: $input["last_name"],
            );

            $output = $this->registerUserUseCase->execute($registerInput);

            $this->jsonResponse(
                [
                    "success" => true,
                    "user" => [
                        "id" => $output->id,
                        "email" => $output->email,
                        "first_name" => $output->firstName,
                        "last_name" => $output->lastName,
                        "full_name" => $output->fullName,
                        "created_at" => $output->createdAt,
                    ],
                ],
                201,
            );
        } catch (\InvalidArgumentException $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => "Internal server error"], 500);
        }
    }

    public function login(): void
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            if (!isset($input["email"], $input["password"])) {
                $this->jsonResponse(
                    ["error" => "Missing email or password"],
                    400,
                );
                return;
            }

            $authInput = AuthenticateUserInput::create(
                email: $input["email"],
                password: $input["password"],
            );

            $output = $this->authenticateUserUseCase->execute($authInput);

            $this->jsonResponse(
                [
                    "success" => true,
                    "token" => $output->token,
                    "type" => $output->type,
                    "expires_in" => $output->expiresIn,
                    "user" => [
                        "id" => $output->user->id,
                        "email" => $output->user->email,
                        "first_name" => $output->user->firstName,
                        "last_name" => $output->user->lastName,
                        "full_name" => $output->user->fullName,
                    ],
                ],
                200,
            );
        } catch (\App\Domain\Exceptions\InvalidCredentialsException $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 401);
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => "Internal server error"], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header("Content-Type: application/json");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
