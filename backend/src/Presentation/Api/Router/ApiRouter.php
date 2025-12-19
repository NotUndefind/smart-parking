<?php

declare(strict_types=1);

namespace App\Presentation\Api\Router;

use App\Presentation\Api\Controllers\AuthApiController;
use App\Presentation\Api\Controllers\OwnerApiController;
use App\Presentation\Api\Controllers\UserApiController;

final class ApiRouter
{
    private AuthApiController $authController;
    private OwnerApiController $ownerController;
    private UserApiController $userController;

    public function __construct(
        AuthApiController $authController,
        OwnerApiController $ownerController,
        UserApiController $userController
    ) {
        $this->authController = $authController;
        $this->ownerController = $ownerController;
        $this->userController = $userController;
    }

    public function handle(string $method, string $uri): void
    {
        // Nettoyer l'URI (enlever les query params)
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');

        // Enlever le préfixe /api pour simplifier le matching
        $path = preg_replace('#^/api#', '', $uri);

        // Routes Auth (User)
        if ($method === 'POST' && $path === '/auth/register') {
            $this->authController->register();
            return;
        }

        if ($method === 'POST' && $path === '/auth/login') {
            $this->authController->login();
            return;
        }

        // Routes Auth (Owner)
        if ($method === 'POST' && $path === '/owner/register') {
            $this->ownerController->register();
            return;
        }

        if ($method === 'POST' && $path === '/owner/login') {
            $this->ownerController->login();
            return;
        }

        // Routes User - Parkings
        if ($method === 'GET' && $path === '/parkings/search') {
            $this->userController->searchParkings();
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)$#', $path, $matches)) {
            $this->userController->getParkingDetails($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/subscriptions$#', $path, $matches)) {
            $this->userController->getParkingSubscriptions($matches[1]);
            return;
        }

        // Routes User - Réservations
        if ($method === 'POST' && $path === '/reservations') {
            // TODO: Vérifier AuthMiddleware
            $this->userController->createReservation();
            return;
        }

        if ($method === 'GET' && $path === '/user/reservations') {
            // TODO: Vérifier AuthMiddleware
            $this->userController->getUserReservations();
            return;
        }

        if ($method === 'GET' && preg_match('#^/reservations/([^/]+)/invoice$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware
            $this->userController->getInvoice($matches[1]);
            return;
        }

        // Routes User - Abonnements
        if ($method === 'POST' && $path === '/subscriptions') {
            // TODO: Vérifier AuthMiddleware
            $this->userController->subscribe();
            return;
        }

        // Routes User - Stationnements
        if ($method === 'POST' && preg_match('#^/parkings/([^/]+)/enter$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware
            $this->userController->enterParking($matches[1]);
            return;
        }

        if ($method === 'POST' && preg_match('#^/parkings/([^/]+)/exit$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware
            $this->userController->exitParking($matches[1]);
            return;
        }

        if ($method === 'GET' && $path === '/user/stationnements') {
            // TODO: Vérifier AuthMiddleware
            $this->userController->getUserStationnements();
            return;
        }

        // Route par défaut
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode([
            'error' => 'Route non trouvée',
            'method' => $method,
            'uri' => $uri,
            'path' => $path
        ]);
    }
}
