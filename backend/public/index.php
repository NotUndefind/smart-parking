<?php

<<<<<<< HEAD
require_once __DIR__ . '/../vendor/autoload.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use Presentation\Api\Router;
use Presentation\Api\Controllers\AuthApiController;
use Presentation\Api\Controllers\UserApiController;
use Presentation\Api\Controllers\OwnerApiController;

// Instancier les contrôleurs
$authController = new AuthApiController();
$userController = new UserApiController();
$ownerController = new OwnerApiController();

// Instancier le router
$router = new Router($authController, $userController, $ownerController);

// Dispatcher la requête
$router->dispatch();
=======
declare(strict_types=1);

$router = require __DIR__ . '/bootstrap.php';
$router->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
>>>>>>> origin/feature/imrane-user-auth-module
