<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entities\Reservation;
use App\Domain\Repositories\ReservationRepositoryInterface;

final class FileReservationRepository implements ReservationRepositoryInterface
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/../../../data/reservations')
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save(Reservation $reservation): void
    {
        $filePath = $this->getFilePath($reservation->getId());
        $data = [
            'id' => $reservation->getId(),
            'user_id' => $reservation->getUserId(),
            'parking_id' => $reservation->getParkingId(),
            'start_time' => $reservation->getStartTime(),
            'end_time' => $reservation->getEndTime(),
            'estimated_price' => $reservation->getEstimatedPrice(),
            'status' => $reservation->getStatus(),
            'created_at' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $reservation->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findById(string $id): ?Reservation
    {
        $reservations = $this->loadAll();

        if (!isset($reservations[$id])) {
            return null;
        }

        return $this->hydrate($reservations[$id]);
    }

    public function findActiveByParking(string $parkingId, int $debut, int $fin): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] !== $parkingId) {
                continue;
            }

            if ($reservationData['statut'] !== 'active') {
                continue;
            }

            // Vérifier le chevauchement
            $resDebut = $reservationData['debut'];
            $resFin = $reservationData['fin'];

            // Pas de chevauchement si : fin <= debut OU debut >= fin
            $overlap = !($fin <= $resDebut || $debut >= $resFin);

            if ($overlap) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        return $result;
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data ? $this->hydrate($data) : null;
    }

    public function findByUserId(string $userId): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['user_id'] === $userId) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        // Trier par date de début décroissante
        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId) {
                $reservations[] = $this->hydrate($data);
            }
        }
        return $reservations;
    }

    public function findByParkingId(string $parkingId): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] === $parkingId) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        // Trier par date de début décroissante
        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
    }

    public function findCompletedByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $reservations = $this->loadAll();
        $result = [];

        // Calculer le début et la fin du mois
        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] !== $parkingId) {
                continue;
            }

            if ($reservationData['statut'] !== 'terminee') {
                continue;
            }

            $resFin = $reservationData['fin'];

            if ($resFin >= $debutMois && $resFin <= $finMois) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        // Trier par date de fin décroissante
        usort($result, fn($a, $b) => $b->getFin() <=> $a->getFin());

        return $result;
        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId) {
                $reservations[] = $this->hydrate($data);
            }
        }
        return $reservations;
    }

    public function findActiveByParking(string $parkingId, int $startTime, int $endTime): array
    {
        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId && $data['status'] === 'active') {
                $reservation = $this->hydrate($data);
                if ($reservation->isOverlapping($startTime, $endTime)) {
                    $reservations[] = $reservation;
                }
            }
        }
        return $reservations;
    }

    public function delete(string $id): void
    {
        $reservations = $this->loadAll();

        if (isset($reservations[$id])) {
            unset($reservations[$id]);
            $this->saveAll($reservations);
        }
    }

    private function loadAll(): array
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $reservations): void
    {
        $json = json_encode($reservations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->filePath, $json, LOCK_EX);
    }

    private function ensureFileExists(): void
    {
        $dir = dirname($this->filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]), LOCK_EX);
        }
        $filePath = $this->getFilePath($id);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getFilePath(string $id): string
    {
        return $this->dataDir . '/' . $id . '.json';
    }

    private function hydrate(array $data): Reservation
    {
        return new Reservation(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            estimatedPrice: $data['estimated_price'],
            status: $data['status'] ?? 'active',
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

