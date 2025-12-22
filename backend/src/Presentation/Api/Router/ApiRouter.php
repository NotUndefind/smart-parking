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
        $uri = rtrim($uri, "/");

        // Enlever le préfixe /api pour simplifier le matching
        $path = preg_replace('#^/api#', '', $uri);

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

        // Routes Owner - Gestion des parkings
        if ($method === 'POST' && $path === '/parkings') {
            $this->ownerController->createParking();
            return;
        }

        if ($method === 'GET' && preg_match('#^/owners/([^/]+)/parkings$#', $path, $matches)) {
            $this->ownerController->listOwnerParkings($matches[1]);
            return;
        }

        if ($method === 'PUT' && preg_match('#^/parkings/([^/]+)/tariff$#', $path, $matches)) {
            $this->ownerController->updateParkingTariff($matches[1]);
            return;
        }

        if ($method === 'PUT' && preg_match('#^/parkings/([^/]+)/schedule$#', $path, $matches)) {
            $this->ownerController->updateParkingSchedule($matches[1]);
            return;
        }

        if ($method === 'POST' && preg_match('#^/parkings/([^/]+)/subscription-types$#', $path, $matches)) {
            $this->ownerController->addSubscriptionType($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/reservations$#', $path, $matches)) {
            $this->ownerController->listParkingReservations($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/stationnements$#', $path, $matches)) {
            $this->ownerController->listParkingStationnements($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/availability$#', $path, $matches)) {
            $this->ownerController->getAvailableSpotsAtTime($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/revenue$#', $path, $matches)) {
            $this->ownerController->getMonthlyRevenue($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/parkings/([^/]+)/overstays$#', $path, $matches)) {
            $this->ownerController->listOverstayingUsers($matches[1]);
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

        if ($method === 'GET' && preg_match('#^/users/([^/]+)/subscriptions$#', $path, $matches)) {
            $this->userController->listUserSubscriptions($matches[1]);
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
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode([
            "error" => "Route not found",
            "method" => $method,
            "uri" => $uri,
        ]);
    }
}
