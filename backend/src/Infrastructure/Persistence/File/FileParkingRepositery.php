<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\ParkingRepositoryInterface;
use Domain\Entities\Parking;
use Domain\Entities\TarifHoraire;

class FileParkingRepository implements ParkingRepositoryInterface
{
    private string $parkingsFile;
    private string $tarifsFile;

    public function __construct(string $dataDirectory)
    {
        $dataDirectory = rtrim($dataDirectory, '/');
        $this->parkingsFile = $dataDirectory . '/parkings.json';
        $this->tarifsFile = $dataDirectory . '/tarifs_horaires.json';
        $this->ensureFilesExist();
    }

    public function save(Parking $parking): void
    {
        $parkings = $this->loadAllParkings();
        $parkings[$parking->getId()] = [
            'id' => $parking->getId(),
            'owner_id' => $parking->getOwnerId(),
            'nom' => $parking->getNom(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'nb_places' => $parking->getNbPlaces(),
            'horaires_ouverture' => $parking->getHorairesOuverture(),
            'created_at' => $parkings[$parking->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAllParkings($parkings);
        $this->saveTarifs($parking);
    }

    private function saveTarifs(Parking $parking): void
    {
        $allTarifs = $this->loadAllTarifs();
        
        $allTarifs = array_filter($allTarifs, fn($t) => $t['parking_id'] !== $parking->getId());

        foreach ($parking->getTarifsHoraires() as $tarif) {
            $allTarifs[$tarif->getId()] = [
                'id' => $tarif->getId(),
                'parking_id' => $parking->getId(),
                'tranche_duree' => $tarif->getTrancheDuree(),
                'prix' => $tarif->getPrix(),
                'ordre' => $tarif->getOrdre()
            ];
        }

        $this->saveAllTarifs($allTarifs);
    }

    public function findById(string $id): ?Parking
    {
        $parkings = $this->loadAllParkings();

        if (!isset($parkings[$id])) {
            return null;
        }

        return $this->hydrate($parkings[$id]);
    }

    public function findByOwnerId(string $ownerId): array
    {
        $parkings = $this->loadAllParkings();
        $result = [];

        foreach ($parkings as $parkingData) {
            if ($parkingData['owner_id'] === $ownerId) {
                $result[] = $this->hydrate($parkingData);
            }
        }

        return $result;
    }

    public function searchByGPS(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        $parkings = $this->loadAllParkings();
        $result = [];

        foreach ($parkings as $parkingData) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $parkingData['latitude'],
                $parkingData['longitude']
            );

            if ($distance <= $radiusKm) {
                $result[] = [
                    'parking' => $this->hydrate($parkingData),
                    'distance' => $distance
                ];
            }
        }

        usort($result, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return array_map(fn($item) => $item['parking'], $result);
    }

    public function delete(string $id): void
    {
        $parkings = $this->loadAllParkings();
        if (isset($parkings[$id])) {
            unset($parkings[$id]);
            $this->saveAllParkings($parkings);
        }

        // Supprimer les tarifs associés
        $tarifs = $this->loadAllTarifs();
        $tarifs = array_filter($tarifs, fn($t) => $t['parking_id'] !== $id);
        $this->saveAllTarifs($tarifs);
    }

    public function findAll(): array
    {
        $parkings = $this->loadAllParkings();
        return array_map(fn($data) => $this->hydrate($data), array_values($parkings));
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; 

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function hydrate(array $data): Parking
    {
        $allTarifs = $this->loadAllTarifs();
        $tarifsData = array_filter($allTarifs, fn($t) => $t['parking_id'] === $data['id']);

        usort($tarifsData, fn($a, $b) => $a['ordre'] <=> $b['ordre']);

        $tarifs = array_map(function($tarifData) {
            return new TarifHoraire(
                id: $tarifData['id'],
                parkingId: $tarifData['parking_id'],
                trancheDuree: (int)$tarifData['tranche_duree'],
                prix: (float)$tarifData['prix'],
                ordre: (int)$tarifData['ordre']
            );
        }, $tarifsData);

        return new Parking(
            id: $data['id'],
            ownerId: $data['owner_id'],
            nom: $data['nom'],
            latitude: (float)$data['latitude'],
            longitude: (float)$data['longitude'],
            nbPlaces: (int)$data['nb_places'],
            tarifsHoraires: $tarifs,
            horairesOuverture: $data['horaires_ouverture'] ?? []
        );
    }

    private function loadAllParkings(): array
    {
        $json = file_get_contents($this->parkingsFile);
        return json_decode($json, true) ?? [];
    }

    private function saveAllParkings(array $parkings): void
    {
        $json = json_encode($parkings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->parkingsFile, $json, LOCK_EX);
    }

    private function loadAllTarifs(): array
    {
        $json = file_get_contents($this->tarifsFile);
        return json_decode($json, true) ?? [];
    }

    private function saveAllTarifs(array $tarifs): void
    {
        $json = json_encode($tarifs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->tarifsFile, $json, LOCK_EX);
    }

    private function ensureFilesExist(): void
    {
        $dir = dirname($this->parkingsFile);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->parkingsFile)) {
            file_put_contents($this->parkingsFile, json_encode([]), LOCK_EX);
        }

        if (!file_exists($this->tarifsFile)) {
            file_put_contents($this->tarifsFile, json_encode([]), LOCK_EX);
        }
    }
}