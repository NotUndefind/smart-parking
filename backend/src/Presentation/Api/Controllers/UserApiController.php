<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controllers;

// Use Cases
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

// DTOs Input
use App\Application\DTOs\Input\CreateReservationInput;
use App\Application\DTOs\Input\SearchParkingsByLocationInput;
use App\Application\DTOs\Input\GetParkingDetailsInput;
use App\Application\DTOs\Input\SubscribeToPlanInput;
use App\Application\DTOs\Input\EnterParkingInput;
use App\Application\DTOs\Input\ExitParkingInput;
use App\Application\DTOs\Input\GenerateInvoiceInput;

// Auth
use App\Presentation\Middleware\AuthMiddleware;

final class UserApiController
{
    public function __construct(
        private SearchParkingsByLocationUseCase $searchParkingByLocationUseCase,
        private GetParkingDetailsUseCase $getParkingDetailsUseCase,
        private CreateReservationUseCase $createReservationUseCase,
        private ListUserReservationsUseCase $listUserReservationsUseCase,
        private ListAvailableSubscriptionsUseCase $listAvailableSubscriptionsUseCase,
        private ListUserSubscriptionsUseCase $listUserSubscriptionsUseCase,
        private SubscribeToPlanUseCase $subscribeToPlanUseCase,
        private EnterParkingUseCase $enterParkingUseCase,
        private ExitParkingUseCase $exitParkingUseCase,
        private ListUserStationnementsUseCase $listUserStationnementsUseCase,
        private GenerateInvoiceUseCase $generateInvoiceUseCase,
        private AuthMiddleware $authMiddleware,
    ) {}

    public function searchParkings(): void
    {
        try {
            $latitude = (float) ($_GET["lat"] ?? 0);
            $longitude = (float) ($_GET["lng"] ?? 0);
            $radius = (float) ($_GET["radius"] ?? 5.0);

            if (!$latitude || !$longitude) {
                $this->jsonResponse(
                    ["error" => "Latitude et longitude requises"],
                    400,
                );
                return;
            }

            // Create DTO Input
            $input = SearchParkingsByLocationInput::create(
                latitude: $latitude,
                longitude: $longitude,
                radiusKm: $radius,
            );

            $output = $this->searchParkingByLocationUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "data" => $output->parkings,
                    "count" => count($output->parkings),
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function getParkingDetails(string $parkingId): void
    {
        if (empty($parkingId)) {
            $this->jsonResponse(["error" => "parking_id est requis"], 400);
            return;
        }

        try {
            $input = GetParkingDetailsInput::create(parkingId: $parkingId);

            $output = $this->getParkingDetailsUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "data" => $output->parkingDetails,
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function createReservation(): void
    {
        try {
            $payload = $this->authMiddleware->handle();

            $data = json_decode(file_get_contents("php://input"), true);

            $input = CreateReservationInput::create(
                userId: $payload["sub"],
                parkingId: $data["parking_id"] ?? "",
                startTime: (int) ($data["start_time"] ?? 0),
                endTime: (int) ($data["end_time"] ?? 0),
            );

            $output = $this->createReservationUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "reservation" => [
                        "id" => $output->id,
                        "parking_id" => $output->parkingId,
                        "start_time" => $output->startTime,
                        "end_time" => $output->endTime,
                        "estimated_price" => $output->estimatedPrice,
                        "status" => $output->status,
                    ],
                ],
                201,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function getUserReservations(): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $userId = $payload["sub"];

            $output = $this->listUserReservationsUseCase->execute($userId);

            $this->jsonResponse(
                [
                    "success" => true,
                    "data" => $output->reservations,
                    "count" => count($output->reservations),
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function getParkingSubscriptions(string $parkingId): void
    {
        try {
            $output = $this->listAvailableSubscriptionsUseCase->execute(
                $parkingId,
            );

            $this->jsonResponse(
                [
                    "success" => true,
                    "subscriptions" => $output->subscriptions,
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function subscribe(): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $userId = $payload["sub"];

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["parking_id"]) || !isset($data["type"])) {
                $this->jsonResponse(
                    ["error" => "parking_id et type requis"],
                    400,
                );
                return;
            }

            $input = SubscribeToPlanInput::create(
                userId: $userId,
                parkingId: $data["parking_id"],
                type: $data["type"],
                startDate: (int) ($data["start_date"] ?? time()),
                endDate: isset($data["end_date"])
                    ? (int) $data["end_date"]
                    : null,
            );

            $output = $this->subscribeToPlanUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "subscription" => [
                        "id" => $output->id,
                        "parking_id" => $output->parkingId,
                        "type" => $output->type,
                        "start_date" => $output->startDate,
                        "end_date" => $output->endDate,
                    ],
                ],
                201,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function listUserSubscriptions(string $userId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $authenticatedUserId = $payload["sub"];

            // Vérifier que l'utilisateur demande ses propres abonnements
            if ($authenticatedUserId !== $userId) {
                $this->jsonResponse(["error" => "Forbidden"], 403);
                return;
            }

            $subscriptions = $this->listUserSubscriptionsUseCase->execute($userId);

            $this->jsonResponse([
                "success" => true,
                "subscriptions" => array_map(fn($sub) => [
                    'id' => $sub->id,
                    'parking_id' => $sub->parkingId,
                    'parking_name' => $sub->parkingName,
                    'type' => $sub->type,
                    'price' => $sub->price,
                    'start_date' => $sub->startDate,
                    'end_date' => $sub->endDate,
                    'is_active' => $sub->isActive
                ], $subscriptions),
                "count" => count($subscriptions)
            ], 200);

        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function enterParking(): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $userId = $payload["sub"];

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["parking_id"])) {
                $this->jsonResponse(["error" => "parking_id required"], 400);
                return;
            }

            $input = EnterParkingInput::create(
                userId: $userId,
                parkingId: $data["parking_id"],
                reservationId: $data["reservation_id"] ?? null,
                subscriptionId: $data["subscription_id"] ?? null,
            );

            $output = $this->enterParkingUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "stationnement_id" => $output->stationnementId,
                    "message" => "Entrée en parking enregistrée avec succès",
                ],
                201,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function exitParking(): void
    {
        try {
            $payload = $this->authMiddleware->handle();

            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data["stationnement_id"])) {
                $this->jsonResponse(
                    ["error" => "stationnement_id requis"],
                    400,
                );
                return;
            }

            $input = ExitParkingInput::create(
                stationnementId: $data["stationnement_id"],
            );

            $output = $this->exitParkingUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "final_price" => $output->finalPrice,
                    "penalty" => $output->penalty,
                    "total" => $output->total,
                    "message" => "Sortie de parking enregistrée avec succès",
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 400);
        }
    }

    public function getUserStationnements(): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $userId = $payload["sub"];

            $output = $this->listUserStationnementsUseCase->execute($userId);

            $this->jsonResponse(
                [
                    "success" => true,
                    "data" => $output->stationnements,
                    "count" => count($output->stationnements),
                ],
                200,
            );
        } catch (\Exception $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 500);
        }
    }

    public function getInvoice(string $stationnementId): void
    {
        try {
            $payload = $this->authMiddleware->handle();
            $userId = $payload["sub"];

            $input = GenerateInvoiceInput::create(
                stationnementId: $stationnementId,
                userId: $userId,
            );

            $output = $this->generateInvoiceUseCase->execute($input);

            $this->jsonResponse(
                [
                    "success" => true,
                    "invoice" => [
                        "pdf_url" => $output->pdfUrl ?? null,
                        "total" => $output->total,
                    ],
                ],
                200,
            );
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
