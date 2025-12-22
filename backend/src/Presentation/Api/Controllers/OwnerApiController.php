<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controllers;

//Middlewares
use App\Presentation\Middleware\AuthMiddleware;

//DTOs
use App\Application\DTOs\Input\AuthenticateOwnerInput;
use App\Application\DTOs\Input\RegisterOwnerInput;
use App\Application\DTOs\Input\CreateParkingInput;
use App\Application\DTOs\Input\UpdateParkingTariffInput;
use App\Application\DTOs\Input\UpdateParkingScheduleInput;
use App\Application\DTOs\Input\AddSubscriptionTypeInput;
use App\Application\DTOs\Input\GetAvailableSpotsAtTimeInput;
use App\Application\DTOs\Input\GetMonthlyRevenueInput;

//Use Cases
use App\Application\UseCases\Owner\RegisterOwnerUseCase;
use App\Application\UseCases\Owner\AuthenticateOwnerUseCase;
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

final class OwnerApiController
{
    public function __construct(
        private RegisterOwnerUseCase $registerOwnerUseCase,
        private AuthenticateOwnerUseCase $authenticateOwnerUseCase,
        private CreateParkingUseCase $createParkingUseCase,
        private ListOwnerParkingsUseCase $listOwnerParkingsUseCase,
        private UpdateParkingTariffUseCase $updateParkingTariffUseCase,
        private UpdateParkingScheduleUseCase $updateParkingScheduleUseCase,
        private AddSubscriptionTypeUseCase $addSubscriptionTypeUseCase,
        private ListParkingReservationsUseCase $listParkingReservationsUseCase,
        private ListParkingStationnementsUseCase $listParkingStationnementsUseCase,
        private GetAvailableSpotsAtTimeUseCase $getAvailableSpotsAtTimeUseCase,
        private GetMonthlyRevenueUseCase $getMonthlyRevenueUseCase,
        private ListOverstayingUsersUseCase $listOverstayingUsersUseCase,
        private AuthMiddleware $authMiddleware,
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
                    $input["company_name"]
                )
            ) {
                $this->jsonResponse(["error" => "Missing required fields"], 400);
                return;
            }

            $registerInput = RegisterOwnerInput::create(
                email: $input["email"],
                password: $input["password"],
                companyName: $input["company_name"],
                firstName: $input["first_name"],
                lastName: $input["last_name"]
            );

            $output = $this->registerOwnerUseCase->execute($registerInput);

            $this->jsonResponse(
                [
                    "success" => true,
                    "owner" => [
                        "id" => $output->id,
                        "email" => $output->email,
                        "company_name" => $output->companyName ?? null,
                        "first_name" => $output->firstName,
                        "last_name" => $output->lastName,
                    ]
                ],
                201
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
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["email"], $data["password"])) {
                $this->jsonResponse(["error" => "Missing email or password"], 400);
                return;
            }

            $input = AuthenticateOwnerInput::create(
                email: $data["email"],
                password: $data["password"]
            );

            $output = $this->authenticateOwnerUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "token" => $output->token,
                "role" => "owner"
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 401);
        }
    }

    public function createParking(): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            $input = CreateParkingInput::create(
                ownerId: $ownerId,
                name: $data["name"] ?? "",
                address: $data["address"] ?? "",
                latitude: (float) ($data["latitude"] ?? 0),
                longitude: (float) ($data["longitude"] ?? 0),
                totalSpots: (int) ($data["total_spots"] ?? 0),
                tariffs: $data["tariffs"] ?? [],
                schedule: $data["schedule"] ?? []
            );

            $output = $this->createParkingUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "parking" => [
                    "id" => $output->id,
                    "name" => $output->name,
                    "address" => $output->address,
                    "latitude" => $output->latitude,
                    "longitude" => $output->longitude,
                    "total_spots" => $output->totalSpots,
                    "tariffs" => $output->tariffs,
                    "schedule" => $output->schedule,
                ]
            ], 201);
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function listOwnerParkings(string $ownerId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $authenticatedOwnerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            // Vérifier que l'owner demande ses propres parkings
            if ($authenticatedOwnerId !== $ownerId) {
                $this->jsonResponse(["error" => "Forbidden"], 403);
                return;
            }

            $parkings = $this->listOwnerParkingsUseCase->execute($ownerId);

            $this->jsonResponse([
                "success" => true,
                "parkings" => $parkings,
                "count" => count($parkings)
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function updateParkingTariff(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["tariffs"])) {
                $this->jsonResponse(["error" => "Missing tariffs"], 400);
                return;
            }

            $input = UpdateParkingTariffInput::create(
                parkingId: $parkingId,
                ownerId: $ownerId,
                tariffs: $data["tariffs"]
            );

            $output = $this->updateParkingTariffUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "parking" => [
                    "id" => $output->id,
                    "tariffs" => $output->tariffs,
                ]
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function updateParkingSchedule(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["schedule"])) {
                $this->jsonResponse(["error" => "Missing schedule"], 400);
                return;
            }

            $input = UpdateParkingScheduleInput::create(
                parkingId: $parkingId,
                ownerId: $ownerId,
                schedule: $data["schedule"]
            );

            $output = $this->updateParkingScheduleUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "parking" => [
                    "id" => $output->id,
                    "schedule" => $output->schedule,
                ]
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function addSubscriptionType(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["type"], $data["price"])) {
                $this->jsonResponse(["error" => "Missing type or price"], 400);
                return;
            }

            $input = AddSubscriptionTypeInput::create(
                parkingId: $parkingId,
                ownerId: $ownerId,
                type: $data["type"],
                price: (float) $data["price"],
                duration: (int) ($data["duration"] ?? 30),
                description: $data["description"] ?? null
            );

            $output = $this->addSubscriptionTypeUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "subscription_type" => [
                    "type" => $output->type,
                    "price" => $output->price,
                    "duration" => $output->duration
                ]
            ], 201);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function listParkingReservations(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $output = $this->listParkingReservationsUseCase->execute($parkingId, $ownerId);

            $this->jsonResponse([
                "success" => true,
                "reservations" => $output->reservations,
                "count" => count($output->reservations)
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function listParkingStationnements(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $output = $this->listParkingStationnementsUseCase->execute($parkingId, $ownerId);

            $this->jsonResponse([
                "success" => true,
                "stationnements" => $output->stationnements,
                "count" => count($output->stationnements)
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function getAvailableSpotsAtTime(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $timestamp = (int) ($_GET["timestamp"] ?? time());

            $input = GetAvailableSpotsAtTimeInput::create(
                parkingId: $parkingId,
                ownerId: $ownerId,
                timestamp: $timestamp
            );

            $output = $this->getAvailableSpotsAtTimeUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "parking_id" => $parkingId,
                "timestamp" => $timestamp,
                "available_spots" => $output->availableSpots,
                "total_spots" => $output->totalSpots,
                "occupied_spots" => $output->totalSpots - $output->availableSpots
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function getMonthlyRevenue(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $month = (int) ($_GET["month"] ?? date("n"));
            $year = (int) ($_GET["year"] ?? date("Y"));

            $input = GetMonthlyRevenueInput::create(
                parkingId: $parkingId,
                ownerId: $ownerId,
                month: $month,
                year: $year
            );

            $output = $this->getMonthlyRevenueUseCase->execute($input);

            $this->jsonResponse([
                "success" => true,
                "parking_id" => $parkingId,
                "month" => $month,
                "year" => $year,
                "total_revenue" => $output->totalRevenue,
                "reservations_revenue" => $output->reservationsRevenue,
                "subscriptions_revenue" => $output->subscriptionsRevenue,
                "penalties_revenue" => $output->penaltiesRevenue ?? 0,
                "reservations_count" => $output->reservationsCount
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function listOverstayingUsers(string $parkingId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $ownerId = $payload["sub"];

            if (($payload["role"] ?? "") !== "owner") {
                $this->jsonResponse(["error" => "Unauthorized"], 403);
                return;
            }

            $output = $this->listOverstayingUsersUseCase->execute($parkingId, $ownerId);

            $this->jsonResponse([
                "success" => true,
                "overstaying_users" => $output->overstayingUsers,
                "count" => count($output->overstayingUsers)
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header("Content-Type: application/json");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
