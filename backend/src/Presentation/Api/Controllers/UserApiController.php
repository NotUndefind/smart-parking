<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controllers;

final class UserApiController
{
    public function __construct()
    {
    }

    public function searchParkings(): void
    {
        try {
            $latitude = $_GET['lat'] ?? null;
            $longitude = $_GET['lng'] ?? null;
            $radius = $_GET['radius'] ?? 5.0;

            if (!$latitude || !$longitude) {
                $this->jsonResponse(['error' => 'Latitude et longitude requises'], 400);
                return;
            }

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'params' => compact('latitude', 'longitude', 'radius')
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingDetails(string $parkingId): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function createReservation(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['parking_id']) || !isset($data['start_time']) || !isset($data['end_time'])) {
                $this->jsonResponse(['error' => 'parking_id, start_time et end_time requis'], 400);
                return;
            }

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserReservations(): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getParkingSubscriptions(string $parkingId): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function subscribe(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['parking_id']) || !isset($data['type'])) {
                $this->jsonResponse(['error' => 'parking_id et type requis'], 400);
                return;
            }

            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'data' => $data
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function enterParking(string $parkingId): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function exitParking(string $parkingId): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
                'parking_id' => $parkingId
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserStationnements(): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case'
            ], 501);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function getInvoice(string $reservationId): void
    {
        try {
            $this->jsonResponse([
                'message' => 'Endpoint prêt - En attente du Use Case',
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