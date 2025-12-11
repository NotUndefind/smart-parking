<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
use App\Application\UseCases\Owner\RegisterOwnerUseCase;
use App\Application\Validators\EmailValidator;
use App\Application\Validators\PasswordValidator;
use App\Infrastructure\Persistence\File\FileOwnerRepository;
use App\Infrastructure\Persistence\File\FileUserRepository;
use App\Infrastructure\Persistence\SQL\MySQLOwnerRepository;
use App\Infrastructure\Persistence\SQL\MySQLUserRepository;
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;
use App\Presentation\Api\Controllers\AuthApiController;
use App\Presentation\Api\Controllers\OwnerApiController;
use App\Presentation\Api\Router\ApiRouter;

// Configuration
$jwtConfig = require __DIR__ . '/../config/jwt.php';
$storageType = $_ENV['STORAGE_TYPE'] ?? 'file'; // 'sql' ou 'file'

// Services Infrastructure
$passwordHasher = new PasswordHasher();
$jwtService = new JWTService(
    secret: $jwtConfig['secret'],
    issuer: $jwtConfig['issuer'],
    audience: $jwtConfig['audience'],
    accessTokenTtl: $jwtConfig['access_token_ttl']
);

// Repositories (choix SQL ou File)
if ($storageType === 'sql') {
    require_once __DIR__ . '/../config/database.php';
    $pdo = createPdoConnection();
    $userRepository = new MySQLUserRepository($pdo);
    $ownerRepository = new MySQLOwnerRepository($pdo);
} else {
    $userRepository = new FileUserRepository();
    $ownerRepository = new FileOwnerRepository();
}

// Validators
$emailValidator = new EmailValidator();
$passwordValidator = new PasswordValidator();

// Use Cases Utilisateur
$registerUserUseCase = new RegisterUserUseCase(
    userRepository: $userRepository,
    emailValidator: $emailValidator,
    passwordValidator: $passwordValidator,
    passwordHasher: $passwordHasher
);

$authenticateUserUseCase = new AuthenticateUserUseCase(
    userRepository: $userRepository,
    passwordHasher: $passwordHasher,
    jwtService: $jwtService
);

// Use Cases Propriétaire
$registerOwnerUseCase = new RegisterOwnerUseCase(
    ownerRepository: $ownerRepository,
    emailValidator: $emailValidator,
    passwordValidator: $passwordValidator,
    passwordHasher: $passwordHasher
);

$authenticateOwnerUseCase = new AuthenticateOwnerUseCase(
    ownerRepository: $ownerRepository,
    passwordHasher: $passwordHasher,
    jwtService: $jwtService
);

// Controllers
$authController = new AuthApiController(
    registerUserUseCase: $registerUserUseCase,
    authenticateUserUseCase: $authenticateUserUseCase
);

$ownerController = new OwnerApiController(
    registerOwnerUseCase: $registerOwnerUseCase,
    authenticateOwnerUseCase: $authenticateOwnerUseCase
);

// Router
$router = new ApiRouter($authController, $ownerController);

return $router;
