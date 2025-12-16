<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SQL;

use App\Domain\Repositories\ParkingRepositoryInterface;
use App\Domain\Entities\Parking;
use PDO;

final class MySQLParkingRepository implements ParkingRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
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
                    name = :name,
                    address = :address,
                    latitude = :latitude,
                    longitude = :longitude,
                    total_spots = :total_spots,
                    tariffs = :tariffs,
                    schedule = :schedule,
                    is_active = :is_active,
                    updated_at = :updated_at
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $parking->getId(),
                'owner_id' => $parking->getOwnerId(),
                'name' => $parking->getName(),
                'address' => $parking->getAddress(),
                'latitude' => $parking->getLatitude(),
                'longitude' => $parking->getLongitude(),
                'total_spots' => $parking->getTotalSpots(),
                'tariffs' => json_encode($parking->getTariffs()),
                'schedule' => json_encode($parking->getSchedule()),
                'is_active' => $parking->isActive() ? 1 : 0,
                'updated_at' => $parking->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO parkings (id, owner_id, name, address, latitude, longitude, total_spots, tariffs, schedule, is_active, created_at)
                VALUES (:id, :owner_id, :name, :address, :latitude, :longitude, :total_spots, :tariffs, :schedule, :is_active, :created_at)
            ");
            $stmt->execute([
                'id' => $parking->getId(),
                'owner_id' => $parking->getOwnerId(),
                'name' => $parking->getName(),
                'address' => $parking->getAddress(),
                'latitude' => $parking->getLatitude(),
                'longitude' => $parking->getLongitude(),
                'total_spots' => $parking->getTotalSpots(),
                'tariffs' => json_encode($parking->getTariffs()),
                'schedule' => json_encode($parking->getSchedule()),
                'is_active' => $parking->isActive() ? 1 : 0,
                'created_at' => $parking->getCreatedAt()->format('Y-m-d H:i:s'),
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

    public function findByLocation(float $latitude, float $longitude, float $radiusKm = 5.0): array
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
            WHERE is_active = 1
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
        $stmt = $this->pdo->prepare("DELETE FROM parkings WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $data): Parking
    {
        return new Parking(
            id: $data['id'],
            ownerId: $data['owner_id'],
            name: $data['name'],
            address: $data['address'],
            latitude: (float)$data['latitude'],
            longitude: (float)$data['longitude'],
            totalSpots: (int)$data['total_spots'],
            tariffs: json_decode($data['tariffs'] ?? '[]', true),
            schedule: json_decode($data['schedule'] ?? '{}', true),
            isActive: (bool)$data['is_active'],
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}
