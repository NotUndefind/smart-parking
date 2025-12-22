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

            $parkings = $this->searchParkingByLocationUseCase->execute($input);

            // Convertir les ParkingOutput en array pour le JSON
            $parkingsArray = array_map(function ($parkingOutput) {
                $data = $parkingOutput->toArray();
                // Ajouter available_spots pour le frontend (pour l'instant = total_spots)
                $data['available_spots'] = $data['total_spots'];
                return $data;
            }, $parkings);

            // Le frontend attend une clé "parkings"
            $this->jsonResponse(
                [
                    "success" => true,
                    "parkings" => $parkingsArray,
                    "count" => count($parkingsArray),
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
            
            $output = $this->getParkingDetailsUseCase->execute(
                $input->parkingId,
                $input->timestamp
            );

            // Convertir en array et adapter pour le frontend
            $parkingData = $output->toArray();
            // Ajouter les champs attendus par le frontend
            $parkingData['nom'] = $parkingData['name'];
            $parkingData['adresse'] = $parkingData['address'];
            $parkingData['nb_places'] = $parkingData['total_spots'];
            $parkingData['places_disponibles'] = $parkingData['available_spots'];
            // Calculer un tarif horaire moyen pour l'affichage
            $tariffs = $parkingData['tariffs'] ?? [];
            if (!empty($tariffs) && isset($tariffs[0]['price'])) {
                $parkingData['tarif_horaire'] = ($tariffs[0]['price'] / ($tariffs[0]['duration'] / 60)) ?? 2.0;
            } else {
                $parkingData['tarif_horaire'] = 2.0;
            }
            // Formater les horaires pour l'affichage
            $schedule = $parkingData['schedule'] ?? [];
            if (!empty($schedule) && isset($schedule[0]['hours'])) {
                $parkingData['horaires'] = $schedule[0]['hours'];
            } else {
                $parkingData['horaires'] = '24h/24';
            }

            // Le frontend attend une clé "parking"
            $this->jsonResponse(
                [
                    "success" => true,
                    "parking" => $parkingData,
                ],
                200,
            );
        } catch (\App\Domain\Exceptions\ParkingNotFoundException $e) {
            $this->jsonResponse(["error" => $e->getMessage()], 404);
        } catch (\Exception $e) {
            error_log("Erreur getParkingDetails: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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

            $reservations = $this->listUserReservationsUseCase->execute($userId);

            // Convertir les objets ReservationOutput en tableaux
            $reservationsArray = array_map(fn($res) => $res->toArray(), $reservations);

            // Le frontend attend une clé "reservations"
            $this->jsonResponse(
                [
                    "success" => true,
                    "reservations" => $reservationsArray,
                    "count" => count($reservationsArray),
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
            $subscriptions = $this->listAvailableSubscriptionsUseCase->execute(
                $parkingId,
            );

            $this->jsonResponse(
                [
                    "success" => true,
                    "subscriptions" => $subscriptions,
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

            $this->jsonResponse(
                [
                    "success" => true,
                    "subscriptions" => array_map(
                        fn($sub) => $sub->toArray(),
                        $subscriptions,
                    ),
                    "count" => count($subscriptions),
                ],
                200,
            );
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
                    "stationnement_id" => $output->id,
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
                    "penalty_amount" => $output->penaltyAmount,
                    "total" => $output->finalPrice + $output->penaltyAmount,
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

            $stationnements = $this->listUserStationnementsUseCase->execute($userId);

            // Le frontend attend une clé "stationnements"
            $this->jsonResponse(
                [
                    "success" => true,
                    "stationnements" => array_map(
                        fn($s) => $s->toArray(),
                        $stationnements,
                    ),
                    "count" => count($stationnements),
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
