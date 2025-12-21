<?php

declare(strict_types=1);

namespace App\Presentation\Api\Router;

use App\Presentation\Api\Controllers\AuthApiController;
use App\Presentation\Api\Controllers\OwnerApiController;

final class ApiRouter
{
    private AuthApiController $authController;
    private OwnerApiController $ownerController;

    public function __construct(
        AuthApiController $authController,
        OwnerApiController $ownerController,
    ) {
        $this->authController = $authController;
        $this->ownerController = $ownerController;
    }

    public function handle(string $method, string $uri): void
    {
        // Nettoyer l'URI (enlever les query params)
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, "/");

        // Routes d'authentification utilisateur
        if ($uri === "/api/auth/register" && $method === "POST") {
            $this->authController->register();
            return;
        }

        if ($uri === "/api/auth/login" && $method === "POST") {
            $this->authController->login();
            return;
        }

        // Routes d'authentification propriétaire
        if ($uri === "/api/owner/register" && $method === "POST") {
            $this->ownerController->register();
            return;
        }

        if ($uri === "/api/owner/login" && $method === "POST") {
            $this->ownerController->login();
            return;
        }

        // Route par défaut
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode([
            "error" => "Route not found",
            "method" => $method,
            "uri" => $uri,
        ]);
    }
}
