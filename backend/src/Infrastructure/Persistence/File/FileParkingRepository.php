<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entities\Parking;
use App\Domain\Repositories\ParkingRepositoryInterface;

final class FileParkingRepository implements ParkingRepositoryInterface
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/../../../data/parkings')
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save(Parking $parking): void
    {
        $filePath = $this->getFilePath($parking->getId());
        $data = [
            'id' => $parking->getId(),
            'owner_id' => $parking->getOwnerId(),
            'name' => $parking->getName(),
            'address' => $parking->getAddress(),
            'latitude' => $parking->getLatitude(),
            'longitude' => $parking->getLongitude(),
            'total_spots' => $parking->getTotalSpots(),
            'tariffs' => $parking->getTariffs(),
            'schedule' => $parking->getSchedule(),
            'is_active' => $parking->isActive(),
            'created_at' => $parking->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $parking->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findById(string $id): ?Parking
    {
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data ? $this->hydrate($data) : null;
    }

    public function findByOwnerId(string $ownerId): array
    {
        $parkings = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['owner_id'] === $ownerId) {
                $parkings[] = $this->hydrate($data);
            }
        }
        return $parkings;
    }

    public function findByLocation(float $latitude, float $longitude, float $radiusKm = 5.0): array
    {
        $parkings = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data) {
                $parking = $this->hydrate($data);
                if ($parking->isActive()) {
                    $distance = $parking->calculateDistance($latitude, $longitude);
                    if ($distance <= $radiusKm) {
                        $parkings[] = $parking;
                    }
                }
            }
        }

        // Trier par distance
        usort($parkings, function ($a, $b) use ($latitude, $longitude) {
            $distA = $a->calculateDistance($latitude, $longitude);
            $distB = $b->calculateDistance($latitude, $longitude);
            return $distA <=> $distB;
        });

        return $parkings;
    }

    public function delete(string $id): void
    {
        $filePath = $this->getFilePath($id);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getFilePath(string $id): string
    {
        return $this->dataDir . '/' . $id . '.json';
    }

    private function hydrate(array $data): Parking
    {
        return new Parking(
            id: $data['id'],
            ownerId: $data['owner_id'],
            name: $data['name'],
            address: $data['address'],
            latitude: $data['latitude'],
            longitude: $data['longitude'],
            totalSpots: $data['total_spots'],
            tariffs: $data['tariffs'] ?? [],
            schedule: $data['schedule'] ?? [],
            isActive: $data['is_active'] ?? true,
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

