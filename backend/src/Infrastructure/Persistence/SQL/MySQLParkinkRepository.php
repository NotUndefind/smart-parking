<?php

namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\ParkingRepositoryInterface;
use Domain\Entities\Parking;
use Domain\Entities\TarifHoraire;
use PDO;

class MySQLParkingRepository implements ParkingRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Parking $parking): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM parkings WHERE id = :id");
        $stmt->execute(['id' => $parking->getId()]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $this->pdo->prepare("
                UPDATE parkings 
                SET owner_id = :owner_id,
                    nom = :nom,
                    latitude = :latitude,
                    longitude = :longitude,
                    nb_places = :nb_places,
                    horaires_ouverture = :horaires_ouverture
                WHERE id = :id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO parkings (id, owner_id, nom, latitude, longitude, nb_places, horaires_ouverture, created_at)
                VALUES (:id, :owner_id, :nom, :latitude, :longitude, :nb_places, :horaires_ouverture, NOW())
            ");
        }

        $stmt->execute([
            'id' => $parking->getId(),
            'owner_id' => $parking->getOwnerId(),
            'nom' => $parking->getNom(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'nb_places' => $parking->getNbPlaces(),
            'horaires_ouverture' => json_encode($parking->getHorairesOuverture())
        ]);

        $this->saveTarifs($parking);
    }

    private function saveTarifs(Parking $parking): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM tarifs_horaires WHERE parking_id = :parking_id");
        $stmt->execute(['parking_id' => $parking->getId()]);

        foreach ($parking->getTarifsHoraires() as $tarif) {
            $stmt = $this->pdo->prepare("
                INSERT INTO tarifs_horaires (id, parking_id, tranche_duree, prix, ordre)
                VALUES (:id, :parking_id, :tranche_duree, :prix, :ordre)
            ");
            
            $stmt->execute([
                'id' => $tarif->getId(),
                'parking_id' => $parking->getId(),
                'tranche_duree' => $tarif->getTrancheDuree(),
                'prix' => $tarif->getPrix(),
                'ordre' => $tarif->getOrdre()
            ]);
        }
    }

    public function findById(string $id): ?Parking
    {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByOwnerId(string $ownerId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM parkings 
            WHERE owner_id = :owner_id 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['owner_id' => $ownerId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function searchByGPS(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT *,
                (6371 * acos(
                    cos(radians(:lat)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(:lng)) + 
                    sin(radians(:lat)) * 
                    sin(radians(latitude))
                )) AS distance
            FROM parkings
            HAVING distance <= :radius
            ORDER BY distance ASC
        ");

        $stmt->execute([
            'lat' => $latitude,
            'lng' => $longitude,
            'radius' => $radiusKm
        ]);

        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM tarifs_horaires WHERE parking_id = :parking_id");
        $stmt->execute(['parking_id' => $id]);

        $stmt = $this->pdo->prepare("DELETE FROM parkings WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM parkings ORDER BY created_at DESC");
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    private function hydrate(array $data): Parking
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM tarifs_horaires 
            WHERE parking_id = :parking_id 
            ORDER BY ordre ASC
        ");
        $stmt->execute(['parking_id' => $data['id']]);
        $tarifsData = $stmt->fetchAll();

        $tarifs = array_map(function($tarifRow) {
            return new TarifHoraire(
                id: $tarifRow['id'],
                parkingId: $tarifRow['parking_id'],
                trancheDuree: (int)$tarifRow['tranche_duree'],
                prix: (float)$tarifRow['prix'],
                ordre: (int)$tarifRow['ordre']
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
            horairesOuverture: json_decode($data['horaires_ouverture'] ?? '{}', true)
        );
    }
}


