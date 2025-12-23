<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Use Cases - Auth
use App\Application\UseCases\Auth\AuthenticateUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;

// Use Cases - Owner
use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
use App\Application\UseCases\Owner\RegisterOwnerUseCase;
use App\Application\UseCases\Owner\CreateParkingUseCase;
use App\Application\UseCases\Owner\ListOwnerParkingsUseCase;
use App\Application\UseCases\Owner\UpdateParkingTariffUseCase;
use App\Application\UseCases\Owner\UpdateParkingScheduleUseCase;
use App\Application\UseCases\Owner\AddSubscriptionTypeUseCase;
use App\Application\UseCases\Owner\ListParkingReservationsUseCase;
use App\Application\UseCases\Owner\ListParkingStationnementsUseCase;
use App\Application\UseCases\Owner\GetAvailableSpotsAtTimeUseCase;
use App\Application\UseCases\Owner\GetMonthlyRevenueUseCase;
use App\Application\UseCases\Owner\ListOverstayingUsersUseCase;

// Use Cases - User
use App\Application\UseCases\User\SearchParkingsByLocationUseCase;
use App\Application\UseCases\User\GetParkingDetailsUseCase;
use App\Application\UseCases\User\CreateReservationUseCase;
use App\Application\UseCases\User\ListUserReservationsUseCase;
use App\Application\UseCases\User\ListAvailableSubscriptionsUseCase;
use App\Application\UseCases\User\ListUserSubscriptionsUseCase;
use App\Application\UseCases\User\SubscribeToPlanUseCase;
use App\Application\UseCases\User\EnterParkingUseCase;
use App\Application\UseCases\User\ExitParkingUseCase;
use App\Application\UseCases\User\ListUserStationnementsUseCase;
use App\Application\UseCases\User\GenerateInvoiceUseCase;

// Validators
use App\Application\Validators\EmailValidator;
use App\Application\Validators\GPSCoordinatesValidator;
use App\Application\Validators\PasswordValidator;
use App\Application\Validators\TimeSlotValidator;

// Infrastructure - Persistence File
use App\Infrastructure\Persistence\File\FileOwnerRepository;
use App\Infrastructure\Persistence\File\FileUserRepository;
use App\Infrastructure\Persistence\File\FileParkingRepository;
use App\Infrastructure\Persistence\File\FileReservationRepository;
use App\Infrastructure\Persistence\File\FileStationnementRepository;
use App\Infrastructure\Persistence\File\FileSubscriptionRepository;

// Infrastructure - Persistence SQL
use App\Infrastructure\Persistence\SQL\MySQLOwnerRepository;
use App\Infrastructure\Persistence\SQL\MySQLUserRepository;
use App\Infrastructure\Persistence\SQL\MySQLParkingRepository;
use App\Infrastructure\Persistence\SQL\MySQLReservationRepository;
use App\Infrastructure\Persistence\SQL\MySQLStationnementRepository;
use App\Infrastructure\Persistence\SQL\MySQLSubscriptionRepository;

// Infrastructure - Security
use App\Infrastructure\Security\JWTService;
use App\Infrastructure\Security\PasswordHasher;

// Presentation - Middleware
use App\Presentation\Middleware\AuthMiddleware;

// Presentation - Controllers
use App\Presentation\Api\Controllers\AuthApiController;
use App\Presentation\Api\Controllers\OwnerApiController;
use App\Presentation\Api\Controllers\UserApiController;

// Presentation - Router
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
    $parkingRepository = new MySQLParkingRepository($pdo);
    $reservationRepository = new MySQLReservationRepository($pdo);
    $stationnementsRepository = new MySQLStationnementRepository($pdo);
    $subscriptionRepository = new MySQLSubscriptionRepository($pdo);
} else {
    $userRepository = new FileUserRepository();
    $ownerRepository = new FileOwnerRepository();
    $parkingRepository = new FileParkingRepository();
    $reservationRepository = new FileReservationRepository();
    $stationnementsRepository = new FileStationnementRepository();
    $subscriptionRepository = new FileSubscriptionRepository();
}

// Validators
$emailValidator = new EmailValidator();
$passwordValidator = new PasswordValidator();
$gpsValidator = new GPSCoordinatesValidator();
$timeSlotValidator = new TimeSlotValidator();

// Middleware
$authMiddleware = new AuthMiddleware($jwtService);

// ============================================================================
// Use Cases - Auth
// ============================================================================
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

// ============================================================================
// Use Cases - User
// ============================================================================
$searchParkingsByLocationUseCase = new SearchParkingsByLocationUseCase(
    parkingRepository: $parkingRepository,
    gpsCoordinatesValidator: $gpsValidator
);

$getParkingDetailsUseCase = new GetParkingDetailsUseCase(
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository,
    subscriptionRepository: $subscriptionRepository
);

$createReservationUseCase = new CreateReservationUseCase(
    userRepository: $userRepository,
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository,
    timeSlotValidator: $timeSlotValidator
);

$listUserReservationsUseCase = new ListUserReservationsUseCase(
    reservationRepository: $reservationRepository,
    parkingRepository: $parkingRepository
);

$listAvailableSubscriptionsUseCase = new ListAvailableSubscriptionsUseCase(
    parkingRepository: $parkingRepository
);

$listUserSubscriptionsUseCase = new ListUserSubscriptionsUseCase(
    subscriptionRepository: $subscriptionRepository,
    parkingRepository: $parkingRepository
);

$subscribeToPlanUseCase = new SubscribeToPlanUseCase(
    userRepository: $userRepository,
    parkingRepository: $parkingRepository,
    subscriptionRepository: $subscriptionRepository
);

$enterParkingUseCase = new EnterParkingUseCase(
    userRepository: $userRepository,
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository,
    subscriptionRepository: $subscriptionRepository,
    stationnementRepository: $stationnementsRepository
);

$exitParkingUseCase = new ExitParkingUseCase(
    stationnementRepository: $stationnementsRepository,
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository
);

$listUserStationnementsUseCase = new ListUserStationnementsUseCase(
    stationnementRepository: $stationnementsRepository,
    parkingRepository: $parkingRepository
);

$generateInvoiceUseCase = new GenerateInvoiceUseCase(
    stationnementRepository: $stationnementsRepository,
    parkingRepository: $parkingRepository,
    userRepository: $userRepository
);

// ============================================================================
// Use Cases - Owner
// ============================================================================
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

$createParkingUseCase = new CreateParkingUseCase(
    ownerRepository: $ownerRepository,
    parkingRepository: $parkingRepository
);

$listOwnerParkingsUseCase = new ListOwnerParkingsUseCase(
    parkingRepository: $parkingRepository,
    ownerRepository: $ownerRepository
);

$updateParkingTariffUseCase = new UpdateParkingTariffUseCase(
    parkingRepository: $parkingRepository
);

$updateParkingScheduleUseCase = new UpdateParkingScheduleUseCase(
    parkingRepository: $parkingRepository
);

$addSubscriptionTypeUseCase = new AddSubscriptionTypeUseCase(
    parkingRepository: $parkingRepository
);

$listParkingReservationsUseCase = new ListParkingReservationsUseCase(
    reservationRepository: $reservationRepository,
    parkingRepository: $parkingRepository,
    userRepository: $userRepository
);

$listParkingStationnementsUseCase = new ListParkingStationnementsUseCase(
    stationnementRepository: $stationnementsRepository,
    parkingRepository: $parkingRepository,
    userRepository: $userRepository
);

$getAvailableSpotsAtTimeUseCase = new GetAvailableSpotsAtTimeUseCase(
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository,
    subscriptionRepository: $subscriptionRepository
);

$getMonthlyRevenueUseCase = new GetMonthlyRevenueUseCase(
    parkingRepository: $parkingRepository,
    reservationRepository: $reservationRepository,
    stationnementRepository: $stationnementsRepository,
    subscriptionRepository: $subscriptionRepository
);

$listOverstayingUsersUseCase = new ListOverstayingUsersUseCase(
    parkingRepository: $parkingRepository,
    stationnementRepository: $stationnementsRepository,
    reservationRepository: $reservationRepository,
    userRepository: $userRepository
);

// ============================================================================
// Controllers
// ============================================================================
$authController = new AuthApiController(
    registerUserUseCase: $registerUserUseCase,
    authenticateUserUseCase: $authenticateUserUseCase
);

$userController = new UserApiController(
    searchParkingByLocationUseCase: $searchParkingsByLocationUseCase,
    getParkingDetailsUseCase: $getParkingDetailsUseCase,
    createReservationUseCase: $createReservationUseCase,
    listUserReservationsUseCase: $listUserReservationsUseCase,
    listAvailableSubscriptionsUseCase: $listAvailableSubscriptionsUseCase,
    listUserSubscriptionsUseCase: $listUserSubscriptionsUseCase,
    subscribeToPlanUseCase: $subscribeToPlanUseCase,
    enterParkingUseCase: $enterParkingUseCase,
    exitParkingUseCase: $exitParkingUseCase,
    listUserStationnementsUseCase: $listUserStationnementsUseCase,
    generateInvoiceUseCase: $generateInvoiceUseCase,
    authMiddleware: $authMiddleware
);

$ownerController = new OwnerApiController(
    registerOwnerUseCase: $registerOwnerUseCase,
    authenticateOwnerUseCase: $authenticateOwnerUseCase,
    createParkingUseCase: $createParkingUseCase,
    listOwnerParkingsUseCase: $listOwnerParkingsUseCase,
    updateParkingTariffUseCase: $updateParkingTariffUseCase,
    updateParkingScheduleUseCase: $updateParkingScheduleUseCase,
    addSubscriptionTypeUseCase: $addSubscriptionTypeUseCase,
    listParkingReservationsUseCase: $listParkingReservationsUseCase,
    listParkingStationnementsUseCase: $listParkingStationnementsUseCase,
    getAvailableSpotsAtTimeUseCase: $getAvailableSpotsAtTimeUseCase,
    getMonthlyRevenueUseCase: $getMonthlyRevenueUseCase,
    listOverstayingUsersUseCase: $listOverstayingUsersUseCase,
    authMiddleware: $authMiddleware
);

// Router
$router = new ApiRouter($authController, $ownerController, $userController);

return $router;
