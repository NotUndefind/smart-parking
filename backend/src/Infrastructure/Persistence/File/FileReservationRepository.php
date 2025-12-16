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
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data ? $this->hydrate($data) : null;
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

    public function findByUserId(string $userId): array
    {
        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId) {
                $reservations[] = $this->hydrate($data);
            }
        }

        usort($reservations, fn($a, $b) => $b->getStartTime() <=> $a->getStartTime());

        return $reservations;
    }

    public function findByParkingId(string $parkingId): array
    {
        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId) {
                $reservations[] = $this->hydrate($data);
            }
        }

        usort($reservations, fn($a, $b) => $b->getStartTime() <=> $a->getStartTime());

        return $reservations;
    }

    public function findCompletedByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        $reservations = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId && $data['status'] === 'completed') {
                $reservation = $this->hydrate($data);
                $endTime = $reservation->getEndTime();
                if ($endTime >= $debutMois && $endTime <= $finMois) {
                    $reservations[] = $reservation;
                }
            }
        }

        usort($reservations, fn($a, $b) => $b->getEndTime() <=> $a->getEndTime());

        return $reservations;
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

    private function hydrate(array $data): Reservation
    {
        return new Reservation(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            startTime: (int)$data['start_time'],
            endTime: (int)$data['end_time'],
            estimatedPrice: (float)$data['estimated_price'],
            status: $data['status'] ?? 'active',
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}
