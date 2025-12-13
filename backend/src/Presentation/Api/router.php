<?php

namespace Presentation\Api;

use Presentation\Api\Controllers\AuthApiController;
use Presentation\Api\Controllers\UserApiController;
use Presentation\Api\Controllers\OwnerApiController;


class Router
{
    private AuthApiController $authController;
    private UserApiController $userController;
    private OwnerApiController $ownerController;

    public function __construct(
        AuthApiController $authController,
        UserApiController $userController,
        OwnerApiController $ownerController
    ) {
        $this->authController = $authController;
        $this->userController = $userController;
        $this->ownerController = $ownerController;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = preg_replace('#^/api#', '', $path);

        // Routes Auth
        if ($method === 'POST' && $path === '/auth/register/user') {
            $this->authController->registerUser();
            return;
        }

        if ($method === 'POST' && $path === '/auth/register/owner') {
            $this->authController->registerOwner();
            return;
        }

        if ($method === 'POST' && $path === '/auth/login') {
            $this->authController->login();
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

        // Routes Owner - Parkings
        if ($method === 'POST' && $path === '/owner/parkings') {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->createParking();
            return;
        }

        if ($method === 'PUT' && preg_match('#^/owner/parkings/([^/]+)/tarifs$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->updateParkingTarifs($matches[1]);
            return;
        }

        if ($method === 'PUT' && preg_match('#^/owner/parkings/([^/]+)/horaires$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->updateParkingHoraires($matches[1]);
            return;
        }

        // Routes Owner - Gestion
        if ($method === 'GET' && preg_match('#^/owner/parkings/([^/]+)/reservations$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->getParkingReservations($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/owner/parkings/([^/]+)/stationnements$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->getParkingStationnements($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/owner/parkings/([^/]+)/available-places$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->getAvailablePlaces($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/owner/parkings/([^/]+)/revenue$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->getMonthlyRevenue($matches[1]);
            return;
        }

        if ($method === 'POST' && preg_match('#^/owner/parkings/([^/]+)/subscription-types$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->addSubscriptionType($matches[1]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/owner/parkings/([^/]+)/out-of-timeslot-drivers$#', $path, $matches)) {
            // TODO: Vérifier AuthMiddleware + role owner
            $this->ownerController->getOutOfTimeSlotDrivers($matches[1]);
            return;
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Route non trouvée',
            'method' => $method,
            'path' => $path
        ]);
    }
}