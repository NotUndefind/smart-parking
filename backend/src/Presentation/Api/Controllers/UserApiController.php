<?php

namespace Presentation\Api\Controllers;

// TODO: Importer les Use Cases quand @Maoni les aura créés
// use Application\UseCases\User\SearchParkingsByGPSUseCase;
// use Application\UseCases\User\GetParkingDetailsUseCase;
// use Application\UseCases\User\CreateReservationUseCase;
// use Application\UseCases\User\GetUserReservationsUseCase;
// use Application\UseCases\User\SubscribeUseCase;
// use Application\UseCases\User\GetParkingSubscriptionsUseCase;
// use Application\UseCases\User\EnterParkingUseCase;
// use Application\UseCases\User\ExitParkingUseCase;
// use Application\UseCases\User\GetUserStationnementsUseCase;
// use Application\UseCases\User\GetInvoiceUseCase;

class UserApiController
{
    // TODO: Injecter les Use Cases
    // private SearchParkingsByGPSUseCase $searchParkingsUseCase;
    // private CreateReservationUseCase $createReservationUseCase;

    public function __construct()
    {
        // TODO: Injection de dépendances
    }

    public function searchParkings(): void
    {
        try {
            // 1. Récupérer les paramètres GET
            $latitude = $_GET['lat'] ?? null;
            $longitude = $_GET['lng'] ?? null;
            $radius = $_GET['radius'] ?? 5.0;

            if (!$latitude || !$longitude) {
                $this->jsonResponse(['error' => 'Latitude et longitude requises'], 400);
                return;
            }

            // TODO: 2. Créer Input DTO et appeler Use Case
            // $input = new SearchParkingsByGPSInput(
            //     latitude: (float)$latitude,
            //     longitude: (float)$longitude,
            //     radiusKm: (float)$radius
            // );
            // $output = $this->searchParkingsUseCase->execute($input);

            // TODO: 3. Retourner les parkings
            // $this->jsonResponse([
            //     'success' => true,
            //     'parkings' => $output->parkings
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case SearchParkingsByGPSUseCase',
                'params' => compact('latitude', 'longitude', 'radius')
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingDetails(string $parkingId): void
    {
        try {
            // TODO: Appeler Use Case
            // $input = new GetParkingDetailsInput(parkingId: $parkingId);
            // $output = $this->getParkingDetailsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetParkingDetailsUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/reservations
     * Créer une réservation
     * 
     * Body: {
     *   "parking_id": "parking-123",
     *   "debut": 1234567890,
     *   "fin": 1234571490
     * }
     */
    public function createReservation(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['parking_id']) || !isset($data['debut']) || !isset($data['fin'])) {
                $this->jsonResponse(['error' => 'parking_id, debut et fin requis'], 400);
                return;
            }

            // TODO: Récupérer userId depuis le JWT (via Middleware)
            // $userId = $_REQUEST['user_id']; // Injecté par AuthMiddleware

            // TODO: Créer Input DTO et appeler Use Case
            // $input = new CreateReservationInput(
            //     userId: $userId,
            //     parkingId: $data['parking_id'],
            //     debut: (int)$data['debut'],
            //     fin: (int)$data['fin']
            // );
            // $output = $this->createReservationUseCase->execute($input);

            // TODO: Retourner la réservation créée
            // $this->jsonResponse([
            //     'success' => true,
            //     'reservation' => [
            //         'id' => $output->id,
            //         'parking_nom' => $output->parkingNom,
            //         'debut' => $output->debut,
            //         'fin' => $output->fin,
            //         'prix_estime' => $output->prixEstime,
            //         'statut' => $output->statut
            //     ]
            // ], 201);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case CreateReservationUseCase',
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserReservations(): void
    {
        try {
            // TODO: Récupérer userId depuis le JWT
            // $userId = $_REQUEST['user_id'];

            // TODO: Appeler Use Case
            // $input = new GetUserReservationsInput(userId: $userId);
            // $output = $this->getUserReservationsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetUserReservationsUseCase'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingSubscriptions(string $parkingId): void
    {
        try {
            // TODO: Appeler Use Case
            // $input = new GetParkingSubscriptionsInput(parkingId: $parkingId);
            // $output = $this->getParkingSubscriptionsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetParkingSubscriptionsUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/subscriptions
     * Souscrire à un abonnement
     * 
     * Body: {
     *   "parking_id": "parking-123",
     *   "type": "weekend",
     *   "creneaux_reserves": [...],
     *   "duree_mois": 12
     * }
     */
    public function subscribe(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['parking_id']) || !isset($data['type'])) {
                $this->jsonResponse(['error' => 'parking_id et type requis'], 400);
                return;
            }

            // TODO: Créer Input DTO et appeler Use Case
            // $userId = $_REQUEST['user_id'];
            // $input = new SubscribeInput(...);
            // $output = $this->subscribeUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case SubscribeUseCase',
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function enterParking(string $parkingId): void
    {
        try {
            // TODO: Créer Input DTO et appeler Use Case
            // $userId = $_REQUEST['user_id'];
            // $input = new EnterParkingInput(
            //     userId: $userId,
            //     parkingId: $parkingId
            // );
            // $output = $this->enterParkingUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case EnterParkingUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function exitParking(string $parkingId): void
    {
        try {
            // TODO: Créer Input DTO et appeler Use Case
            // $userId = $_REQUEST['user_id'];
            // $input = new ExitParkingInput(
            //     userId: $userId,
            //     parkingId: $parkingId
            // );
            // $output = $this->exitParkingUseCase->execute($input);

            // TODO: Retourner la facture
            // $this->jsonResponse([
            //     'success' => true,
            //     'stationnement' => [
            //         'id' => $output->id,
            //         'debut' => $output->debut,
            //         'fin' => $output->fin,
            //         'duree_minutes' => $output->dureeMinutes,
            //         'montant' => $output->montant,
            //         'penalite' => $output->penalite
            //     ]
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case ExitParkingUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserStationnements(): void
    {
        try {
            // TODO: Récupérer userId depuis le JWT
            // $userId = $_REQUEST['user_id'];
            // $input = new GetUserStationnementsInput(userId: $userId);
            // $output = $this->getUserStationnementsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetUserStationnementsUseCase'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }


    public function getInvoice(string $reservationId): void
    {
        try {
            // TODO: Appeler Use Case
            // $input = new GetInvoiceInput(reservationId: $reservationId);
            // $output = $this->getInvoiceUseCase->execute($input);

            // TODO: Retourner facture en PDF ou HTML
            // if ($_GET['format'] === 'pdf') {
            //     header('Content-Type: application/pdf');
            //     echo $output->pdfContent;
            // } else {
            //     echo $output->htmlContent;
            // }

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetInvoiceUseCase',
                'reservation_id' => $reservationId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}