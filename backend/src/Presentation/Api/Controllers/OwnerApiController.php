<?php

namespace Presentation\Api\Controllers;

// TODO: Importer les Use Cases quand @Maoni les aura créés
// use Application\UseCases\Owner\CreateParkingUseCase;
// use Application\UseCases\Owner\UpdateParkingTarifsUseCase;
// use Application\UseCases\Owner\UpdateParkingHorairesUseCase;
// use Application\UseCases\Owner\GetParkingReservationsUseCase;
// use Application\UseCases\Owner\GetParkingStationnementsUseCase;
// use Application\UseCases\Owner\GetAvailablePlacesUseCase;
// use Application\UseCases\Owner\CalculateMonthlyRevenueUseCase;
// use Application\UseCases\Owner\AddSubscriptionTypeUseCase;
// use Application\UseCases\Owner\GetOutOfTimeSlotDriversUseCase;

class OwnerApiController
{
    // TODO: Injecter les Use Cases
    // private CreateParkingUseCase $createParkingUseCase;

    public function __construct()
    {
        // TODO: Injection de dépendances
    }

    /**
     * POST /api/owner/parkings
     * Créer un parking
     * 
     * Body: {
     *   "nom": "Parking Centre Ville",
     *   "latitude": 48.8566,
     *   "longitude": 2.3522,
     *   "nb_places": 50,
     *   "horaires_ouverture": {...},
     *   "tarifs": [...]
     * }
     */
    public function createParking(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['latitude']) || !isset($data['longitude']) || !isset($data['nb_places'])) {
                $this->jsonResponse(['error' => 'Données manquantes'], 400);
                return;
            }

            // TODO: Récupérer ownerId depuis le JWT
            // $ownerId = $_REQUEST['user_id'];

            // TODO: Créer Input DTO et appeler Use Case
            // $input = new CreateParkingInput(
            //     ownerId: $ownerId,
            //     nom: $data['nom'] ?? null,
            //     latitude: (float)$data['latitude'],
            //     longitude: (float)$data['longitude'],
            //     nbPlaces: (int)$data['nb_places'],
            //     horairesOuverture: $data['horaires_ouverture'] ?? [],
            //     tarifs: $data['tarifs'] ?? []
            // );
            // $output = $this->createParkingUseCase->execute($input);

            // TODO: Retourner le parking créé
            // $this->jsonResponse([
            //     'success' => true,
            //     'parking' => [
            //         'id' => $output->id,
            //         'nom' => $output->nom,
            //         'latitude' => $output->latitude,
            //         'longitude' => $output->longitude,
            //         'nb_places' => $output->nbPlaces
            //     ]
            // ], 201);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case CreateParkingUseCase',
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/owner/parkings/{id}/tarifs
     * Modifier les tarifs d'un parking
     * 
     * Body: {
     *   "tarifs": [
     *     {"tranche_duree": 60, "prix": 2.00, "ordre": 1},
     *     {"tranche_duree": 120, "prix": 1.50, "ordre": 2}
     *   ]
     * }
     */
    public function updateParkingTarifs(string $parkingId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['tarifs'])) {
                $this->jsonResponse(['error' => 'Tarifs requis'], 400);
                return;
            }

            // TODO: Vérifier que le parking appartient au owner connecté
            // $ownerId = $_REQUEST['user_id'];

            // TODO: Créer Input DTO et appeler Use Case
            // $input = new UpdateParkingTarifsInput(
            //     ownerId: $ownerId,
            //     parkingId: $parkingId,
            //     tarifs: $data['tarifs']
            // );
            // $output = $this->updateParkingTarifsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case UpdateParkingTarifsUseCase',
                'parking_id' => $parkingId,
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/owner/parkings/{id}/horaires
     * Modifier les horaires d'un parking
     * 
     * Body: {
     *   "horaires_ouverture": {
     *     "lundi": ["08:00-18:00"],
     *     "mardi": ["08:00-18:00"]
     *   }
     * }
     */
    public function updateParkingHoraires(string $parkingId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['horaires_ouverture'])) {
                $this->jsonResponse(['error' => 'Horaires requis'], 400);
                return;
            }

            // TODO: Créer Input DTO et appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new UpdateParkingHorairesInput(...);
            // $output = $this->updateParkingHorairesUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case UpdateParkingHorairesUseCase',
                'parking_id' => $parkingId,
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingReservations(string $parkingId): void
    {
        try {
            // TODO: Vérifier ownership
            // $ownerId = $_REQUEST['user_id'];
            // $input = new GetParkingReservationsInput(
            //     ownerId: $ownerId,
            //     parkingId: $parkingId
            // );
            // $output = $this->getParkingReservationsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetParkingReservationsUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingStationnements(string $parkingId): void
    {
        try {
            // TODO: Appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new GetParkingStationnementsInput(...);
            // $output = $this->getParkingStationnementsUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetParkingStationnementsUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getAvailablePlaces(string $parkingId): void
    {
        try {
            $timestamp = $_GET['timestamp'] ?? time();

            // TODO: Appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new GetAvailablePlacesInput(
            //     ownerId: $ownerId,
            //     parkingId: $parkingId,
            //     timestamp: (int)$timestamp
            // );
            // $output = $this->getAvailablePlacesUseCase->execute($input);

            // TODO: Retourner le nombre de places
            // $this->jsonResponse([
            //     'success' => true,
            //     'nb_places_totales' => $output->nbPlacesTotales,
            //     'nb_places_occupees' => $output->nbPlacesOccupees,
            //     'nb_places_disponibles' => $output->nbPlacesDisponibles
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetAvailablePlacesUseCase',
                'parking_id' => $parkingId,
                'timestamp' => $timestamp
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getMonthlyRevenue(string $parkingId): void
    {
        try {
            $month = $_GET['month'] ?? date('Y-m');
            $monthTimestamp = strtotime($month . '-01');

            // TODO: Appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new CalculateMonthlyRevenueInput(
            //     ownerId: $ownerId,
            //     parkingId: $parkingId,
            //     monthTimestamp: $monthTimestamp
            // );
            // $output = $this->calculateMonthlyRevenueUseCase->execute($input);

            // TODO: Retourner le CA
            // $this->jsonResponse([
            //     'success' => true,
            //     'month' => $month,
            //     'ca_reservations' => $output->caReservations,
            //     'ca_abonnements' => $output->caAbonnements,
            //     'ca_total' => $output->caTotal,
            //     'nb_reservations' => $output->nbReservations,
            //     'nb_abonnements' => $output->nbAbonnements
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case CalculateMonthlyRevenueUseCase',
                'parking_id' => $parkingId,
                'month' => $month
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/owner/parkings/{id}/subscription-types
     * Ajouter un type d'abonnement sur un parking
     * 
     * Body: {
     *   "type": "weekend",
     *   "creneaux": [...],
     *   "prix_mensuel": 80.00
     * }
     */
    public function addSubscriptionType(string $parkingId): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['type']) || !isset($data['prix_mensuel'])) {
                $this->jsonResponse(['error' => 'Type et prix_mensuel requis'], 400);
                return;
            }

            // TODO: Créer Input DTO et appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new AddSubscriptionTypeInput(...);
            // $output = $this->addSubscriptionTypeUseCase->execute($input);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case AddSubscriptionTypeUseCase',
                'parking_id' => $parkingId,
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getOutOfTimeSlotDrivers(string $parkingId): void
    {
        try {
            // TODO: Appeler Use Case
            // $ownerId = $_REQUEST['user_id'];
            // $input = new GetOutOfTimeSlotDriversInput(
            //     ownerId: $ownerId,
            //     parkingId: $parkingId
            // );
            // $output = $this->getOutOfTimeSlotDriversUseCase->execute($input);

            // TODO: Retourner la liste
            // $this->jsonResponse([
            //     'success' => true,
            //     'conducteurs_hors_creneaux' => $output->conducteurs
            // ], 200);

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case GetOutOfTimeSlotDriversUseCase',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper pour retourner une réponse JSON
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}